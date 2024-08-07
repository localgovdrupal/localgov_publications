<?php

/**
 * @file
 * Provides tokens for publications related data.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\NodeInterface;

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

  if ($context['type'] != 'node' || !isset($context['data']['node'])) {
    return;
  }

  if (!isset($context['tokens']['localgov-publication-cover-page-alias'])) {
    return;
  }

  $node = $context['data']['node'];
  $bid = (int) ($node->book['bid'] ?? 0);

  if ($bid === 0) {
    return;
  }

  /** @var \Drupal\localgov_publications\Service\PublicationManager $publicationManager */
  $publicationManager = \Drupal::service('localgov_publications.publication_manager');
  $coverPage = $publicationManager->getCoverPage($bid);
  if ($coverPage instanceof NodeInterface) {
    $bubbleable_metadata->addCacheableDependency($coverPage);
    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $coverPage->id());
    $replacements[$context['tokens']['localgov-publication-cover-page-alias']] = $alias;
  }
}
