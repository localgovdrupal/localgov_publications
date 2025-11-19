<?php

namespace Drupal\localgov_publications\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\localgov_publications\Service\PublicationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a page header block for publications.
 *
 * @Block(
 *  id = "localgov_publications_page_header_block",
 *  admin_label = @Translation("Publications page header block."),
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current node")
 *     )
 *   }
 * )
 */
class PublicationPageHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Publication manager.
   *
   * @var \Drupal\localgov_publications\Service\PublicationManager
   */
  protected $publicationManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('localgov_publications.publication_manager')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PublicationManager $publicationManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->publicationManager = $publicationManager;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');
    $topLevelNode = $this->publicationManager->getTopLevel($node);

    $build = [
      '#theme' => 'localgov_publication_page_header_block',
      '#title' => $topLevelNode->getTitle(),
    ];

    if ($node->id() !== $topLevelNode->id()) {
      $build['#node_title'] = $node->getTitle();
    }

    // Add published date, if available.
    if ($topLevelNode->hasField('localgov_published_date')) {
      $published_date = $topLevelNode->get('localgov_published_date')->value;
      if (!is_null($published_date)) {
        $build['#published_date'] = $this->formatDate($published_date);
      }
    }

    // Add last updated date, if available.
    if ($topLevelNode->hasField('localgov_updated_date')) {
      $last_updated_date = $topLevelNode->get('localgov_updated_date')->value;
      if (!is_null($last_updated_date)) {
        $build['#last_updated_date'] = $this->formatDate($last_updated_date);
      }
    }

    $cache = CacheableMetadata::createFromObject($node);
    $cache->addCacheableDependency($topLevelNode);
    $cache->applyTo($build);

    return $build;
  }

  /**
   * Formats a date for display.
   */
  protected function formatDate(string $date): string {
    return date_format(date_create($date), "j F Y");
  }

}
