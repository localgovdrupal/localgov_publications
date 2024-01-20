<?php

/**
 * @file
 * Hooks provided by the LocalGov Publications module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the menu tree of a publication.
 *
 * Modules may implement this hook to alter the menu tree of a publication,
 * when it's used to build the navigation in the publication_navigation block.
 *
 * See \Drupal\localgov_publications\Plugin\Block::build() for where this is
 * called.
 *
 * @param array $tree
 *   The menu tree, as returned by
 *   \Drupal\book\BookManagerInterfacebookManager::bookTreeAllData()
 */
function hook_localgov_publications_menu_tree_alter(&$tree) {

  // This example replaces the title shown in the navigation with a shorter
  // title provided by a custom field (lbhf_teaser_title).
  foreach ($tree as $item) {
    if (!empty($item['below'])) {
      hook_localgov_publications_menu_tree_alter($item['below']);
    }

    if (!isset($item['link']['nid'])) {
      continue;
    }

    $node = Node::load($item['link']['nid']);

    if (!$node instanceof NodeInterface) {
      continue;
    }

    if (!$node->hasField('lbhf_teaser_title')) {
      continue;
    }

    $teaserTitle = $node->get('lbhf_teaser_title')->value;
    if ($teaserTitle) {
      $item['link']['title'] = $teaserTitle;
    }
  }
}

/**
 * @} End of "addtogroup hooks".
 */
