<?php

namespace Drupal\Tests\demo_module\Kernel;

use Drupal\block\Entity\Block;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test description.
 *
 * @group demo_module
 */
class ExampleTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Mock required services here.
  }

  /**
   * Test callback.
   */
  public function testNetwork() {
    $test_variable = "Not Empty";
    $this->assertNotEmpty($test_variable, "This variable shouldn't be empty.");
  }

}
