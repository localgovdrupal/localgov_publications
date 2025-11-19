<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\NodeInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Symfony\Component\HttpFoundation\Response;

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
   * {@inheritdoc}
   */
  public function setUp() :void {
    parent::setUp();

    // Set up a book administrator.
    $bookAdministrator = $this->createUser([
      'administer book outlines',
      'bypass node access',
      'administer nodes',
      'create new books',
      'add content to books',
    ]);

    $this->drupalLogin($bookAdministrator);
  }

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

    // Create a book node and check publications are filtered.
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

    // Test filters apply to edit pages.
    $publication_node_2 = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Test publication page 2',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet($publication_node_2->toUrl('edit-form')->toString());

    // Get the book select widget.
    $query = $this->xpath('.//select[@name="book[bid]"]//option');
    $options = [];
    foreach ($query as $option) {
      $options[$option->getAttribute('value')] = $option->getText();
    }

    $expected = [
      0 => '- None -',
      $publication_node_2->id() => '- Create a new publication -',
      $publication_node->id() => 'Test publication page',
    ];

    $this->assertEquals($expected, $options);

    // Book node edit page.
    $book_node_2 = $this->createNode([
      'type' => 'book',
      'title' => 'Test book 2',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet($book_node_2->toUrl('edit-form')->toString());

    // Get the book select widget.
    $query = $this->xpath('.//select[@name="book[bid]"]//option');
    $options = [];
    foreach ($query as $option) {
      $options[$option->getAttribute('value')] = $option->getText();
    }

    $expected = [
      0 => '- None -',
      $book_node_2->id() => '- Create a new book -',
      $book_node->id() => 'Test book',
    ];

    $this->assertEquals($expected, $options);

  }

  /**
   * Test that publication pages can be created when there are no books.
   */
  public function testPublicationNodeAddPageWithoutExistingBooks() :void {

    // Go to publication page.
    $this->drupalGet('/node/add/localgov_publication_page');

    // Test /node/add/localgov_publication_page is able to be displayed.
    // @See https://github.com/localgovdrupal/localgov_publications/issues/236
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
  }

}
