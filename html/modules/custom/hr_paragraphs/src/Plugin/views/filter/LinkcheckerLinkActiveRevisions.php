<?php

namespace Drupal\hr_paragraphs\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Views;

/**
 * Field handler to filter active revisions.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsFilter("linkchecker_link_active_revisions")
 */
class LinkcheckerLinkActiveRevisions extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    return 'Active only';
  }

  /**
   * {@inheritdoc}
   */
  public function canExpose() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    $entity_type_manager = \Drupal::entityTypeManager();

    $fields = $entity_type_manager->getStorage('field_storage_config')->loadByProperties([
      'type' => 'entity_reference_revisions',
      'settings' => [
        'target_type' => 'paragraph',
      ],
    ]);

    // Force numeric ids.
    $fields = array_values($fields);
    $wherefields = [];

    /** @var \Drupal\field\Entity\FieldStorageConfig $field */
    foreach ($fields as $id => $field) {
      // Skip nested paragraphs.
      if ($field->getTargetEntityTypeId() == 'paragraph') {
        continue;
      }

      $configuration = [
        'type' => 'LEFT',
        'table' => $field->getTargetEntityTypeId() . '__' . $field->getName(),
        'field' => $field->getName() . '_target_id',
        'left_table' => 'linkchecker_link',
        'left_field' => 'parent_entity_id',
        'operator' => '=',
      ];

      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $query->addRelationship('lclf' . $id, $join, 'linkchecker_link');
      $wherefields[] = 'lclf' . $id . '.' . $field->getName() . '_target_id IS NOT NULL';
    }

    // Check that at least one isn't NULL.
    if (!empty($wherefields)) {
      $query->addWhereExpression('activeonly', implode(' OR ', $wherefields));
    }
  }

}
