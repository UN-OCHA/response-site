<?php

namespace Drupal\ocha_docstore_files\Plugin\search_api\processor;

use Drupal\search_api\Processor\ProcessorPluginBase;

/**
 * Store facet data for entity reference fields as `uuid:label`.
 *
 * Note: this applies to field with the `__facet`.
 *
 * @see \Drupal\ocha_docstore_files\Plugin\facets\processor\ChangeStringQueryType
 * @see \Drupal\ocha_docstore_files\Plugin\facets\query_tyoe\SearchApiString
 *
 * @SearchApiProcessor(
 *   id = "ar_entity_reference_facet",
 *   label = @Translation("AR entity reference facet"),
 *   description = @Translation("Concatenate the uuid and label of entity reference fields."),
 *   stages = {
 *     "preprocess_index" = 20,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class EntityReferenceFacet extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessIndexItems(array $items) {
    foreach ($items as $item) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $item->getOriginalObject()->getEntity();

      // Concatenate the entity uuid and entity label for facet fields
      // so that we can extract the label without any other lookup.
      $fields = $item->getFields(FALSE);
      foreach ($fields as $name => $field) {
        if (strpos($name, '__facet') !== FALSE) {
          $field_name = strtok($field->getPropertyPath(), ':');
          $field_item_list = $entity->get($field_name);

          if (!empty($field_item_list)) {
            $values = [];
            foreach ($field_item_list->filterEmptyItems() as $field_item) {
              $field_entity = $field_item->entity;
              if (!empty($field_entity)) {
                $values[] = $field_entity->id() . ':' . $field_entity->label();
              }

              if (isset($field_entity->field_parent)) {
                foreach ($field_entity->field_parent->referencedEntities() as $parent) {
                  $values = array_merge($values, $this->getParentValues($parent));
                }
              }
            }
            $field->setValues($values);
          }
        }
      }
    }
  }

  /**
   * Get all parents.
   */
  protected function getParentValues($child) {
    $parents = [
      $child->id() . ':' . $child->label(),
    ];

    if (isset($child->field_parent)) {
      foreach ($child->field_parent->referencedEntities() as $parent) {
        $parents = array_merge($parents, $this->getParentValues($parent));
      }
    }

    return $parents;
  }

}
