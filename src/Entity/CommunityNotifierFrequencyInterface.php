<?php

namespace Drupal\community_notifier\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Community notifier frequency entities.
 *
 * @ingroup community_notifier
 */
interface CommunityNotifierFrequencyInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Community notifier frequency name.
   *
   * @return string
   *   Name of the Community notifier frequency.
   */
  public function getName();

  /**
   * Sets the Community notifier frequency name.
   *
   * @param string $name
   *   The Community notifier frequency name.
   *
   * @return \Drupal\community_notifier\Entity\CommunityNotifierFrequencyInterface
   *   The called Community notifier frequency entity.
   */
  public function setName($name);

  /**
   * Gets the Community notifier frequency creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Community notifier frequency.
   */
  public function getCreatedTime();

  /**
   * Sets the Community notifier frequency creation timestamp.
   *
   * @param int $timestamp
   *   The Community notifier frequency creation timestamp.
   *
   * @return \Drupal\community_notifier\Entity\CommunityNotifierFrequencyInterface
   *   The called Community notifier frequency entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Community notifier frequency published status indicator.
   *
   * Unpublished Community notifier frequency are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Community notifier frequency is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Community notifier frequency.
   *
   * @param bool $published
   *   TRUE to set this Community notifier frequency to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\community_notifier\Entity\CommunityNotifierFrequencyInterface
   *   The called Community notifier frequency entity.
   */
  public function setPublished($published);

}
