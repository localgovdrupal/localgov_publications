<?php

namespace Drupal\localgov_publications\Breadcrumb;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\system\PathBasedBreadcrumbBuilder;

/**
 * A breadcrumb builder for publications.
 *
 * This exists to nullify the operation of the BookBreadcrumbBuilder for our
 * publication types. It's set to run before BookBreadcrumbBuilder in the
 * services file, and for publication types will run first and act exactly like
 * PathBasedBreadcrumbBuilder does.
 *
 * @see \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
 */
class BreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match, ?CacheableMetadata $cacheable_metadata = NULL) {
    $node = $route_match->getParameter('node');
    return $node instanceof NodeInterface && localgov_publications_is_publication_type($node->getType());
  }

}
