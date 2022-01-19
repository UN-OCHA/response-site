<?php

namespace Drupal\ocha_docstore_files\Plugin\search_api\datasource;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Plugin\search_api\datasource\ContentEntity;
use Drupal\search_api\Utility\Utility;

/**
 * Represents a datasource which exposes the content entities.
 *
 * @SearchApiDatasource(
 *   id = "entity",
 *   deriver = "Drupal\ocha_docstore_files\Plugin\search_api\datasource\DocstoreEntityDeriver"
 * )
 */
class DocstoreEntity extends ContentEntity {

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids) {
    $entity_ids = [];
    foreach ($ids as $item_id) {
      // The entity id is in the form id:langcode.
      $pos = strrpos($item_id, ':');
      // This can only happen if someone passes an invalid ID, since we always
      // include a language code. Still, no harm in guarding against bad input.
      if ($pos === FALSE) {
        continue;
      }
      $entity_ids[$item_id] = substr($item_id, 0, $pos);
    }

    /** @var \Drupal\Core\Entity\ContentEntityInterface[] $entities */
    $entities = $this->getEntityStorage()->loadMultiple($entity_ids);
    $items = [];
    $allowed_bundles = $this->getBundles();
    foreach ($entity_ids as $item_id => $entity_id) {
      if (empty($entities[$entity_id]) || !isset($allowed_bundles[$entities[$entity_id]->bundle()])) {
        continue;
      }
      $items[$item_id] = $entities[$entity_id]->getTypedData();
    }

    return $items;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslationOptions() {
    // External entities don't have a language.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPartialItemIds($page = NULL, array $bundles = NULL, array $languages = NULL) {
    // These would be pretty pointless calls, but for the sake of completeness
    // we should check for them and return early. (Otherwise makes the rest of
    // the code more complicated.)
    if (($bundles === [] && !$languages) || ($languages === [] && !$bundles)) {
      return NULL;
    }

    $select = $this->getEntityTypeManager()
      ->getStorage($this->getEntityTypeId())
      ->getQuery();

    // When tracking items, we never want access checks.
    $select->accessCheck(FALSE);

    // Build up the context for tracking the last ID for this batch page.
    $batch_page_context = [
      'index_id' => $this->getIndex()->id(),
      // The derivative plugin ID includes the entity type ID.
      'datasource_id' => $this->getPluginId(),
      'bundles' => $bundles,
      'languages' => $languages,
    ];
    $context_key = Crypt::hashBase64(serialize($batch_page_context));
    $last_ids = $this->getState()->get(self::TRACKING_PAGE_STATE_KEY, []);

    // We want to determine all entities of either one of the given bundles OR
    // one of the given languages. That means we can't just filter for $bundles
    // if $languages is given. Instead, we have to filter for all bundles we
    // might want to include and later sort out those for which we want only the
    // translations in $languages and those (matching $bundles) where we want
    // all (enabled) translations.
    if ($this->hasBundles()) {
      $bundle_property = $this->getEntityType()->getKey('bundle');
      if ($bundles && !$languages) {
        $select->condition($bundle_property, $bundles, 'IN');
      }
      else {
        $enabled_bundles = array_keys($this->getBundles());
        // Since this is also called for removed bundles/languages,
        // $enabled_bundles might not include $bundles.
        if ($bundles) {
          $enabled_bundles = array_unique(array_merge($bundles, $enabled_bundles));
        }
        if (count($enabled_bundles) < count($this->getEntityBundles())) {
          $select->condition($bundle_property, $enabled_bundles, 'IN');
        }
      }
    }

    if (isset($page)) {
      $page_size = $this->getConfigValue('tracking_page_size');
      assert($page_size, 'Tracking page size is not set.');
      $entity_id = $this->getEntityType()->getKey('id');

      // If known, use a condition on the last tracked ID for paging instead of
      // the offset, for performance reasons on large sites.
      $offset = $page * $page_size;
      if ($page > 0) {
        // We only handle the case of picking up from where the last page left
        // off. (This will cause an infinite loop if anyone ever wants to index
        // Search API tasks in an index, so check for that to be on the safe
        // side.)
        if (isset($last_ids[$context_key])
            && $last_ids[$context_key]['page'] == ($page - 1)
            && $this->getEntityTypeId() !== 'search_api_task') {
          $select->condition($entity_id, $last_ids[$context_key]['last_id'], '>');
          $offset = 0;
        }
      }
      $select->range($offset, $page_size);

      // For paging to reliably work, a sort should be present.
      $select->sort($entity_id);
    }

    $entity_ids = $select->execute();

    if (!$entity_ids) {
      if (isset($page)) {
        // Clean up state tracking of last ID.
        unset($last_ids[$context_key]);
        $this->getState()->set(self::TRACKING_PAGE_STATE_KEY, $last_ids);
      }
      return NULL;
    }

    // Remember the last tracked ID for the next call.
    if (isset($page)) {
      $last_ids[$context_key] = [
        'page' => (int) $page,
        'last_id' => end($entity_ids),
      ];
      $this->getState()->set(self::TRACKING_PAGE_STATE_KEY, $last_ids);
    }

    // External entities don't have a language.
    $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED;
    foreach ($entity_ids as $entity_id) {
      $item_ids[] = "$entity_id:$langcode";
    }

    if (Utility::isRunningInCli()) {
      // When running in the CLI, this might be executed for all entities from
      // within a single process. To avoid running out of memory, reset the
      // static cache after each batch.
      $this->getEntityMemoryCache()->deleteAll();
    }

    return $item_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getAffectedItemsForEntityChange(EntityInterface $entity, array $foreign_entity_relationship_map, EntityInterface $original_entity = NULL): array {
    if (!($entity instanceof ContentEntityInterface)) {
      return [];
    }

    $ids_to_reindex = [];
    $path_separator = IndexInterface::PROPERTY_PATH_SEPARATOR;
    foreach ($foreign_entity_relationship_map as $relation_info) {
      // Ignore relationships belonging to other datasources.
      if (!empty($relation_info['datasource'])
          && $relation_info['datasource'] !== $this->getPluginId()) {
        continue;
      }
      // Check whether entity type and (if specified) bundles match the entity.
      if ($relation_info['entity_type'] !== $entity->getEntityTypeId()) {
        continue;
      }
      if (!empty($relation_info['bundles'])
          && !in_array($entity->bundle(), $relation_info['bundles'])) {
        continue;
      }
      // Maybe this entity belongs to a bundle that does not have this field
      // attached. Hence we have this check to ensure the field is present on
      // this particular entity.
      if (!$entity->hasField($relation_info['field_name'])) {
        continue;
      }

      $items = $entity->get($relation_info['field_name']);

      // We trigger re-indexing if either it is a removed entity or the
      // entity has changed its field value (in case it's an update).
      if (!$original_entity || !$items->equals($original_entity->get($relation_info['field_name']))) {
        $query = $this->entityTypeManager->getStorage($this->getEntityTypeId())
          ->getQuery();
        $query->accessCheck(FALSE);

        // Luckily, to translate from property path to the entity query
        // condition syntax, all we have to do is replace the property path
        // separator with the entity query path separator (a dot) and that's it.
        $property_path = $relation_info['property_path_to_foreign_entity'];
        $property_path = str_replace($path_separator, '.', $property_path);
        $query->condition($property_path, $entity->id());

        try {
          $entity_ids = array_values($query->execute());
        }
        // @todo Switch back to \Exception once Core bug #2893747 is fixed.
        catch (\Throwable $e) {
          // We don't want to catch all PHP \Error objects thrown, but just the
          // ones caused by #2893747.
          if (!($e instanceof \Exception)
              && (get_class($e) !== \Error::class || $e->getMessage() !== 'Call to a member function getColumns() on bool')) {
            throw $e;
          }
          $vars = [
            '%index' => $this->index->label(),
            '%entity_type' => $entity->getEntityType()->getLabel(),
            '@entity_id' => $entity->id(),
          ];
          try {
            $link = $entity->toLink($this->t('Go to changed %entity_type with ID "@entity_id"', $vars))
              ->toString()->getGeneratedLink();
          }
          catch (\Throwable $e) {
            // Ignore any errors here, it's not that important that the log
            // message contains a link.
            $link = NULL;
          }
          $this->logException($e, '%type while attempting to find indexed entities referencing changed %entity_type with ID "@entity_id" for index %index: @message in %function (line %line of %file).', $vars, RfcLogLevel::ERROR, $link);
          continue;
        }

        // External entities don't have a language.
        $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED;
        foreach ($entity_ids as $entity_id) {
          $ids_to_reindex["$entity_id:$langcode"] = 1;
        }
      }
    }

    return array_keys($ids_to_reindex);
  }

}
