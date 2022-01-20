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
   * Helper to check if tab is active.
   */
  protected function tabIsActive($group, $tab) {
    if (is_numeric($group)) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    $enabled_tabs = $group->field_enabled_tabs->getValue();
    array_walk($enabled_tabs, function (&$item) {
      $item = $item['value'];
    });

    return AccessResult::allowedIf(in_array($tab, $enabled_tabs));
  }

  /**
   * Check if offices is enabled.
   */
  public function hasOffices($group) {
    return $this->tabIsActive($group, 'offices');
  }

  /**
   * Check if assessments is enabled.
   */
  public function hasAssessments($group) {
    return $this->tabIsActive($group, 'assessments');
  }

  /**
   * Check if events is enabled.
   */
  public function hasEvents($group) {
    return $this->tabIsActive($group, 'events');
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

  /**
   * Return all assessments of an operation, sector or cluster.
   */
  public function getAssessments($group) {
    if ($group->field_operation->isEmpty()) {
      return array(
        '#type' => 'markup',
        '#markup' => $this->t('Operation not set.'),
      );
    }

    $operation_uuid = $group->field_operation->entity->uuid();

    $entity_id = 'assessment';
    $view_mode = 'teaser';

    $assessment_uuids = $this->entityTypeManager->getStorage($entity_id)->getQuery()
      ->condition('operations', $operation_uuid)
      ->range(0, 25)
      ->sort('changed', 'DESC')
      ->execute();
    $assessments = $this->entityTypeManager->getStorage($entity_id)->loadMultiple($assessment_uuids);

    $view_builder = $this->entityTypeManager->getViewBuilder($entity_id);
    return $view_builder->viewMultiple($assessments, $view_mode);
  }

  /**
   * Return all events of an operation, sector or cluster.
   */
  public function getEvents($group) {
    return [
      'calendar' => [
        '#type' => 'inline_template',
        '#template' => '<iframe src="https://calendar.google.com/calendar/embed?height=600&wkst=2&bgcolor=%23ffffff&ctz=Asia%2FDamascus&showTitle=0&showPrint=0&showCalendars=0&src=M3Rha2FwdmgxZWpiMHFmZzAyaDFsMWRhMXNAZ3JvdXAuY2FsZW5kYXIuZ29vZ2xlLmNvbQ&src=ZW4uYmUjaG9saWRheUBncm91cC52LmNhbGVuZGFyLmdvb2dsZS5jb20&src=ZW4udXNhI2hvbGlkYXlAZ3JvdXAudi5jYWxlbmRhci5nb29nbGUuY29t&color=%237986CB&color=%237986CB&color=%237986CB" style="border-width:0" width="800" height="600" frameborder="0" scrolling="no"></iframe>',
      ],
      'link' => [
        '#type' => 'inline_template',
        '#template' => '<a href="https://calendar.google.com/calendar/u/0?cid=M3Rha2FwdmgxZWpiMHFmZzAyaDFsMWRhMXNAZ3JvdXAuY2FsZW5kYXIuZ29vZ2xlLmNvbQ">Link to calendar</a>',
      ],
    ];
  }

}
