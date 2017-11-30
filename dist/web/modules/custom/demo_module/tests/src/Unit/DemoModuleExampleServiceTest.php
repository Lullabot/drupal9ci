<?php

namespace Drupal\Tests\demo_module\Unit;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\demo_module\DemoModuleExampleService;
use Drupal\node\NodeStorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests DemoModuleExampleService.
 *
 * @group demo_module
 * @coversDefaultClass \Drupal\demo_module\DemoModuleExampleService
 */
class DemoModuleExampleServiceTest extends TestCase {

  /**
   * An instance of DemoModuleExampleService.
   *
   * @var \Drupal\demo_module\DemoModuleExampleService
   */
  protected $demoModuleExampleService;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $node_storage_interface = $this->prophesize(NodeStorageInterface::class);
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getStorage('node')
      ->willReturn($node_storage_interface->reveal());

    $this->demoModuleExampleService = new DemoModuleExampleService($entity_type_manager->reveal());
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(DemoModuleExampleService::class, $this->demoModuleExampleService);
  }

  /**
   * @covers ::getLastNode
   */
  public function testGetLastNode() {
    /** @var \Prophecy\Prophecy\ObjectProphecy $query_interface */
    $query_interface = $this->prophesize(QueryInterface::class);
    $query_interface->sort('created', 'DESC')
      ->willReturn($query_interface->reveal());
    $query_interface->range(0, 1)
      ->willReturn($query_interface->reveal());
    $query_interface->execute()
      ->willReturn([1]);

    /** @var \Prophecy\Prophecy\ObjectProphecy $query_interface */
    $node_storage_interface = $this->prophesize(NodeStorageInterface::class);
    $node_storage_interface->getQuery()
      ->willReturn($query_interface->reveal());

    /** @var \Prophecy\Prophecy\ObjectProphecy $query_interface */
    $entity_interface = $this->prophesize(EntityInterface::class);
    $node_storage_interface->load(1)
      ->willReturn($entity_interface->reveal());

    /** @var \Prophecy\Prophecy\ObjectProphecy $query_interface */
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getStorage('node')
      ->willReturn($node_storage_interface->reveal());

    $this->demoModuleExampleService = new DemoModuleExampleService($entity_type_manager->reveal());

    $this->assertInstanceOf(EntityInterface::class, $this->demoModuleExampleService->getLastNode());
  }

}
