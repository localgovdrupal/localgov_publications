<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests LocalGov Publications page.
 *
 * @group localgov_publications
 */
class PublicationPageTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'localgov';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_base';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_paragraphs',
    'localgov_publications',
    'field_ui',
  ];

  /**
   * Verifies basic functionality with all modules.
   */
  public function testPublicationPageFields() {

    $adminUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'administer node fields',
    ]);

    $this->drupalLogin($adminUser);

    // Check publication page fields.
    $this->drupalGet('/admin/structure/types/manage/localgov_publication_page/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('body');
    $this->assertSession()->pageTextContains('localgov_publication_content');
    $this->assertSession()->pageTextContains('localgov_published_date');
    $this->assertSession()->pageTextContains('localgov_updated_date');
  }

}
