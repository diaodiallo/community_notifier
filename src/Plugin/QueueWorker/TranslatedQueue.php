<?php
/**
 * Created by PhpStorm.
 * User: ddiallo
 * Date: 6/14/18
 * Time: 2:14 PM
 */
namespace Drupal\community_notifier\Plugin\QueueWorker;

use Drupal\Core\Mail\MailManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @QueueWorker(
 * id = "translated_queue",
 * title = "Translated queue processor",
 * cron = {"time" = 150}
 * )
 */

class TranslatedQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface{

  private $mail;
  public function __construct(MailManager $mail) {
    $this->mail = $mail;
  }

  public function processItem($data) {

    $comment = $data['comment'];
    $commentedEntityId = $comment->getCommentedEntityId();


    $notificationEntities = \Drupal::service('community_notifier.nodeflags')->getFlaggedNotificationEntities($commentedEntityId);

    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('forum')){

      $forumService = \Drupal::service('forum_manager');
      $isForumType = $forumService->checkNodeType($comment->getCommentedEntity());
      if ($isForumType){
        $forum_index_storage = \Drupal::service('forum.index_storage');
        $term_id = $forum_index_storage->getOriginalTermId($comment->getCommentedEntity());

        // Get the appropriate Forum name
        $forumName = NULL;
        $results = \Drupal::database()->query("SELECT name FROM taxonomy_term_field_data WHERE tid = :tid AND langcode = :langcode", [':tid' => $term_id, ':langcode' => $data['lang']])->fetchAll();
        if (count($results) == 0){
          \Drupal::logger('community_notifier')->notice('No data from this query : Forum type id = '.$term_id);
          $term = \Drupal\taxonomy\Entity\Term::load($term_id);
          $forumName = $term->getName();
        }else{
          foreach ($results as $result) {
            $translatedName = get_object_vars($result);
            foreach ($translatedName as $translatedForumName){
              $forumName = $translatedForumName;
            }
          }
        }

      }
    }

    $base_url = \Symfony\Component\HttpFoundation\Request::createFromGlobals()->getSchemeAndHttpHost();
    $link = $base_url.'/node/'.$commentedEntityId.'#comment-'.$comment->id();


    $body = NULL;
    $results = \Drupal::database()->query("SELECT comment_body_value FROM comment__comment_body WHERE entity_id = :entity_id AND langcode = :langcode", [':entity_id' => $data['cid'], ':langcode' => $data['lang']])->fetchAll();
    if (count($results) == 0){
      \Drupal::logger('community_notifier')->notice('No data from this query from translated comment id = '.$data['cid']);
    }else{
      foreach ($results as $result) {
        $translated = get_object_vars($result);
        foreach ($translated as $translatedComment){
          $body = $translatedComment;
        }
      }
    }

    /**
     * Get the appropriate translated title
     */

    $title = NULL;
    $results = \Drupal::database()->query("SELECT title FROM node_field_data WHERE type = :type AND nid = :nid AND langcode = :langcode", [':type' => 'forum', ':nid' => $commentedEntityId, ':langcode' => $data['lang']])->fetchAll();
    if (count($results) == 0){
      \Drupal::logger('community_notifier')->notice('No data from this query : Furum title id = '.$commentedEntityId);
      $title = $comment->getCommentedEntity()->get('title')->value;
    }else{
      foreach ($results as $result) {
        $translatedTitle = get_object_vars($result);
        foreach ($translatedTitle as $translatedTitleContent){
          $title = $translatedTitleContent;
        }
      }
    }

    if (count($results) !== 0 AND $body !== NULL AND isset($body)){

      foreach ($notificationEntities as $notificationEntity) {
      $frequency = $notificationEntity->getFrequency();

      $userId = $notificationEntity->getOwnerId();
      $user = \Drupal\user\Entity\User::load($userId);
      $userLanguage = $user->getPreferredLangcode();

      if ($data['lang'] == 'en') {
        $messageBody = '<p>'.$comment->getOwner()->getAccountName().' commented on '.
          $comment->getCommentedEntity()->getOwner()->getUsername().' post in '.$forumName.': '
          .$title.'</p><p>'.$body.'</p> <p>'.$link.'</p>'.
          '<p>~~~To post a comment on this post via email, reply to this email.~~~</p>';
      } elseif ($data['lang'] == 'fr') {
        $messageBody = '<p>'.$comment->getOwner()->getAccountName().' a commenté la publication de '.
          $comment->getCommentedEntity()->getOwner()->getUsername().' dans '.$forumName.': '
          .$title.'</p><p>'.$body.'</p> <p>'.$link.'</p>'.
          '<p>~~~Pour commenter cette publication par email, repondez juste a cet email.~~~</p>';
      }

      $subject = 'RRHO: '. $title;

      $params['to'] = $notificationEntity->getOwner()->getEmail();
      $params['subject'] = $subject;
      $params['message'] = $messageBody;
      $params['comment_id'] = $comment->getCommentedEntity()->id();



      if($data['lang'] == $userLanguage){
        if ($frequency == 'immediately'){
          $this->mail->mail('community_notifier','comment_insert_alert',$params['to'],'en',$params,NULL,true);
        }
      }
    }
    }
  }
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('plugin.manager.mail')
    );
  }
}