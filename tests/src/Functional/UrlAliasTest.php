<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\NodeInterface;

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
    $nodePath = Url::fromUserInput('/publications/test-publication-cover-page')->toString();
    $this->assertSame($nodePath, $node->toUrl()->toString());
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
    $parentNodePath = Url::fromUserInput('/publication-parent-page')->toString();
    $this->assertSame($parentNodePath, $parentNode->toUrl()->toString());

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
    $childNodePath = Url::fromUserInput('/publication-parent-page/publication-child-page')->toString();
    $this->assertSame($childNodePath, $childNode->toUrl()->toString());
    $this->drupalGet('/publication-parent-page/publication-child-page');
    $this->assertCount(2, $this->xpath('//a[@class="breadcrumbs__link"]'));
    $this->assertSession()->linkByHrefExists($childNodePath);
    $this->assertSession()->linkByHrefExists($parentNodePath);
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
      'title' => 'Publication cover page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_publication' => [
        ['target_id' => $parentNode->id()],
      ],
      'book' => [
        'bid' => '0',
      ],
    ]);

    $parentNodePath = Url::fromUserInput('/publications/publication-cover-page/publication-parent-page')->toString();
    $this->assertSame($parentNodePath, $parentNode->toUrl()->toString());
    $childNodePath = Url::fromUserInput('/publications/publication-cover-page/publication-parent-page/publication-child-page')->toString();
    $this->assertSame($childNodePath, $childNode->toUrl()->toString());

    $this->drupalGet('/publications/publication-cover-page/publication-parent-page');
    $coverPagePath = Url::fromUserInput('/publications/publication-cover-page')->toString();
    $this->assertSession()->linkByHrefExists($coverPagePath);
    $this->assertCount(2, $this->xpath('//a[@class="breadcrumbs__link"]'));

    $this->drupalGet('/publications/publication-cover-page/publication-parent-page/publication-child-page');
    $parentPagePath = Url::fromUserInput('/publications/publication-cover-page/publication-parent-page')->toString();
    $this->assertSession()->linkByHrefExists($parentPagePath);
    $this->assertSession()->linkByHrefExists($coverPagePath);
    $this->assertCount(3, $this->xpath('//a[@class="breadcrumbs__link"]'));
  }

  /**
   * Check publication page URL alias when the root's alias has been changed.
   *
   * (See https://github.com/localgovdrupal/localgov_publications/issues/201).
   */
  public function testPublicationPageWithCustomAlias() {

    $this->createNode([
      'type' => 'service_page',
      'title' => 'Custom Alias',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'status' => NodeInterface::PUBLISHED,
      'path' => [
        'alias' => '/custom-alias',
        'pathauto' => 0,
      ],
    ]);

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

    $parentNodePath = Url::fromUserInput('/custom-alias/publication-parent-page')->toString();
    $this->assertSame($parentNodePath, $parentNode->toUrl()->toString());
    $childNodePath = Url::fromUserInput('/custom-alias/publication-parent-page/publication-child-page')->toString();
    $this->assertSame($childNodePath, $childNode->toUrl()->toString());

    $this->drupalGet('/custom-alias/publication-parent-page');
    $customAliasPath = Url::fromUserInput('/custom-alias')->toString();
    $this->assertSession()->linkByHrefExists($customAliasPath);
    $this->assertCount(2, $this->xpath('//a[@class="breadcrumbs__link"]'));

    $this->drupalGet('/custom-alias/publication-parent-page/publication-child-page');
    $publicationParentPath = Url::fromUserInput('/custom-alias/publication-parent-page')->toString();
    $this->assertSession()->linkByHrefExists($customAliasPath);
    $this->assertSession()->linkByHrefExists($publicationParentPath);
    $this->assertCount(3, $this->xpath('//a[@class="breadcrumbs__link"]'));
  }

}
