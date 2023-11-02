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
  $data['tokens']['node']['localgov-publication-cover-page-alias'] = [
    'name' => t("Cover page path"),
    'description' => t("The path of the publication cover page."),
  ];
}

/**
 * Implements hook_token_info().
 */
function localgov_publications_tokens_alter(array &$replacements, array $context, BubbleableMetadata $bubbleable_metadata) {

  if ($context['type'] != 'node' || empty($context['data']['node'])) {
    return;
  }

  if (!isset($context['tokens']['localgov-publication-cover-page-alias'])) {
    return;
  }

  $node = $context['data']['node'];
  $bid = $node->book ? $node->book['bid'] : 0;

  if (empty($bid)) {
    return;
  }

  /** @var \Drupal\localgov_publications\Service\PublicationManager $publicationManager */
  $publicationManager = \Drupal::service('localgov_publications.publication_manager');

  $coverPage = $publicationManager->getCoverPage($bid);

  $bubbleable_metadata->addCacheableDependency($coverPage);
  $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $coverPage->id());

  $replacements[$context['tokens']['localgov-publication-cover-page-alias']] = $alias;
}
