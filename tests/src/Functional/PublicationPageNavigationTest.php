<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\node\NodeInterface;

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

    $node_child_one = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page one',
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

    $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Publication child page two',
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

    $this->drupalLogin($adminUser);
    $this->drupalGet('/node/' . $node_child_one->id());
    $this->assertSession()->responseContains('<a href="/node/1" rel="prev" title="Go to previous page"><b>‹ Previous: </b> Publication parent page</a>');
    $this->assertSession()->responseContains('<a href="/node/3" rel="next" title="Go to next page">Next: Publication child page two <b>›</b></a>');
  }
}
