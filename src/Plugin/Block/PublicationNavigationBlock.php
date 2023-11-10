<?php

namespace Drupal\localgov_publications\Plugin\Block;

use Drupal\book\BookManagerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a publication navigation block.
 *
 * This is mostly copied from Drupal\book\Plugin\Block\BookNavigationBlock but
 * only implements the one case that block does that we need - IE the in book
 * nav, and not the nav of all books. This block will also show on unpublished
 * nodes, and includes a link to the publication root.
 *
 * @Block(
 *   id = "publication_navigation",
 *   admin_label = @Translation("Publication navigation"),
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current node")
 *     )
 *   }
 * )
 */
class PublicationNavigationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The book manager.
   *
   * @var \Drupal\book\BookManagerInterface
   */
  protected $bookManager;

  /**
   * Constructs a new BookNavigationBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\book\BookManagerInterface $book_manager
   *   The book manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BookManagerInterface $book_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->bookManager = $book_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('book.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');

    if (!empty($node->book['bid'])) {
      $tree = $this->bookManager->bookTreeAllData($node->book['bid'], $node->book);
      $output = $this->bookManager->bookTreeOutput($tree);
      if (!empty($output)) {
        $this->setActiveClass($output['#items']);
        return $output;
      }
    }
    return [];
  }

  /**
   * Sets 'active' class on menu items that are in the active trail.
   */
  protected function setActiveClass($items) {
    foreach ($items as $item) {
      if (!empty($item['in_active_trail'])) {
        /** @var \Drupal\Core\Template\Attribute $attributes */
        $attributes = $item['attributes'];
        $attributes->addClass('active');
      }
      if (!empty($item['below'])) {
        $this->setActiveClass($item['below']);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @todo Make cacheable in https://www.drupal.org/node/2483181
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
