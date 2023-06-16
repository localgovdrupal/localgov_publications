<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Publication navigation tests.
 */
class PublicationPageHeadingBlockTest extends BrowserTestBase {

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
  public function testHeadingBlockIsConsistent() {
    $adminUser = $this->drupalCreateUser([], NULL, TRUE);

    $node_parent = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication parent page',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'localgov_published_date' => date('Y-m-d', mktime(0, 0, 0, 4, 16, 2023)),
      'localgov_updated_date' => date('Y-m-d', mktime(0, 0, 0, 4, 20, 2023)),
      'book' => [
        'bid' => 'new',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $node_child_one = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page one',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'localgov_published_date' => date('Y-m-d', mktime(0, 0, 0, 4, 16, 2023)),
      'localgov_updated_date' => date('Y-m-d', mktime(0, 0, 0, 4, 20, 2023)),
      'book' => [
        'bid' => $node_parent->id(),
        'pid' => $node_parent->id(),
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $node_child_two = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page two',
      'body' => [
        'summary' => '<p>Content</p>',
        'value' => '<p>Content</p>',
        'format' => 'wysiwyg',
      ],
      'localgov_published_date' => date('Y-m-d', mktime(0, 0, 0, 4, 16, 2023)),
      'localgov_updated_date' => date('Y-m-d', mktime(0, 0, 0, 4, 20, 2023)),
      'book' => [
        'bid' => $node_parent->id(),
        'pid' => $node_parent->id(),
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalLogin($adminUser);

    // Top level parent page.
    $this->drupalGet('/node/' . $node_parent->id());
    $this->assertSession()->responseContains('<h1 class="lgd-page-title-block__title">Publication parent page</h1>');
    $this->assertSession()->responseContains('<div><span>Published: </span>16 April 2023</div>');
    $this->assertSession()->responseContains('<div><span>Last updated: </span>20 April 2023</div>');

    // Child page one.
    $this->drupalGet('/node/' . $node_child_one->id());
    $this->assertSession()->responseContains('<h1 class="lgd-page-title-block__title">Publication parent page</h1>');
    $this->assertSession()->responseContains('<div><span>Published: </span>16 April 2023</div>');
    $this->assertSession()->responseContains('<div><span>Last updated: </span>20 April 2023</div>');

    // Child page two.
    $this->drupalGet('/node/' . $node_child_two->id());
    $this->assertSession()->responseContains('<h1 class="lgd-page-title-block__title">Publication parent page</h1>');
    $this->assertSession()->responseContains('<div><span>Published: </span>16 April 2023</div>');
    $this->assertSession()->responseContains('<div><span>Last updated: </span>20 April 2023</div>');
  }

}
