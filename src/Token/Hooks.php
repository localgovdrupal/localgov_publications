<?php

namespace Drupal\localgov_publications\Token;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\localgov_publications\Service\PublicationManager;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Hooks implements ContainerInjectionInterface {

  public function __construct(
    private PublicationManager $publicationManager,
    private AliasManager $aliasManager
  ) {
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('localgov_publications.publication_manager'),
      $container->get('path_alias.manager'),
    );
  }

  /**
   * Implements hook_tokens_alter().
   *
   * See localgov_publications_tokens_alter().
   */
  public function tokensAlter(array &$replacements, array $context, BubbleableMetadata $bubbleable_metadata) {

    if ($context['type'] !== 'node' || !isset($context['data']['node'])) {
      return;
    }

    /** @var \Drupal\node\NodeInterface $node */
    $node = $context['data']['node'];

    if (!isset($node->book['bid']) || $node->book['bid'] === 0) {
      return;
    }

    if (isset($context['tokens']['localgov-publication-cover-page-alias'])) {

      $coverPage = $this->publicationManager->getCoverPage($node->book['bid']);
      if ($coverPage instanceof NodeInterface) {
        $bubbleable_metadata->addCacheableDependency($coverPage);
        $coverPageAlias = $this->aliasManager->getAliasByPath('/node/' . $coverPage->id());
        $replacements[$context['tokens']['localgov-publication-cover-page-alias']] = $coverPageAlias;
      }
    }

    if (isset($context['tokens']['localgov-publication-path'])) {

      $pathElements = [];

      $coverPage = $this->publicationManager->getCoverPage($node->book['bid']);
      if ($coverPage instanceof NodeInterface) {
        $bubbleable_metadata->addCacheableDependency($coverPage);
        $coverPageAlias = trim($this->aliasManager->getAliasByPath('/node/' . $coverPage->id()), '/');
      }

      if ($node->book['bid'] === $node->id()) {
        // Add the cover page alias if we are on the root node and there's a cover page.
        if ($coverPage instanceof NodeInterface) {
          $pathElements[] = $coverPageAlias;
        }
      }
      else {
        // Add the root node's URL alias in if we're not on the root node now:
        $rootNode = Node::load($node->book['bid']);
        $pathElements[] = $this->aliasManager->getAliasByPath('/node/' . $rootNode->id());
      }

      foreach ($this->bookParents($node) as $parent) {
        $pathElements[] = $parent;
      }

      $replacements[$context['tokens']['localgov-publication-path']] = implode('/', $pathElements);
    }
  }

  /**
   * Loads all the parents of the book page.
   *
   * Doesn't include the current node or the root node.
   */
  private function bookParents(NodeInterface $argNode) {

    // Re-load the node, to ensure it's got all the book data on it.
    $node = Node::load($argNode->id());

    if (empty($node->book['nid'])) {
      return [];
    }

    $parents = [];

    $i = 2; // Skip the first level of the book, as we add this elsewhere.
    while (isset($node->book["p$i"]) && ($node->book["p$i"] != $node->book['nid'])) {
      $node = Node::load($node->book["p$i"]);
      if ($node instanceof NodeInterface) {
        $parents[] = $node->getTitle();
      }
      $i++;
    }

    return $parents;
  }
}
