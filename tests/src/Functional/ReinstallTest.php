<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\filter\Entity\FilterFormat;
use Drupal\filter\FilterFormatInterface;

/**
 * Tests module reinstallation.
 *
 * This checks that the module cleans up after itself when uninstalled. If it
 * can be successfully reinstalled, we'll assume the cleanup was sufficient.
 *
 * @group localgov_publications
 */
class ReinstallTest extends BrowserTestBase {

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
   * Test that the module can be reinstalled.
   */
  public function testReinstall() {

    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
    $moduleHandler = $this->container->get('module_handler');

    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller */
    $moduleInstaller = $this->container->get('module_installer');

    // Confirm module has been installed.
    $this->assertTrue($moduleHandler->moduleExists('localgov_publications'));

    // Remove the localgov_publications_heading_ids filter.
    $this->removeFilter();

    // Uninstall module.
    $moduleInstaller->uninstall(['localgov_publications']);

    // Confirm module has been uninstalled.
    $this->assertFalse($moduleHandler->moduleExists('localgov_publications'));

    // Install module.
    $this->assertTrue($moduleInstaller->install(['localgov_publications']));
  }

  /**
   * Removes the localgov_publications_heading_ids filter.
   *
   * To allow localgov_publications to be uninstalled, the
   * localgov_publications_heading_ids filter must be removed from the wysiwyg
   * filter format. People that wish to remove this module will need
   * to do it manually, but we'll automate it for the purposes of letting this
   * test run.
   */
  protected function removeFilter(): void {
    $wysiwygFormat = FilterFormat::load('wysiwyg');
    $this->assertInstanceOf(FilterFormatInterface::class, $wysiwygFormat);

    // We need to call this to let the next call to removeFilter() work. If you
    // don't call this first, the object's filterCollection property isn't set.
    $wysiwygFormat->filters('localgov_publications_heading_ids');

    $wysiwygFormat->removeFilter('localgov_publications_heading_ids');
    $wysiwygFormat->save();
  }

}
