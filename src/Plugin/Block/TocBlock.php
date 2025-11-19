<?php

namespace Drupal\localgov_publications\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\localgov_publications\Service\HeadingFinderInterface;
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
 *       label = @Translation("Current node")
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
   * The heading finder.
   *
   * @var \Drupal\localgov_publications\Service\HeadingFinderInterface
   */
  protected $headingFinder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('entity_type.manager'),
      $container->get('localgov_publications.heading_finder')
    );
  }

  /**
   * Table of contents block constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer, EntityTypeManagerInterface $entityTypeManager, HeadingFinderInterface $headingFinder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
    $this->entityTypeManager = $entityTypeManager;
    $this->headingFinder = $headingFinder;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');

    // Don't render on new nodes (/node/add form).
    if ($node->isNew()) {
      return [];
    }
    $build = $this->entityTypeManager->getViewBuilder('node')->view($node, 'full');

    // Call this so the render we're about to do has the same IDs as the page.
    // If we don't, they get deduplicated and are different.
    Html::resetSeenIds();

    $nodeHtml = $this->renderer->renderRoot($build)->__toString();
    $links = $this->headingFinder->searchMarkup($nodeHtml);

    if (count($links) === 0) {
      return [];
    }

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $links,
    ];
  }

}
