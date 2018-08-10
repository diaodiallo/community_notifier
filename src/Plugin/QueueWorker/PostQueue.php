<?php

namespace Drupal\community_notifier\Plugin\QueueWorker;

use Drupal\Core\Mail\MailManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @QueueWorker(
 * id = "post_queue",
 * title = "Post queue processor",
 * cron = {"time" = 150}
 * )
 */


class PostQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  private $mail;
  public function __construct(MailManager $mail) {
    $this->mail = $mail;
  }

  public function processItem($data) {

    $title = NULL;
    //$forumName = NULL;
    $results = \Drupal::database()->query("SELECT title FROM node_field_data WHERE type = :type AND nid = :nid AND langcode = :langcode", [':type' => 'forum', ':nid' => $data['entityId'], ':langcode' => $data['lang']])->fetchAll();
    if (count($results) == 0){
      \Drupal::logger('community_notifier')->notice('No data from this query : Post title id = '.$data['entityId']);
    }else{
      foreach ($results as $result) {
        $translatedTitle = get_object_vars($result);
        foreach ($translatedTitle as $translatedTitleContent){
          $title = $translatedTitleContent;
        }
      }
    }

    //GET the translated body of the post
    $entityBody = NULL;
    $results = \Drupal::database()->query("SELECT body_value FROM node__body WHERE entity_id = :entity_id AND langcode = :langcode", [':entity_id' => $data['entityId'], ':langcode' => $data['lang']])->fetchAll();
    if (count($results) == 0){
      \Drupal::logger('community_notifier')->notice('No data from this query : Post body id = '.$data['entityId']);
    }else{
      foreach ($results as $result) {
        $translatedEntity = get_object_vars($result);
        foreach ($translatedEntity as $translatedEntityContent){
          $entityBody = $translatedEntityContent;
        }
      }
    }

    if ($data['lang'] == 'en'){
      $messageBody = '<p>' . $data['entityUser'].' added a new post in '.
        $data['flaggedEntity'].': '.$title.' </p><p> '.$entityBody.
        '</p><p>'.$data['link'].'</p>';
      $body = t($messageBody.'<p>~~~To post a comment on this post via email, reply to this email.~~~</p>') /*. $body*/;
    }elseif ($data['lang'] == 'fr'){
      $messageBody = '<p>' . $data['entityUser'].' a ajouté une nouvelle publication dans '.
        $data['flaggedEntity'].': '.$title.' </p><p> '.$entityBody.
        '</p><p>'.$data['link'].'</p>';
      $body = t($messageBody.'<p>~~~Pour commenter cette publication par email, repondez a cet email.~~~</p>') /*. $body*/;
    }

    $subject = 'RRHO: '. $title;

    $params['to'] = $data['email'];
    $params['subject'] = $subject;
    $params['message'] = $body;
    $params['entity_id'] = $data['entityId'];

    $this->mail->mail('community_notifier','query_mail',$data['email'],'en',$params,NULL,true);
  }
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('plugin.manager.mail')
    );
  }
}