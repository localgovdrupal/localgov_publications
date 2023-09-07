<?php

namespace Drupal\localgov_publications\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Publication manager.
 */
class PublicationManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Gets the node at the top level of a publication.
   */
  public function getTopLevel(NodeInterface $node): NodeInterface {

    // If this node is not part of a book, return the current node.
    if (!isset($node->book)) {
      return $node;
    }

    // If this node is the top level, return it.
    if ($node->book['pid'] === '0') {
      return $node;
    }

    /** @var \Drupal\node\NodeInterface $topLevelNode */
    $topLevelNode = $this->entityTypeManager->getStorage('node')->load($node->book['bid']);

    return $topLevelNode;
  }

}
