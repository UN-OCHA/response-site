<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;

/**
 * Page controller for tabs.
 */
class ParagraphController extends ControllerBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Drupal\Core\Entity\EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Check if offices is enabled.
   */
  public function hasOffices($group) {
    if (is_numeric($group)) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    $enabled_tabs = $group->field_enabled_tabs->getValue();
    array_walk($enabled_tabs, function (&$item) {
      $item = $item['value'];
    });

    return AccessResult::allowedIf(in_array('offices', $enabled_tabs));
  }

  /**
   * Return all offices of an operation, sector or cluster.
   */
  public function getOffices($group) {
    if ($group->field_operation->isEmpty()) {
      return array(
        '#type' => 'markup',
        '#markup' => $this->t('Operation not set.'),
      );
    }

    $operation_uuid = $group->field_operation->entity->uuid();

    $entity_id = 'office';
    $view_mode = 'teaser';

    $office_uuids = $this->entityTypeManager->getStorage($entity_id)->getQuery()->condition('operations', $operation_uuid)->execute();
    $offices = $this->entityTypeManager->getStorage($entity_id)->loadMultiple($office_uuids);

    $view_builder = $this->entityTypeManager->getViewBuilder($entity_id);
    return $view_builder->viewMultiple($offices, $view_mode);
  }

}
