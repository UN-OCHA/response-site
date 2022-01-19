<?php

namespace Drupal\ocha_docstore_files\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Query\ResultSetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\ocha_docstore_files\Plugin\ExternalEntities\StorageClient\RestJson;

/**
 * Store an entity and the entities it references.
 *
 * @SearchApiProcessor(
 *   id = "ar_store_entity",
 *   label = @Translation("Store an entity and the entities it references"),
 *   description = @Translation("Store entity."),
 *   stages = {
 *     "add_properties" = 20,
 *     "alter_items" = -10,
 *     "postprocess_query" = -10,
 *   },
 *   locked = true,
 *   hidden = false,
 * )
 */
class StoreEntity extends ProcessorPluginBase {

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
        'label' => $this->t('Stored entity'),
        'description' => $this->t('Stores the entity and the entities it references.'),
        'type' => 'solr_string_storage',
        'processor_id' => $this->getPluginId(),
        'is_list' => FALSE,
      ];
      // Using an underscore at the beginning to avoid clash with custom
      // fields added by the providers.
      $properties['_stored_entity'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {
    // Get the storage field.
    $index_storage_field = $this->index->getField('_stored_entity');

    // Skip if the index doesn't use the stored entity field.
    if (empty($index_storage_field)) {
      return;
    }

    $references = [];
    $items_to_store = [];

    // Extract the referenced entites for each item to index.
    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $item->getOriginalObject()->getEntity();
      $entity_type_id = $entity->getEntityTypeId();

      // Get the raw external entity data for the entity.
      $extracted = $entity->extractRawData();

      // Get the list of fields that reference external entities.
      $reference_fields = RestJson::getExternalEntityReferenceFields($entity_type_id);

      // Extract the uuids of the referenced entities.
      foreach ($extracted as $field => $field_data) {
        if (empty($field_data) || !isset($reference_fields[$field])) {
          continue;
        }

        $target_entity_type_id = $reference_fields[$field]['entity_type_id'];
        if (isset($field_data['uuid'])) {
          $references[$target_entity_type_id][] = $field_data['uuid'];
        }
        elseif (!static::arrayIsAssociative($field_data)) {
          foreach ($field_data as $delta => $field_item) {
            $references[$target_entity_type_id][] = $field_item['uuid'];
          }
        }
      }

      $items_to_store[] = [
        'entity_type_id' => $entity_type_id,
        'item' => $item,
        'extracted' => $extracted,
      ];
    }

    // Load the referenced entities.
    // @todo do a recursive update?
    $loaded = [];
    foreach ($references as $entity_type_id => $uuids) {
      $entities = $this->entityTypeManager
        ->getStorage($entity_type_id)
        ->loadMultiple($uuids);

      // Extract the raw data of the loaded entities.
      foreach ($entities as $entity) {
        $loaded[$entity->uuid()] = $entity->extractRawData();
      }
    }

    // Update the raw data of the items to store.
    foreach ($items_to_store as $item) {
      $extracted = $item['extracted'];

      // Get the list of fields that reference external entities.
      $reference_fields = RestJson::getExternalEntityReferenceFields($item['entity_type_id']);

      foreach ($extracted as $field => $field_data) {
        if (empty($field_data) || !isset($reference_fields[$field])) {
          continue;
        }

        if (isset($field_data['uuid'])) {
          if (isset($loaded[$field_data['uuid']])) {
            $extracted[$field] += $loaded[$field_data['uuid']];
          }
        }
        elseif (!static::arrayIsAssociative($field_data)) {
          foreach ($field_data as $delta => $field_item) {
            if (isset($loaded[$field_item['uuid']])) {
              $extracted[$field][$delta] += $loaded[$field_item['uuid']];
            }
          }
        }
      }

      // Serialize the data. We also need to encode it to avoid some characters
      // breaking the xml passed to Solr.
      $data = base64_encode(serialize($extracted));

      // Set the raw serialized data as value for the field.
      //
      // Note: we use `setValues()` rather than `addValue()` to reset any
      // previously set data and also to avoid passing the data through the
      // the string data type plugin to prevent any unforseen modifications to
      // the data by other processors that affect the string data type.
      /** @var \Drupal\search_api\Item\FieldInterface $storage_field */
      $storage_field = clone $index_storage_field;
      $storage_field->setValues([$data]);
      $item['item']->setField('_stored_entity', $storage_field);
    }
  }

  /**
   * {@inheritdoc}
   *
   * Extract the stored external entity data and store it in the static cache
   * of the corresponding external entity storage client.
   */
  public function postprocessSearchResults(ResultSetInterface $results) {
    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($results as $item) {
      // Get the field with the stored entity.
      $storage_field = $item->getField('_stored_entity', FALSE);
      if (empty($storage_field)) {
        continue;
      }

      // The field values is an array with the first item being the stored data.
      $values = $storage_field->getValues();
      if (empty($values)) {
        continue;
      }

      $entity_type_id = $item->getDatasource()->getEntityTypeId();

      // Unserialize the stored entity.
      $data = unserialize(base64_decode(reset($values)));

      // Store the data in the storage client static cache so it's used when
      // load or loadMultiple is used to retrieve the external entity data.
      // We force the caching as the stored data contains the entire data of
      // referenced resources.
      RestJson::setCachedResource($entity_type_id, $data['uuid'], $data, TRUE);
    }
  }

  /**
   * Check if an array is associative.
   *
   * @param array $array
   *   Array to check.
   *
   * @return bool
   *   TRUE if the array is an associative array.
   */
  public static function arrayIsAssociative(array $array) {
    foreach ($array as $key => $value) {
      if (is_string($key)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
