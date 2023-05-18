<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\BrowserTestBase;
use Drupal\node\NodeInterface;

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

  /**
   * Data provider for testing the ToC Block.
   */
  public function contentProvider() {
    yield [
      'content' => '<h2 id="heading-1">Heading 1</h2><p>Content 1.</p><h2 id="heading-2">Heading 2</h2><p>Content 2.</p>',
      'display' => TRUE,
      'expectedIDs' => ['#heading-1', '#heading-2'],
    ];
    yield [
      'content' => '<p>Content 1.</p><p>Content 2.</p>',
      'display' => FALSE,
      'expectedIDs' => [],
    ];
  }

  /**
   * Test the block displays with content.
   *
   * @dataProvider contentProvider
   */
  public function testTocBlockDisplays(string $content, bool $display, array $expectedIDs) {

    // Create a text paragraph.
    $text_paragraph = Paragraph::create([
      'type' => 'localgov_text',
      'localgov_text' => [
        'value' => $content,
        'format' => 'wysiwyg',
      ],
    ]);
    $text_paragraph->save();

    $this->createNode([
      'type' => 'localgov_publication_page',
      'title' => 'Test publication page',
      'localgov_page_content' => [
        'target_id' => $text_paragraph->id(),
        'target_revision_id' => $text_paragraph->getRevisionId(),
      ],
      'status' => NodeInterface::PUBLISHED,
      'book' => ['bid' => '0'],
    ]);
    $this->drupalGet('/node/1');

    if ($display) {
      $this->assertSession()->responseContains('On this page');
    }
    else {
      $this->assertSession()->responseNotContains('On this page');
    }

    foreach ($expectedIDs as $expectedID) {
      $this->assertSession()->responseContains($expectedID);
    }
  }

}
