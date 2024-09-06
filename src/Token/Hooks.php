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

    // Build 'localgov-publication-path'.

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

      foreach ($this->token_book_load_all_parents($node) as $parent) {
        $pathElements[] = $parent;
      }

      $replacements[$context['tokens']['localgov-publication-path']] = implode('/', $pathElements);
    }
  }

    /**
     * Loads all the parents of the book page.
     *
     * @param array $book
     *   The book data. The 'nid' key points to the current page of the book.
     *   The 'p1' ... 'p9' keys point to parents of the page, if they exist, with 'p1'
     *   pointing to the book itself and the last defined pX to the current page.
     *
     * @return string[]
     *   List of node titles of the book parents.
     */
    function token_book_load_all_parents(NodeInterface $argNode) {

      // Re-load the node, to ensure it's got all the book data on it.
      $node = Node::load($argNode->id());
      $book = $node->book;

      $parents = [];

      if (empty($book['nid'])) {
        return $parents;
      }

      $nid = $book['nid'];

      $i = 2; // Skip the first level of the book, as we add this elsewhere.
      while (isset($book["p$i"]) && ($book["p$i"] != $nid)) {
        $node = Node::load($book["p$i"]);
        if ($node instanceof NodeInterface) {
          $parents[] = $node->getTitle();
        }
        $i++;
      }

      return $parents;
    }

    /**
     * Cases to cover:
     *
     * /something/cover/root/
     * /something/cover/root/page1
     *
     * /something/root
     * /something/root/page1
     *
     * /cover/root
     * /cover/root/page1
     *
     * /root
     * /root/page1
     *
     *
     *
     */





}
