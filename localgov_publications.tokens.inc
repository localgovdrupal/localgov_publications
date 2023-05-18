<?php

/**
 * @file
 * Provides tokens for publications related data.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function localgov_publications_token_info_alter(&$data) {
  $data['tokens']['node']['publication-landing-page-alias'] = [
    'name' => t("Landing page path"),
    'description' => t("The path of the publication landing page."),
  ];
}

/**
 * Implements hook_token_info().
 */
function localgov_publications_tokens_alter(array &$replacements, array $context, BubbleableMetadata $bubbleable_metadata) {
  if ($context['type'] == 'node' && !empty($context['data']['node'])) {
    $node = $context['data']['node'];
    $bid = $node->book['bid'];
    if ($bid === "0") {
      return;
    }
    $query = \Drupal::entityQuery('node');
    $query->condition('localgov_publication', $bid);
    $result = $query->execute();
    if (!empty($result)) {
      $landingPageNid = reset($result);
      $landingPageNode = \Drupal::entityTypeManager()->getStorage('node')->load($landingPageNid);
      $bubbleable_metadata->addCacheableDependency($landingPageNode);
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $landingPageNid);
      if (isset($context['tokens']['publication-landing-page-alias'])) {
        $replacements[$context['tokens']['publication-landing-page-alias']] = $alias;
      }
    }

  }
}
