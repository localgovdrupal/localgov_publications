<?php

namespace Drupal\localgov_publications\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a page header block for publications.
 *
 * @Block(
 *  id = "localgov_publications_page_header_block",
 *  admin_label = @Translation("Publications page header block."),
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current node"),
 *       constraints = {
 *         "Bundle" = {
 *           "publication_page"
 *         },
 *       }
 *     )
 *   }
 * )
 */
class PublicationPageHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
      $container->get('entity_type.manager')
    );
  }

  /**
   * Table of contents block constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#theme' => 'localgov_publication_page_header_block',
    ];

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');

    // Check if this node is the top level for this publication.
    if (!empty($node->book)) {
      if ($node->book['pid'] === '0') {
        // Top level parent page.
        $title = $node->getTitle();
        $published_date = $node->get('localgov_published_date')->value;
        $last_updated_date = $node->get('localgov_updated_date')->value;
      }
      else {
        // Get the top level parent page.
        $top_parent_node = $this->entityTypeManager->getStorage('node')->load($node->book['bid']);
        $title = $top_parent_node->getTitle();
        $published_date = $top_parent_node->get('localgov_published_date')->value;
        $last_updated_date = $top_parent_node->get('localgov_updated_date')->value;
      }

      $build['#title'] = $title;

      // Add published date, if available.
      if (!empty($published_date)) {
        $build['#published_date'] = date_format(date_create($published_date), "j F Y");
      }

      // Add last updated date, if available.
      if (!empty($last_updated_date)) {
        $build['#last_updated_date'] = date_format(date_create($last_updated_date), "j F Y");
      }
    }

    return $build;
  }

}
