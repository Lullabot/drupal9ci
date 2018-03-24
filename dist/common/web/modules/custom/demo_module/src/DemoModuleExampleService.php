<?php

namespace Drupal\demo_module;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * DemoModuleExampleService service.
 */
class DemoModuleExampleService {

  /**
   * Node storage.
   *
   * @var \Drupal\Node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructs a DemoModuleExampleService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * Retrieves the last created node.
   *
   * @return \Drupal\Core\Entity\EntityInterface|false
   *   A node entity or FALSE if none was found.
   */
  public function getLastNode() {
    $nids = $this->nodeStorage->getQuery()
      ->sort('created', 'DESC')
      ->range(0, 1)
      ->execute();
    $nid = reset($nids);
    return $nid ? $this->nodeStorage->load($nid) : FALSE;
  }

}
