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
  public function testPublicationCoverPage() {
    $node = $this->createNode([
      'type' => 'localgov_publication_cover_page',
      'title' => 'Test publication cover page',
      'status' => NodeInterface::PUBLISHED,
      'book' => [
        'bid' => '0',
      ],
    ]);
    $this->assertSame('/publications/test-publication-cover-page', $node->toUrl()->toString());
  }

  /**
   * Verifies the Publication page URL alias when there is no cover page.
   */
  public function testPublicationPageWithoutCoverPage() {
    $parentNode = $this->createNode([
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
    $this->assertSame('/publication-parent-page', $parentNode->toUrl()->toString());

    $childNode = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'book' => [
        'bid' => $parentNode->id(),
        'pid' => $parentNode->id(),
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->assertSame('/publication-parent-page/publication-child-page', $childNode->toUrl()->toString());
  }

  /**
   * Verifies the Publication page URL alias when there is a cover page.
   */
  public function testPublicationPageWithCoverPage() {
    $parentNode = $this->createNode([
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

    $childNode = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'book' => [
        'bid' => $parentNode->id(),
        'pid' => $parentNode->id(),
        'weight' => '0',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->createNode([
      'type' => 'localgov_publication_cover_page',
      'title' => 'Test publication cover page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_publication' => [
        ['target_id' => $parentNode->id()],
      ],
      'book' => [
        'bid' => '0',
      ],
    ]);

    $this->assertSame('/publications/test-publication-cover-page/publication-parent-page', $parentNode->toUrl()->toString());
    $this->assertSame('/publications/test-publication-cover-page/publication-parent-page/publication-child-page', $childNode->toUrl()->toString());
  }

  /**
   * Check publication page URL alias when the root's alias has been changed.
   *
   * (See https://github.com/localgovdrupal/localgov_publications/issues/201).
   */
  public function testPublicationPageWithCustomAlias() {
    $parentNode = $this->createNode([
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
      'path' => [
        'alias' => '/custom-alias/publication-parent-page',
        'pathauto' => 0,
      ],
    ]);

    $childNode = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'book' => [
        'bid' => $parentNode->id(),
        'pid' => $parentNode->id(),
        'weight' => '0',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->assertSame('/custom-alias/publication-parent-page', $parentNode->toUrl()->toString());
    $this->assertSame('/custom-alias/publication-parent-page/publication-child-page', $childNode->toUrl()->toString());
  }

}
