<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests LocalGov Publications landing page.
 *
 * @group localgov_publications
 */
class PublicationLandingPageTest extends BrowserTestBase {

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
   * A user with permission to bypass content access checks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'localgov_publications',
    'field_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'administer node fields',
    ]);
    $this->nodeStorage = $this->container
      ->get('entity_type.manager')
      ->getStorage('node');
  }

  /**
   * Verifies basic functionality with all modules.
   */
  public function testPublicationLandingPageFields() {
    $this->drupalLogin($this->adminUser);

    // Check publication page fields.
    $this->drupalGet('/admin/structure/types/manage/publication_landing_page/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('body');
    $this->assertSession()->pageTextContains('localgov_documents');
    $this->assertSession()->pageTextContains('localgov_pub_landing_content');
    $this->assertSession()->pageTextContains('localgov_published_date');
    $this->assertSession()->pageTextContains('field_localgov_services_landing');
    $this->assertSession()->pageTextContains('localgov_updated_date');
  }

}
