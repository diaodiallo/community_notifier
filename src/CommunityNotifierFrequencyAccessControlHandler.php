<?php

namespace Drupal\community_notifier;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Community notifier frequency entity.
 *
 * @see \Drupal\community_notifier\Entity\CommunityNotifierFrequency.
 */
class CommunityNotifierFrequencyAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\community_notifier\Entity\CommunityNotifierFrequencyInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished community notifier frequency entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published community notifier frequency entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit community notifier frequency entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete community notifier frequency entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add community notifier frequency entities');
  }

}
