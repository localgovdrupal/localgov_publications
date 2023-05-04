<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\BrowserTestBase;

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
    ]);
    $this->drupalGet('/publications/test-publication-landing-page');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('Test publication landing page');
  }

}
