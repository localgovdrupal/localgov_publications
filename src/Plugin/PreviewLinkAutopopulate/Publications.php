<?php

namespace Drupal\localgov_publications\Plugin\PreviewLinkAutopopulate;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\book\BookManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\preview_link\PreviewLinkAutopopulatePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-populate publication preview links.
 *
 * @PreviewLinkAutopopulate(
 *   id = "localgov_publications",
 *   label = @Translation("Add all the pages for this publication"),
 *   description = @Translation("Add publication page and any cover page nodes to preview link."),
 *   supported_entities = {
 *     "node" = {
 *       "localgov_publication_cover_page",
 *       "localgov_publication_page",
 *     }
 *   },
 * )
 */
class Publications extends PreviewLinkAutopopulatePluginBase {

  /**
   * The book manager service.
   *
   * @var \Drupal\book\BookManagerInterface
   */
  protected BookManagerInterface $bookManager;

  /**
   * Constructs a Publications preview_link_autopopulate plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\book\BookManagerInterface $book_manager
   *   The book manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, BookManagerInterface $book_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_match, $entity_type_manager);
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
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('book.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviewEntities(): array {
    $nodes = [];

    $entity = $this->getEntity();
    if ($entity->bundle() == 'localgov_publication_cover_page') {
      $publications = $entity->get('localgov_publication')->referencedEntities();
    }
    else {
      $publications = [$entity];
    }

    foreach ($publications as $publication) {
      if (isset($publication->book['bid'])) {

        // Find publication pages.
        $node_storage = $this->entityTypeManager->getStorage('node');
        $book_links = $this->bookManager->bookTreeGetFlat($publication->book);
        foreach ($book_links as $link) {
          $node = $node_storage->load($link['nid']);
          if ($node instanceof NodeInterface && !isset($nodes[$node->id()])) {
            $nodes[$node->id()] = $node;
          }
        }

        // Find any publication cover pages.
        $cover_pages = $node_storage->loadByProperties([
          'localgov_publication' => array_keys($book_links),
        ]);
        foreach ($cover_pages as $cover_page) {
          if (!isset($nodes[$cover_page->id()])) {
            $nodes[$cover_page->id()] = $cover_page;
          }
        }
      }
    }

    return $nodes;
  }

}
