<?php

/**
 * @file
 * Contains community_notifier_frequency.page.inc.
 *
 * Page callback for Community notifier frequency entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Community notifier frequency templates.
 *
 * Default template: community_notifier_frequency.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_community_notifier_frequency(array &$variables) {
  // Fetch CommunityNotifierFrequency Entity Object.
  $community_notifier_frequency = $variables['elements']['#community_notifier_frequency'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
