<?php

namespace Drupal\localgov_publications\Controller;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\book\Controller\BookController;

/**
 * Returns responses for LocalGov Publications routes.
 */
class LocalgovPublicationsBookController extends BookController {

  /**
   * Builds the response.
   */
  public function build() {
    $rows = [];

    $headers = [$this->t('Book'), $this->t('Operations')];
    // Add any recognized books to the table list.
    foreach ($this->bookManager->getAllBooks() as $book) {
      if ($book['type'] === 'localgov_publication_page') {
        continue;
      }
      /** @var \Drupal\Core\Url $url */
      $url = $book['url'];
      if (isset($book['options'])) {
        $url->setOptions($book['options']);
      }
      $row = [
        Link::fromTextAndUrl($book['title'], $url),
      ];
      $links = [];
      $links['edit'] = [
        'title' => $this->t('Edit order and titles'),
        'url' => Url::fromRoute('book.admin_edit', ['node' => $book['nid']]),
      ];
      $row[] = [
        'data' => [
          '#type' => 'operations',
          '#links' => $links,
        ],
      ];
      $rows[] = $row;
    }
    return [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No books available.'),
    ];
  }

}
