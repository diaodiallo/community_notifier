<?php

namespace Drupal\community_notifier\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class CommunityNotifierRouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class CommunityNotifierRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('flag.action_link_flag')) {
      $route->setDefaults(array(
        '_controller' => '\Drupal\community_notifier\Controller\CommunityNotifierController::flag',
      ));
    }
    if ($route = $collection->get('flag.action_link_unflag')) {
      $route->setDefaults(array(
        '_controller' => '\Drupal\community_notifier\Controller\CommunityNotifierController::unflag',
      ));
    }
  }
}
