<?php

namespace Drupal\community_notifier\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Community notifier frequency entities.
 */
class CommunityNotifierFrequencyViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
