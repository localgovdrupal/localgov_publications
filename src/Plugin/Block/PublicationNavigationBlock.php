<?php

namespace Drupal\localgov_publications\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\book\BookManagerInterface;
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

  use CollapsibleTrait;

  /**
   * Current node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected BookManagerInterface $bookManager,
    protected ModuleHandlerInterface $moduleHandler,
    protected ThemeManagerInterface $themeManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('book.manager'),
      $container->get('module_handler'),
      $container->get('theme.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'collapsible' => TRUE,
      'collapse_width' => '768',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {

    /** @var \Drupal\node\NodeInterface $node */
    $this->node = $this->getContextValue('node');

    if (!isset($this->node->book['bid'])) {
      return [];
    }

    if (!empty($this->node->book['bid'])) {
      $tree = $this->bookManager->bookTreeAllData($this->node->book['bid'], $this->node->book);
      $this->moduleHandler->alter('localgov_publications_menu_tree', $tree);
      $this->themeManager->alter('localgov_publications_menu_tree', $tree);

      // If the top level doesn't have any child pages, (IE this is a single
      // page publication) don't show the menu block, as there isn't anything
      // else to navigate to.
      $top = reset($tree);
      if (empty($top['below'])) {
        return [];
      }

      $output = $this->bookManager->bookTreeOutput($tree);
      if (!empty($output)) {
        $this->setActiveClass($output['#items']);
        $this->addCollabsibleData($output);
        return $output;
      }
    }

    $tree = $this->bookManager->bookTreeAllData($this->node->book['bid'], $this->node->book);
    $this->moduleHandler->alter('localgov_publications_menu_tree', $tree);
    $this->themeManager->alter('localgov_publications_menu_tree', $tree);

    // If the top level doesn't have any child pages, (IE this is a single
    // page publication) don't show the menu block, as there isn't anything
    // else to navigate to.
    $top = reset($tree);
    if (!isset($top['below']) || $top['below'] === []) {
      return [];
    }

    $output = $this->bookManager->bookTreeOutput($tree);
    if ($output === []) {
      return [];
    }

    $this->setActiveClass($output['#items']);
    return $output;
  }

  /**
   * Sets 'active' class on menu items that are in the active trail.
   */
  protected function setActiveClass($items): void {
    foreach ($items as $item) {
      $original_link_id = $item['original_link']['nid'] ?? NULL;
      if ($original_link_id && ($original_link_id == $this->node->id())) {
        /** @var \Drupal\Core\Template\Attribute $attributes */
        $attributes = $item['attributes'];
        $attributes->addClass('active');
      }
      if (isset($item['below']) && is_array($item['below'])) {
        $this->setActiveClass($item['below']);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @todo Make cacheable in https://www.drupal.org/node/2483181
   */
  public function getCacheMaxAge(): int {
    return 0;
  }

}
