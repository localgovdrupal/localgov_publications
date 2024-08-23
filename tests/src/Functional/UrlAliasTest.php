<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the URL aliases.
 *
 * Tests for correctly formed URL aliases on
 * publication pages and publication cover pages.
 *
 * @group localgov_publications
 */
class UrlAliasTest extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'localgov';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_base';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_paragraphs',
    'localgov_publications',
  ];

  /**
   * Verifies the publication cover page URL alias.
   */
  public function testPublicationCoverPageUrlAlias() {
    $this->createNode([
      'type' => 'localgov_publication_cover_page',
      'title' => 'Test publication cover page',
      'status' => NodeInterface::PUBLISHED,
      'book' => [
        'bid' => '0',
      ],
    ]);
    $this->drupalGet('/publications/test-publication-cover-page');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('Test publication cover page');
  }

  /**
   * Verifies the Publication page URL alias when there is no cover page.
   */
  public function testPublicationPageWithoutCoverPageAlias() {
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
    $this->drupalGet('/publication-parent-page');
    $this->assertSession()->statusCodeEquals(200);

    $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'book' => [
        'bid' => $node_parent->id(),
        'pid' => $node_parent->id(),
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->drupalGet('/publication-parent-page/publication-child-page');
    $this->assertSession()->statusCodeEquals(200);

  }

  /**
   * Verifies the Publication page URL alias when there is a cover page.
   */
  public function testPublicationPageWithCoverPageAlias() {
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
        'weight' => '0',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'book' => [
        'bid' => $node_parent->id(),
        'pid' => $node_parent->id(),
        'weight' => '0',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->createNode([
      'type' => 'localgov_publication_cover_page',
      'title' => 'Test publication cover page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_publication' => [
        ['target_id' => $node_parent->id()],
      ],
      'book' => [
        'bid' => '0',
      ],
    ]);

    $this->drupalGet('/publications/test-publication-cover-page/publication-parent-page');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalGet('/publications/test-publication-cover-page/publication-parent-page/publication-child-page');
    $this->assertSession()->statusCodeEquals(200);
  }

}
