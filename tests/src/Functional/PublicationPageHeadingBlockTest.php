<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

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
    'layout_paragraphs',
    'localgov_publications',
  ];

  /**
   * Test the heading block displays correct information.
   */
  public function testHeadingBlockIsConsistent() {

    $adminUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'administer node fields',
    ]);

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
    $this->assertSession()->responseContains('Publication parent page');
    $this->assertSession()->responseContains('<span>Published:</span> 16 April 2023');
    $this->assertSession()->responseContains('<span>Last updated:</span> 20 April 2023');

    // Child page one.
    $this->drupalGet('/node/' . $node_child_one->id());
    $this->assertSession()->responseContains('Publication parent page');
    $this->assertSession()->responseContains('Publication child page one');
    $this->assertSession()->responseContains('<span>Published:</span> 16 April 2023');
    $this->assertSession()->responseContains('<span>Last updated:</span> 20 April 2023');

    // Child page two.
    $this->drupalGet('/node/' . $node_child_two->id());
    $this->assertSession()->responseContains('Publication parent page');
    $this->assertSession()->responseContains('Publication child page two');
    $this->assertSession()->responseContains('<span>Published:</span> 16 April 2023');
    $this->assertSession()->responseContains('<span>Last updated:</span> 20 April 2023');

    // Reload the node so it's fully populated.
    $node_parent = Node::load($node_parent->id());

    // Update the 'Last updated' date on the parent page.
    $node_parent->localgov_updated_date->setValue(date('Y-m-d', mktime(0, 0, 0, 4, 21, 2023)));
    $node_parent->save();

    // Check date updated on the parent page.
    $this->drupalGet('/node/' . $node_parent->id());
    $this->assertSession()->responseContains('<span>Last updated:</span> 21 April 2023');

    // Check date updated on a child page.
    $this->drupalGet('/node/' . $node_child_one->id());
    $this->assertSession()->responseContains('<span>Last updated:</span> 21 April 2023');
  }

}
