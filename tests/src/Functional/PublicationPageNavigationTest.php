<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

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
    'layout_paragraphs',
    'localgov_publications',
  ];

  /**
   * Test that links and nav are present on a multi-page publication.
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
      'localgov_publication_content' => [
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
      'localgov_publication_content' => [
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
      'localgov_publication_content' => [
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

    $prevLinks = $this->xpath('//a[contains(@class, "lgd-prev-next__link--prev")]');
    $prevLink = reset($prevLinks);
    $this->assertSame("/publication-parent-page", $prevLink->getAttribute('href'));

    $nextLinks = $this->xpath('//a[contains(@class, "lgd-prev-next__link--next")]');
    $nextLink = reset($nextLinks);
    $this->assertSame("/publication-parent-page/publication-child-page-two", $nextLink->getAttribute('href'));

    // This is the default title of the publication navigation block.
    $this->assertSession()->pageTextContains('Publication navigation');
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

    // This is the default title of the publication navigation block.
    // It shouldn't show on a single page publication.
    $this->assertSession()->pageTextNotContains('Publication navigation');
  }

}
