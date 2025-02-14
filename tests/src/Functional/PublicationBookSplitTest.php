<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\NodeInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Tests LocalGov Publications books are split from Drupal books.
 *
 * @group localgov_publications
 */
class PublicationBookSplitTest extends BrowserTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';


  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_paragraphs',
    'localgov_publications',
  ];

  /**
   * Test the book selection dropdown filters books and publications.
   *
   * This is so that publication pages can only be in publications, and other
   * Drupal books do not contain publication pages.
   */
  public function testNodeBookSelectorFiltersBooks() :void {

    // Set up a publications node.
    $publication_node = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Test publication page',
      'status' => NodeInterface::PUBLISHED,
      'book' => [
        'bid' => 'new',
      ],
    ]);

    // Set up a standard book node.
    $this->createContentType([
      'type' => 'book',
    ]);
    $book_node = $this->createNode([
      'type' => 'book',
      'title' => 'Test book',
      'status' => NodeInterface::PUBLISHED,
      'book' => [
        'bid' => 'new',
      ],
    ]);

    // Set up a book administrator.
    $bookAdministrator = $this->createUser([
      'administer book outlines',
      'bypass node access',
      'administer nodes',
      'create new books',
      'add content to books',
    ]);
    $this->drupalLogin($bookAdministrator);

    // Create a publication node and check books are filtered.
    $this->drupalGet('/node/add/localgov_publication_page');

    // Get the book select widget.
    $query = $this->xpath('.//select[@name="book[bid]"]//option');
    $options = [];
    foreach ($query as $option) {
      $options[$option->getAttribute('value')] = $option->getText();
    }

    $expected = [
      0 => '- None -',
      'new' => '- Create a new publication -',
      $publication_node->id() => 'Test publication page',
    ];

    $this->assertEquals($expected, $options);

    // // Create a book node and check publications are filtered.
    $this->drupalGet('/node/add/book');

    // Get the book select widget.
    $query = $this->xpath('.//select[@name="book[bid]"]//option');
    $options = [];
    foreach ($query as $option) {
      $options[$option->getAttribute('value')] = $option->getText();
    }

    $expected = [
      0 => '- None -',
      'new' => '- Create a new book -',
      $book_node->id() => 'Test book',
    ];

    $this->assertEquals($expected, $options);
  }

}
