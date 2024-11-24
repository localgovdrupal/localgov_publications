<?php

namespace Drupal\Tests\localgov_publications\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Link;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Url;
use Drupal\Tests\UnitTestCase;
use Drupal\localgov_publications\Service\HeadingFinder;

/**
 * Unit tests for the HeadingFinder.
 */
class HeadingFinderTest extends UnitTestCase {

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();

    $pathValidator = $this->createMock(PathValidatorInterface::class);

    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
    $container->set('path.validator', $pathValidator);
  }

  /**
   * Data provider for ::searchMarkup.
   */
  public static function contentProvider() {
    // Check multiple headings can be found.
    yield [
      'markup' => '<h2 id="heading-1">Heading 1</h2><p>Content 1.</p><h2 id="heading-2">Heading 2</h2><p>Content 2.</p>',
      'expectedLinks' => [
        new Link('Heading 1', Url::fromUri('base:<none>', ['fragment' => 'heading-1'])),
        new Link('Heading 2', Url::fromUri('base:<none>', ['fragment' => 'heading-2'])),
      ],
    ];
    // Check the id still gets found in other attributes.
    yield [
      'markup' => '<h2 class="foo" id="heading-1" lang="en">Heading 1</h2><p>Content 1.</p>',
      'expectedLinks' => [
        new Link('Heading 1', Url::fromUri('base:<none>', ['fragment' => 'heading-1'])),
      ],
    ];
    // Check none are found if there aren't any.
    yield [
      'markup' => '<p>Content 1.</p><p>Content 2.</p>',
      'expectedLinks' => [],
    ];
    // Check HTML entities are handled correctly.
    yield [
      'markup' => '<h2 id="hammersmith-fulham">Hammersmith &amp; Fulham</h2><p>Hammersmith &amp; Fulham is a London borough in West London.</p>',
      'expectedLinks' => [
        new Link('Hammersmith & Fulham', Url::fromUri('base:<none>', ['fragment' => 'hammersmith-fulham'])),
      ],
    ];
  }

  /**
   * Tests HeadingFinder::searchMarkup().
   *
   * @dataProvider contentProvider
   */
  public function testSearchMarkup(string $markup, array $expectedLinks) {

    $headingFinder = new HeadingFinder();
    $links = $headingFinder->searchMarkup($markup);

    $this->assertEquals($expectedLinks, $links);
  }

}
