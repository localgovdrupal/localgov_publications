<?php

namespace Drupal\Tests\localgov_publications\Functional;

use Drupal\Tests\BrowserTestBase;

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

    // Uninstall module.
    $moduleInstaller->uninstall(['localgov_publications']);

    // Confirm module has been uninstalled.
    $this->assertFalse($moduleHandler->moduleExists('localgov_publications'));

    // Install module.
    $this->assertTrue($moduleInstaller->install(['localgov_publications']));
  }

}
