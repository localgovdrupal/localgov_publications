<?php

namespace Drupal\localgov_publications\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Table of Contents block for publications.
 *
 * @Block(
 *  id = "localgov_publications_toc_block",
 *  admin_label = @Translation("Publications table of contents block."),
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current node"),
 *       constraints = {
 *         "Bundle" = {
 *           "publication"
 *         },
 *       }
 *     )
 *   }
 * )
 */
class TocBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Table of contents block constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');

    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    $build = $view_builder->view($node, 'full');

    // Call this so the render we're about to do has the same IDs as the page.
    // If we don't, they get deduplicated and are different.
    Html::resetSeenIds();

    $output = $this->renderer->renderRoot($build);
    $nodeHtml = $output->__toString();

    $links = $this->extractLinks($nodeHtml);

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $links,
    ];
  }

  /**
   * Extract links from markup.
   *
   * This'll probably get split out into a pluggable thing.
   *
   * @param string $nodeHtml
   *   HTML to scan for links.
   *
   * @return array
   *   Array of found links.
   */
  public function extractLinks(string $nodeHtml): array {

    $links = [];

    // This regex here is too prescriptive. We should parse this some more to
    // extract the ID more reliably.
    if (preg_match_all('#<h2 id="([^"]+)">([^<]+)</h2>#', $nodeHtml, $matches)) {
      foreach ($matches[0] as $key => $value) {
        $fragment = $matches[1][$key];
        $text = $matches[2][$key];
        $links[] = Link::fromTextAndUrl($text, Url::fromUserInput('#' . $fragment));
      }
    }

    return $links;
  }

}
