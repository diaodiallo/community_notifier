<?php

namespace Drupal\community_notifier;
use Drupal\community_notifier\Entity\CommunityNotifierFrequency;
use Drupal\community_notifier\Util\CommunityNotifierUtility;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\flag\Entity\Flag;
use Drupal\flag\FlagService;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Class CommunityNotifierService.
 */
class CommunityNotifierService implements CommunityNotifierServiceInterface {

  /**
   * Drupal\flag\FlagService definition.
   *
   * @var \Drupal\flag\FlagService
   */
  private $flag;
  private $currentUser;
  private $configFactory;
  private $connection;
  private $moduleHandler;
  /*
   * @var EntityTypeManagerInterface
   * */
  private $entityTypeManager;
  /**
   * Constructs a new CommunityNotifierService object.
   */
  public function __construct(FlagService $flag, AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactory $config_factory, Connection $connection, ModuleHandler $module_handler) {
    $this->flagService = $flag;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->connection = $connection;
    $this->moduleHandler = $module_handler;
  }
  public function getFlaggableEntityTypes(){
    $contentFlags = [];
    $flags = $this->flagService->getAllFlags();

    foreach ($flags as $flag_key => $flag){
      $flagEnityType = $flag->getFlaggableEntityTypeId();
      if ($flagEnityType == 'node'){
        array_push($contentFlags, $flagEnityType);
      }
    }

    return $contentFlags;
  }
  public function flag($flagId, $flaggedEntityId, Request $request = NULL, array $entities = NULL){
    $destination = 'node';
    if ($request != NULL){
      if ($request->get('destination') != 'node')
      //$destination = $this->destination($request->get('destination'));
      $destination = 'taxonomy_term';
    }

    switch($destination){
      case 'node':
        $this->createNotificationEntities($this->currentUser->id(), $this->currentUser->getDisplayName(), $flagId,
          $flaggedEntityId, $this->getEntityById($flaggedEntityId)->label());
        break;
      case 'taxonomy_term':
        $res = $this->getEntityById($flaggedEntityId, 'taxonomy_term');

        $this->createNotificationEntities($this->currentUser->id(), $this->currentUser->getDisplayName(), $flagId,
          $flaggedEntityId, $this->getEntityById($flaggedEntityId, (gettype($res) == 'boolean' ? 'node' : 'taxonomy_term'))->label());
        /*if ($entities){
          $subscribeFlag = $this->getSubscribeFlag();
          foreach ($entities as $entity){
            $this->flagService->flag($subscribeFlag, $entity);
            $this->createNotificationEntities($this->currentUser->id(), $this->currentUser->getDisplayName(),
              $subscribeFlag->id(), $entity->id(), $this->getEntityById($entity->id())->label());
          }
        }*/
        break;
    }
  }
  public function unflag($flagId, $flaggedEntityId, Request $request, array $entities = NULL){
    $this->deleteSubscriptions($flaggedEntityId, $flagId, $this->currentUser->id());

    //handles deletion of forum subscriptions.
    /*$destination = $this->destination($request->get('destination'));
    if ($destination == 'taxonomy_term'){
      if ($entities){
        $subscribeFlag = $this->getSubscribeFlag();
        foreach ($entities as $entity){
          $this->flagService->unflag($subscribeFlag, $entity);
          $this->deleteSubscriptions($entity->id(), $subscribeFlag->id(), $this->currentUser->id());
        }
      }
    }*/
  }

  /**
   * @param $flaggedEntityId
   * @param $ownerId
   * @return an array of CommunityNotifierFrequency entities with a specified flaggedEntityId and owned or not by
   * $ownerId depending on the condition.
   */
  public function getNotificationEntitiesById($flaggedEntityId, $ownerId, $condition = '!='){
    $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
    $ids = $storage->getQuery()
      ->condition('entity_id', $flaggedEntityId, '=')
    // daniel commented this out  ->condition('user_id', $ownerId, $condition)
      ->execute();
    $notifierEntities = $storage->loadMultiple($ids);

    return $notifierEntities;
  }

    /**
     * @param $flaggedEntityId
     * @return an array of CommunityNotifierFrequency entities with a specified flaggedEntityId
     */
  public function getFlaggedNotificationEntities($flaggedEntityId){
      $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
      $ids = $storage->getQuery()
          ->condition('entity_id', $flaggedEntityId, '=')
          ->execute();
      $notifierEntities = $storage->loadMultiple($ids);

      return $notifierEntities;
  }
  public function getEntityById($nodeId, $enityType = 'node'){
    $entities = $this->entityTypeManager->getStorage($enityType)->loadMultiple([$nodeId]);

    return current($entities);
  }

  /**
   * @param $frequency (immediate, daily or weekly)
   * @return an array of CommunityNotifierFrequency entities with a specified frequency.
   */
  public function getNotificationEntitiesByFrequency($frequency, $condition = '='){

    $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
    $ids = $storage->getQuery()
      ->condition('frequency', $frequency, $condition)
      ->execute();
    $notifierEntities = $storage->loadMultiple($ids);

    return $notifierEntities;
  }

  /**
   * @param $targetId
   * @param $frequency
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function getCommentsForNotification($uid, $targetId, $frequency){
    $send = $this->configFactory->getEditable('community_notifier.settings')->get('settings.messages.send');
    $range = CommunityNotifierUtility::frequencyDateRange($frequency);
    $storage = $this->entityTypeManager->getStorage('comment');
    $query = $storage->getQuery()
      ->condition('uid', $uid, '<>')
      ->condition('entity_id', $targetId, '=')
      ->condition('created', $range, 'BETWEEN');
      //->sort('changed','DESC');
    if ($send != 0){
      $query = $query->range(0, $send);
    }
    $ids = $query->execute();
    $comments = $storage->loadMultiple($ids);

    return $comments;
  }

  /**
   * @return array of comments subscribed to by different users
   */
  public function getComments($frequency){
    $notificationEntities = $this->getNotificationEntitiesByFrequency($frequency);
    $comments = [];

    foreach ($notificationEntities as $notificationEntity){
      $uid = $notificationEntity->getOwnerId();
      $notificationEntityId = $notificationEntity->id();
      $targetId = $notificationEntity->getFlaggedEntityId();
      $notificationComments = $this->getCommentsForNotification($uid, $targetId, $frequency);
      $email = $notificationEntity->getOwner()->getEmail();
      $notifiableComments = [];
      $subject = '';

      foreach ($notificationComments as $notificationComment){
        $subject = $notificationComment->getCommentedEntity()->get('title')->value;
        $temp = [];
        //$temp['subject'] = $notificationComment->getSubject();
        $temp['id'] = $notificationComment->id();
        $temp['body'] = $notificationComment->get('comment_body')->value;
        $temp['created'] = date('Y-m-d', $notificationComment->get('created')->value);
        array_push($notifiableComments, $temp);
      }
      if (!empty($notifiableComments)){
        $comments[$notificationEntityId] = [
          'email' => $email,
          'subject' => $subject,
          'comments' => $notifiableComments
        ];
      }
    }
    return $comments;
  }
  public function getUserNotificationEntities($userId){
    $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
    $ids = $storage->getQuery()
      ->condition('user_id', $userId, '=')
      ->execute();
    $userNotificationEntities = $storage->loadMultiple($ids);

    return $userNotificationEntities;
  }
  public function updateNotificationEntity($fnotificationEntityId){
    $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
    $ids = $storage->getQuery()
      ->condition('id', $fnotificationEntityId, '=')
      ->execute();
    $userNotificationEntity = $storage->loadMultiple($ids);

    return current($userNotificationEntity);
  }
  public function getForumTopics($tid){
    if ($this->moduleHandler->moduleExists('forum')){
      $query = $this->connection->select('forum_index', 'f')
        ->fields('f')
        ->condition('f.tid', $tid);
      $result = $query->execute();
      $nids = [];
      foreach ($result as $record) {
        $nids[] = $record->nid;
      }
      if ($nids) {
        $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
        return $nodes;
      }
    }

    return [];
  }
  public function getSubscribeFlag(){
    $storage = $this->entityTypeManager->getStorage('flag');
    $ids = $storage->getQuery()
      ->condition('id', 'subscribe', '=')
      ->execute();
    $flag = $storage->loadMultiple($ids);

    return current($flag);
  }
  private function destination($destination){
    if ($destination == 'forum'){
      return 'taxonomy_term';
    }
    $destination_parameters = explode('/', $destination);
    $destination = count($destination_parameters) == 1 ? $destination :  $destination_parameters[0].'_'.$destination_parameters[1];
    $destination = str_replace('/', '_', $destination);

    return $destination;
  }
  public function deleteSubscriptions($flaggedEntityId, $flagId = NULL, $userId = NULL){
    $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
    $query = $storage->getQuery()->condition('entity_id', $flaggedEntityId, '=');
    if ($flagId){
      $query->condition('flag_id', $flagId, '=');
    }
    if ($userId){
      $query->condition('user_id', $userId, '=');
    }
    $ids = $query->execute();
    /*$ids = $storage->getQuery()
      ->condition('entity_id', $flaggedEntityId, '=')
      ->condition('flag_id', $flagId, '=')
      ->condition('user_id', $userId, '=')
      ->execute();*/

    foreach ($storage->loadMultiple($ids) as $entity){
      $entity->delete();
    }
  }

  /**
   * creates a notification entity
   *
   * @param $userId
   * @param $userName
   * @param $flagId
   * @param $entityId
   * @param $entityName
   */
  public function createNotificationEntities($userId, $userName, $flagId, $entityId, $entityName){
    CommunityNotifierFrequency::create([
      'uid' => $userId,
      'name' => $userName,
      'flag_id' => $flagId,
      'entity_id' => $entityId,
      'entity_name' => $entityName,
    ])->save();
  }

  /**
   * @param $tid taxonomy term id
   * @return \Drupal\Core\Entity\EntityInterface[] array of notification entities
   */
  public function getNotificationEntitiesByForum($tid){
    $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
    $ids = $storage->getQuery()
      ->condition('entity_id', $tid, '=')
      ->execute();
    return $storage->loadMultiple($ids);
  }
  public function createFlag(array $parameters){
    /*Flag::create([
      'id' => 'subscribe',
      'label' => 'subscribe',
      'langcode' => 'en',
      'status' => TRUE,
      'bundles' => ['node'],
      'entity_type' => 'node',
      'global' => FALSE,
      'flag_short' => 'subscribe to this item',
      'unflag_short' => 'unsubscribe  to this item',
      'flag_type' => 'entity:node',
      'link_type' => 'reload',
    ])->save();*/
    Flag::create($parameters)->save();
  }
  public function getFlagEntityById($flagId){
    $storage = $this->entityTypeManager->getStorage('flag');
    $ids = $storage->getQuery()->condition('id', $flagId, '=')->execute();
    $flagEntity = $storage->loadMultiple($ids);
    return current($flagEntity);
  }
  public function getCurrentUser(){
    return $this->currentUser->id();
  }
}

