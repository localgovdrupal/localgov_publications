<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Functional tests for the TocBlock.
 */
class AdminTest extends BrowserTestBase {

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
   * Test that publications are not listed on the Book overview page.
   */
  public function testPublicationsAreNotListedOnBookOverview() {

    $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Test publication page',
      'status' => NodeInterface::PUBLISHED,
      'book' => [
        'bid' => 'new',
      ],
    ]);
    $this->createNode([
      'type' => 'book',
      'title' => 'Test book',
      'status' => NodeInterface::PUBLISHED,
      'book' => [
        'bid' => 'new',
      ],
    ]);
    $this->drupalGet('/admin/structure/book');

    $this->assertSession()->linkExists('Test publication page');
    $this->assertSession()->linkNotExists('Test book');
  }

}
