<?php

namespace Drupal\Tests\demo_module\ExistingSiteJavascript;

use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;

/**
 * Sample class to test the existing site.
 *
 * @group demo_module
 */
class ExistingSiteTest extends ExistingSiteWebDriverTestBase {

  /**
   * Checks that a node exists.
   */
  public function testNodeExists() {
    $this->visit('/node/1');
    $web_assert = $this->assertSession();
    $web_assert->pageTextContains('Sample article');
  }

}
