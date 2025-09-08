<?php

namespace Drupal\localgov_publications\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\book\BookManagerInterface;
use Drupal\localgov_publications\Service\HeadingFinderInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a combined publication navigation block.
 *
 * This block combines the navigation across pages with the navigation inside
 * pages. The resulting nav is one hierarchy.
 *
 * @Block(
 *   id = "localgov_publications_combined_navigation",
 *   admin_label = @Translation("Combined publication navigation"),
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current node")
 *     )
 *   }
 * )
 */
class CombinedNavigationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Current node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Constructor.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected BookManagerInterface $bookManager,
    protected ModuleHandlerInterface $moduleHandler,
    protected ThemeManagerInterface $themeManager,
    protected RendererInterface $renderer,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected HeadingFinderInterface $headingFinder
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
      $container->get('theme.manager'),
      $container->get('renderer'),
      $container->get('entity_type.manager'),
      $container->get('localgov_publications.heading_finder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $this->node = $this->getContextValue('node');

    if (!isset($this->node->book['bid'])) {
      return [];
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

    // At this point here, fudge in the on-page nav.
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    foreach ($output['#items'] as $bid => $item) {
      foreach ($item['below'] as $nid => $subItem) {
        // Generate the on-page nav and put it in $subItem['below'];

        $pageNode = $nodeStorage->load($nid);
        $toc = $this->buildToc($pageNode);

        foreach ($toc as $i => $tocLink) {
          $toc[$i]['original_link'] = $subItem['original_link'];
        }

        $output['#items'][$bid]['below'][$nid]['below'] = $toc;
      }
    }

    $this->setActiveClass($output['#items']);

    return $output;
  }

  /**
   * Sets 'active' class on menu items that are in the active trail.
   */
  protected function setActiveClass(array $items) {
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
   */
  public function buildToc(NodeInterface $node) {

    // Don't render on new nodes (/node/add form).
    if ($node->isNew()) {
      return [];
    }
    $build = $this->entityTypeManager->getViewBuilder('node')->view($node, 'full');

    // Call this so the render we're about to do has the same IDs as the page.
    // If we don't, they get deduplicated and are different.
    Html::resetSeenIds();

    $nodeHtml = $this->renderer->render($build)->__toString();
    $links = $this->headingFinder->searchMarkup($nodeHtml, $node->toUrl()->toString());

    if (count($links) === 0) {
      return [];
    }

    $rtn = [];

    foreach ($links as $link) {

      $attributes = new Attribute();

      $rtn[] = [
        'is_expanded' => FALSE,
        'is_collapsed' => TRUE,
        'in_active_trail' => FALSE,
        'attributes' => $attributes,
        'title' => $link->getText(),
        'url' => $link->getUrl(),
        'localized_options' => [],
        'below' => [],
      ];
    }

    return $rtn;
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
