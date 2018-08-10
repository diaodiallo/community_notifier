<?php

namespace Drupal\community_notifier;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Community notifier frequency entities.
 *
 * @ingroup community_notifier
 */
class CommunityNotifierFrequencyListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('notifier freq ID');
    $header['name'] = $this->t('Name');
    $header['flag id'] = $this->t('flag_id');
    $header['entity name'] = $this->t('entity_name');
    $header['frequency'] = $this->t('frequency');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\community_notifier\Entity\CommunityNotifierFrequency */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.community_notifier_frequency.edit_form',
      ['community_notifier_frequency' => $entity->id()]
    );
    $row['flag_id'] = $entity->getFlagId();
    $row['entity_name'] = $entity->getFlaggedEntityName();
    $row['frequency'] = $entity->getFrequency();
    return $row + parent::buildRow($entity);
  }

}
