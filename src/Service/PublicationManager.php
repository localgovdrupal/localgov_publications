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

  /**
   * Given the ID of a publication, returns the cover page node if there is one.
   *
   * @param int $publicationId
   *   The ID of the root node of the publication.
   *
   * @return ?\Drupal\node\NodeInterface
   *   The cover page node if there is one.
   */
  public function getCoverPage(int $publicationId) {

    /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    $result = $nodeStorage->getQuery()
      ->condition('localgov_publication', $publicationId)
      ->accessCheck(FALSE)
      ->execute();

    if (count($result) > 0) {
      $coverPageNid = reset($result);
      return $nodeStorage->load($coverPageNid);
    }

    return NULL;
  }

}
