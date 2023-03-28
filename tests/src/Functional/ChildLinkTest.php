<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Functional tests for our link modifications.
 */
class ChildLinkTest extends BrowserTestBase {

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
   * Test the 'Add child page' link on a publication goes to the right type.
   */
  public function testAddChildPageLink() {

    $adminUser = $this->drupalCreateUser([], NULL, TRUE);

    $node = $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Test publication page',
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

    $this->drupalLogin($adminUser);
    $this->drupalGet('/node/' . $node->id());
    $this->assertSession()->responseContains('<a href="/node/add/localgov_publication_page?parent=1">Add child page</a>');
  }

}
