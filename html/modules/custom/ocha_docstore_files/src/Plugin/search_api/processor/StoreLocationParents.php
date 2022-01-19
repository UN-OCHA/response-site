<?php

namespace Drupal\ocha_docstore_files\Plugin\search_api\processor;

use Drupal\search_api\Processor\ProcessorPluginBase;

/**
 * Store location parents.
 *
 * @SearchApiProcessor(
 *   id = "ar_location_parent",
 *   label = @Translation("Store location parent"),
 *   description = @Translation("Store location parent."),
 *   stages = {
 *     "preprocess_index" = 10,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class StoreLocationParents extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessIndexItems(array $items) {
    foreach ($items as $item) {
      /** @var \Drupal\Core\Entity\EntityInterface $FieldableEntityInterface */
      $entity = $item->getOriginalObject()->getEntity();

      if (!$entity->hasField('field_locations')) {
        continue;
      }

      $fields = $item->getFields(FALSE);
      $values = $fields['field_locations']->getValues();

      foreach ($entity->field_locations->referencedEntities() as $location) {
        $values = array_merge($values, $this->getParentValues($location));
      }

      if (!empty($values)) {
        $values = array_unique($values);
        $fields = $item->getFields(FALSE);
        $fields['field_locations']->setValues($values);
      }
    }
  }

  /**
   * Get all parents.
   */
  protected function getParentValues($location) {
    $parents = [
      $location->uuid(),
    ];

    if (isset($location->field_parent)) {
      foreach ($location->field_parent->referencedEntities() as $parent) {
        $parents = array_merge($parents, $this->getParentValues($parent));
      }
    }

    return $parents;
  }

}
