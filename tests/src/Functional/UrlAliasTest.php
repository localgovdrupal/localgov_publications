<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the URL aliases.
 *
 * Tests for correctly formed URL aliases on
 * publication pages and publication landing pages.
 *
 * @group localgov_publications
 */
class UrlAliasTest extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * Test breadcrumbs in the Standard profile.
   *
   * @var string
   */
  protected $profile = 'localgov';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_base';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'localgov_publications',
  ];

  /**
   * Verifies the publication landing page URL alias.
   */
  public function testPublicationLandingPageUrlAlias() {
    $this->createNode([
      'type' => 'publication_landing_page',
      'title' => 'Test publication landing page',
      'status' => NodeInterface::PUBLISHED,
      'book' => [
        'bid' => '0',
      ],
    ]);
    $this->drupalGet('/publications/test-publication-landing-page');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('Test publication landing page');
  }

  /**
   * Verifies the Publication page URL alias when there is no landing page.
   */
  public function testPublicationPageWithoutLandingPageAlias() {
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
   * Verifies the Publication page URL alias when there is a landing page.
   */
  public function testPublicationPageWithLandingPageAlias() {
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

    $node_child = $this->createNode([
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
      'type' => 'publication_landing_page',
      'title' => 'Test publication landing page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_publication' => [
        ['target_id' => $node_parent->id()],
      ],
      'book' => [
        'bid' => '0',
      ],
    ]);

    // Re-save the publication pages so that the reference from the landing page
    // takes effect on the URL aliases.
    $node_parent->save();
    $node_child->save();

    $this->drupalGet('/publications/test-publication-landing-page/publication-parent-page');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalGet('/publications/test-publication-landing-page/publication-parent-page/publication-child-page');
    $this->assertSession()->statusCodeEquals(200);

  }

}
