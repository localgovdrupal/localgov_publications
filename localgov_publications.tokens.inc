<?php

/**
 * @file
 * Provides tokens for publications related data.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\localgov_publications\Token\Hooks as TokenHooks;

/**
 * Implements hook_token_info().
 */
function localgov_publications_token_info_alter(&$data) {
  $data['tokens']['node']['localgov-publication-cover-page-alias'] = [
    'name' => t("Cover page path"),
    'description' => t("The path of the publication cover page."),
  ];
  $data['tokens']['node']['localgov-publication-path'] = [
    'name' => t("Publication path"),
    'description' => t("The path to a publication page. Includes the cover page if there is one and respects existing aliases."),
  ];
}

/**
 * Implements hook_tokens_alter().
 */
function localgov_publications_tokens_alter(array &$replacements, array $context, BubbleableMetadata $bubbleable_metadata) {
  \Drupal::service('class_resolver')
    ->getInstanceFromDefinition(TokenHooks::class)
    ->tokensAlter($replacements, $context, $bubbleable_metadata);
}
