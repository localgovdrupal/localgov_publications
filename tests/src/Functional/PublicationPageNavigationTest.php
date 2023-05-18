<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\BrowserTestBase;
use Drupal\node\NodeInterface;

/**
 * Publication navigation tests.
 */
class PublicationPageNavigationTest extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_base';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'localgov';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'localgov_publications',
  ];

  /**
   * Test the 'next page' link on a publication.
   */
  public function testPreviousNextLinks() {
    $adminUser = $this->drupalCreateUser([], NULL, TRUE);

    // Create a text paragraph.
    $text_paragraph = Paragraph::create([
      'type' => 'localgov_text',
      'localgov_text' => [
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
    ]);
    $text_paragraph->save();

    $node_parent = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication parent page',
      'localgov_page_content' => [
        'target_id' => $text_paragraph->id(),
        'target_revision_id' => $text_paragraph->getRevisionId(),
      ],
      'book' => [
        'bid' => 'new',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $node_child_one = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page one',
      'localgov_page_content' => [
        'target_id' => $text_paragraph->id(),
        'target_revision_id' => $text_paragraph->getRevisionId(),
      ],
      'book' => [
        'bid' => $node_parent->id(),
        'pid' => $node_parent->id(),
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page two',
      'localgov_page_content' => [
        'target_id' => $text_paragraph->id(),
        'target_revision_id' => $text_paragraph->getRevisionId(),
      ],
      'book' => [
        'bid' => $node_parent->id(),
        'pid' => $node_parent->id(),
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalLogin($adminUser);
    $this->drupalGet('/node/' . $node_child_one->id());
    $this->assertSession()->responseContains('<a href="/node/1" rel="prev" title="Go to previous page"><b>‹ Previous: </b> Publication parent page</a>');
    $this->assertSession()->responseContains('<a href="/node/3" rel="next" title="Go to next page">Next: Publication child page two <b>›</b></a>');
  }

  /**
   * Test the 'book navigation' block is not displayed on single page books.
   */
  public function testBookNavigationIsNotDisplayed() {
    $node_parent = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication parent page',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'book' => [
        'bid' => 'new',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->drupalGet('/node/' . $node_parent->id());
    $this->assertSession()->elementNotExists('css', '#block-booknavigation');
  }

}
