<?php

namespace Drupal\ocha_docstore_files\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Store an entity and the entities it references.
 *
 * @SearchApiProcessor(
 *   id = "ar_store_country",
 *   label = @Translation("Store the country"),
 *   description = @Translation("Store the country (admin level 0)."),
 *   stages = {
 *     "add_properties" = 20,
 *     "alter_items" = -10,
 *   },
 *   locked = false,
 *   hidden = false,
 * )
 */
class StoreCountry extends ProcessorPluginBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    // Inject the services.
    //
    // We cannot easily inject our services by adding parameters to the
    // constructor because of the way the search API plugins we extend, add
    // services in their ::create(). So we add them here.
    //
    // @see Drupal\search_api\Processor\ProcessorPluginBase::create()
    $processor->entityTypeManager = $container->get('entity_type.manager');

    return $processor;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    // This check means the field the is not tied to a data source and will
    // appear under "General" in the selectable fields in the UI.
    if (empty($datasource)) {
      $definition = [
        'label' => $this->t('Stored country'),
        'description' => $this->t('Stores the country.'),
        'type' => 'solr_string_storage',
        'processor_id' => $this->getPluginId(),
        'is_list' => TRUE,
      ];
      // Using an underscore at the beginning to avoid clash with custom
      // fields added by the providers.
      $properties['_stored_country'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {
    // Get the storage field.
    $index_storage_field = $this->index->getField('_stored_country');

    // Skip if the index doesn't use the stored entity field.
    if (empty($index_storage_field)) {
      return;
    }

    // Extract the referenced entites for each item to index.
    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item) {
      /** @var \Drupal\Core\Entity\EntityInterface $FieldableEntityInterface */
      $entity = $item->getOriginalObject()->getEntity();

      if (!$entity->hasField('field_locations')) {
        continue;
      }

      $countries = [];
      foreach ($entity->referencedEntities() as $location) {
        $countries = array_merge($countries, $this->getParentValues($location));
      }
      if (!empty($countries)) {
        $countries = array_unique($countries);
        $storage_field = clone $index_storage_field;
        $storage_field->setValues($countries);
        $item->setField('_stored_country', $storage_field);
      }
    }
  }

  /**
   * Find admin level 0 parents.
   */
  protected function getParentValues($location) {
    $parents = [];
    if ($location->hasField('field_admin_level')) {
      if ($location->field_admin_level->value === 0) {
        $parents = [
          $location->id() . ':' . $location->label(),
        ];
      }
    }

    if (isset($location->field_parent)) {
      foreach ($location->field_parent->referencedEntities() as $parent) {
        $parents = array_merge($parents, $this->getParentValues($parent));
      }
    }

    return $parents;
  }

}
