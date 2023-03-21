<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Functional tests for the TocBlock.
 */
class TocBlockTest extends BrowserTestBase {

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

  public function contentProvider() {
    yield [
      'content' => '<h2 id="heading-1">Heading 1</h2><p>Content 1.</p><h2 id="heading-2">Heading 2</h2><p>Content 2.</p>',
      'display' => TRUE,
    ];
    yield [
      'content' => '<p>Content 1.</p><p>Content 2.</p>',
      'display' => FALSE,
    ];
  }

  /**
   * Test the block displays with content.
   * @dataProvider contentProvider
   */
  public function testTocBlockDisplays($content, $display) {

    $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Test publication page',
      'body' => [
        'summary' => $content,
        'value' => $content,
        'format' => 'wysiwyg',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->drupalGet('/node/1');
    if ($display) {
      $this->assertSession()->responseContains('On this page');
      $this->assertSession()->responseContains('#heading-1');
      $this->assertSession()->responseContains('#heading-2');
    }
    else {
      $this->assertSession()->responseNotContains('On this page');
    }
  }
}
