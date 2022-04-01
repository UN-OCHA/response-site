<?php

namespace Drupal\ocha_docstore_files\Plugin\ExternalEntities\StorageClient;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\external_entities\ExternalEntityInterface;
use Drupal\external_entities\Plugin\ExternalEntities\StorageClient\Rest;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * External entities storage client based on a REST API.
 *
 * @ExternalEntityStorageClient(
 *   id = "restjson",
 *   label = @Translation("REST (JSON)"),
 *   description = @Translation("Retrieves external entities from a strict JSON REST API.")
 * )
 */
class RestJson extends Rest implements PluginFormInterface {

  /**
   * Resource cache.
   *
   * @var array
   */
  protected static $resourceCache = [];

  /**
   * Resource count cache.
   *
   * @var array
   */
  protected static $resourceCountCache = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('string_translation'),
      $container->get('external_entities.response_decoder_factory'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'endpoint' => NULL,
      'response_format' => 'json',
      'pager' => [
        'default_limit' => 50,
        'page_parameter' => NULL,
        'page_parameter_type' => NULL,
        'page_size_parameter' => NULL,
        'page_size_parameter_type' => NULL,
      ],
      'api_key' => [
        'header_name' => NULL,
        'key' => NULL,
      ],
      'parameters' => [
        'list' => NULL,
        'single' => NULL,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    if (empty($ids) || !is_array($ids)) {
      return [];
    }

    $entities = [];

    // No need to retrieve data for resources that have already retrieved.
    foreach ($ids as $index => $id) {
      if (static::isCachedResource($id)) {
        $entities[$id] = static::getCachedResource($id);
        unset($ids[$index]);
      }
    }

    // Call the individual resource endpoint if there is a single ID to load.
    if (count($ids) === 1) {
      // Return result as array.
      $id = reset($ids);
      $entities[$id] = $this->load($id);
      return $entities;
    }
    // Otherwise perform batch requests.
    //
    // @todo we could perform parallel requests (ex: 5 or so) via a pool
    // instead of sequential ones to improve performances.
    //
    // @see https://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests
    elseif (!empty($ids)) {
      // Limit to 50 to avoid issues with the query URL being too long.
      // We could probaly go up to 100 if we could use a simplified syntax for
      // the uuid filter like a comma separated list of uuids.
      $chunk_size = 50;
      foreach (array_chunk($ids, $chunk_size) as $chunk) {
        // The docstore returns the full data for each resource, so it's much
        // faster to perform a single request (or a few if the number of ids
        // is larger that the maximum number of items the docstore can return)
        // rather than the individual `loads`.
        $results = $this->query([
          [
            'field' => 'uuid',
            'value' => $chunk,
            'operator' => 'IN',
          ],
        ], [], 0, count($chunk));
        foreach ($results as $result) {
          if (!empty($result['uuid'])) {
            $entities[$result['uuid']] = $result;
          }
        }
      }
    }

    return $entities;
  }

  /**
   * Loads one entity.
   *
   * @param mixed $id
   *   The ID of the entity to load.
   *
   * @return array|null
   *   A raw data array, NULL if no data returned.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function load($id) {
    if (static::isCachedResource($id)) {
      return static::getCachedResource($id);
    }
    else {
      $parameters = $this->getSingleQueryParameters($id);
      return $this->getFromDocstore($this->getDocstoreEndpoint() . '/' . $id, $parameters);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(ExternalEntityInterface $entity) {
    $result = NULL;

    if ($entity->id()) {
      $this->httpClient->request(
        'PUT',
        $this->getDocstoreEndpoint() . '/' . $entity->id(),
        [
          'body' => json_encode($entity->toRawData()),
          'headers' => $this->getHttpHeaders(),
        ]
      );
      $result = $entity->id();
    }
    else {
      // Remove uuid.
      $raw_data = $entity->toRawData();
      unset($raw_data['uuid']);

      $response = $this->httpClient->request(
        'POST',
        $this->getDocstoreEndpoint(),
        [
          'body' => json_encode($raw_data),
          'headers' => $this->getHttpHeaders(),
        ]
      );

      $body = $response->getBody() . '';
      $body = json_decode($body);

      if ($body->uuid) {
        $result = $body->uuid;
      }
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function delete(ExternalEntityInterface $entity) {
    $this->httpClient->request(
      'DELETE',
      $this->getDocstoreEndpoint() . '/' . $entity->id(),
      [
        'headers' => $this->getHttpHeaders(),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query(array $parameters = [], array $sorts = [], $start = NULL, $length = NULL) {
    $parameters = $this->getListQueryParameters($parameters, $start, $length);
    $results = $this->getFromDocstore($this->getDocstoreEndpoint(), $parameters, $sorts);

    // Return only items for lists.
    if (isset($results['_count']) && isset($results['results'])) {
      $results = $results['results'];
    }
    return $results;
  }

  /**
   * Prepares and returns parameters used for list queries.
   *
   * @param array $parameters
   *   (optional) Raw parameter values.
   * @param int|null $start
   *   (optional) The first item to return.
   * @param int|null $length
   *   (optional) The number of items to return.
   *
   * @return array
   *   An associative array of parameters.
   */
  public function getListQueryParameters(array $parameters = [], $start = NULL, $length = NULL) {
    $query_parameters = [];

    /** @var \Drupal\external_entities\Plugin\ExternalEntities\FieldMapper\SimpleFieldMapper $field_mapper */
    $field_mapper = $this->externalEntityType->getFieldMapper();

    // Currently always providing a limit.
    $query_parameters += $this->getPagingQueryParameters($start, $length);

    foreach ($parameters as $parameter) {
      // Map field names.
      $external_field_name = $field_mapper->getFieldMapping($parameter['field'], 'value');

      if (!$external_field_name) {
        $external_field_name = $field_mapper->getFieldMapping($parameter['field'], 'target_id');
        if (!$external_field_name) {
          $external_field_name = $parameter['field'];
        }
        else {
          // We only need the property name, a bit ugly.
          $external_field_name = reset(explode('/', $external_field_name));
        }
      }

      if (isset($parameter['operator'])) {
        $query_parameters['filter'][$external_field_name]['condition']['operator'] = $parameter['operator'];
        $query_parameters['filter'][$external_field_name]['condition']['path'] = $external_field_name;
        $query_parameters['filter'][$external_field_name]['condition']['value'] = $parameter['value'];
      }
      else {
        $query_parameters['filter'][$external_field_name] = $parameter['value'];
      }
    }

    if (!empty($this->configuration['parameters']['list'])) {
      $query_parameters += $this->configuration['parameters']['list'];
    }

    return $query_parameters;
  }

  /**
   * Gets the HTTP headers to add to a request.
   *
   * @return array
   *   Associative array of headers to add to the request.
   */
  public function getHttpHeaders() {
    $headers = [];

    if ($this->configuration['api_key']['header_name'] && $this->configuration['api_key']['key']) {
      $headers[$this->configuration['api_key']['header_name']] = ocha_docstore_files_get_endpoint_apikey($this->configuration['api_key']['key']);
    }

    return $headers;
  }

  /**
   * {@inheritdoc}
   */
  public function countQuery(array $parameters = []) {
    $endpoint = $this->getDocstoreEndpoint();
    $parameters = $this->getListQueryParameters($parameters);

    // Get the offset and limit parameters.
    $pager = $this->configuration['pager'] ?? [];
    $pager['page_parameter'] = $pager['page_parameter'] ?? 'offset';
    $pager['page_size_parameter'] = $pager['page_size_parameter'] ?? 'limit';

    // We don't need to return documents to get the count.
    $parameters[$pager['page_parameter']] = 0;
    $parameters[$pager['page_size_parameter']] = 0;

    // No need to sort the documents to get the count.
    unset($parameters['sort']);

    $key = $this->generateQueryCountCacheKey($endpoint, $parameters);
    if (isset(static::$resourceCountCache[$key])) {
      $count = static::$resourceCountCache[$key];
    }
    else {
      $results = $this->getFromDocstore($endpoint, $parameters);
      $count = $results['_count'] ?? 0;
      static::$resourceCountCache[$key] = $count;
    }
    return $count;
  }

  /**
   * Get the docstore endpoint for that resource.
   *
   * @return string
   *   Endpoint.
   */
  public function getDocstoreEndpoint() {
    return ocha_docstore_files_get_endpoint_base($this->configuration['endpoint']);
  }

  /**
   * Get data from the docstore.
   *
   * @param string $endpoint
   *   Docstore API endpoint.
   * @param array $parameters
   *   Query parameters.
   * @param bool $cache
   *   Wether to statically cache the query or not.
   *
   * @return array
   *   Query results. When querying multiple items, it will be an associative
   *   array with the `_count` (total number of items), `_start` (starting
   *   offset) and `results` (list of resources). Otherwise if will an array
   *   with the resource's data.
   *
   * @todo catch the Guzzle exceptions and return something more user friendly.
   */
  public function getFromDocstore($endpoint, array $parameters = [], array $sorts = [], $cache = TRUE) {
    $entity_type_id = $this->externalEntityType->id();

    if (!empty($sorts)) {
      /** @var \Drupal\external_entities\Plugin\ExternalEntities\FieldMapper\SimpleFieldMapper $field_mapper */
      $field_mapper = $this->externalEntityType->getFieldMapper();

      $sort_parameters = [];
      foreach ($sorts as $sort) {
        // Map field names.
        $external_field_name = $field_mapper->getFieldMapping($sort['field'], 'value');

        if (!$external_field_name) {
          $external_field_name = $field_mapper->getFieldMapping($sort['field'], 'target_id');
          if (!$external_field_name) {
            $external_field_name = $sort['field'];
          }
          else {
            // We only need the property name, a bit ugly.
            $external_field_name = reset(explode('/', $external_field_name));
          }
        }

        if ($sort['direction'] === 'DESC') {
          $sort_parameters[] = '-' . $external_field_name;
        }
        else {
          $sort_parameters[] = $external_field_name;
        }
      }

      $parameters['sort'] = implode(',', $sort_parameters);
    }

    try {
      $response = $this->httpClient->request(
        'GET',
        $endpoint,
        [
          'headers' => $this->getHttpHeaders(),
          'query' => $parameters,
        ]
      );
    } catch (RequestException $exception) {
      if ($exception->getCode() === 404) {
        throw new NotFoundHttpException();
      }
    }

    $body = $response->getBody() . '';
    $results = $this
      ->getResponseDecoderFactory()
      ->getDecoder($this->configuration['response_format'])
      ->decode($body);

    // Cache the total of items for this resource type.
    if (isset($results['_count'])) {
      $key = $this->generateQueryCountCacheKey($endpoint, $parameters);
      static::$resourceCountCache[$key] = $results['_count'];
    }

    // Cache the resources.
    if (!empty($results['results'])) {
      foreach ($results['results'] as $resource) {
        static::setCachedResource($entity_type_id, $resource['uuid'], $resource);
      }
    }
    elseif (isset($results['uuid'])) {
      static::setCachedResource($entity_type_id, $results['uuid'], $results);
    }

    return $results;
  }

  /**
   * Generate the cache key to store the count for a query.
   *
   * @param string $endpoint
   *   Query endpoint.
   * @param array $parameters
   *   Query parameters.
   *
   * @return string
   *   Count cache key.
   */
  public function generateQueryCountCacheKey($endpoint, array $parameters = []) {
    // Get the offset and limit parameters.
    $pager = $this->configuration['pager'] ?? [];
    $pager['page_parameter'] = $pager['page_parameter'] ?? 'offset';
    $pager['page_size_parameter'] = $pager['page_size_parameter'] ?? 'limit';

    // Remove the sort and range parameters to generate the cache key so that
    // it's the same key for normal queries and count queries.
    unset($parameters[$pager['page_parameter']]);
    unset($parameters[$pager['page_size_parameter']]);
    unset($parameters['sort']);

    return md5($endpoint . serialize($parameters));
  }

  /**
   * Check if a resource is cached.
   *
   * @param string $id
   *   Resource id (uuid).
   *
   * @return bool
   *   TRUE if the resource is cached.
   */
  public static function isCachedResource($id) {
    return isset(static::$resourceCache[$id]);
  }

  /**
   * Get a cached resource.
   *
   * @param string $id
   *   Resource id (uuid).
   *
   * @return array
   *   Resource data.
   */
  public static function getCachedResource($id) {
    return static::$resourceCache[$id] ?? NULL;
  }

  /**
   * Cache a resource.
   *
   * @param string $entity_type_id
   *   External entity type id using the resource type.
   * @param string $id
   *   Resource id (uuid).
   * @param array $data
   *   Resource data.
   * @param bool $cache_references
   *   If TRUE cache the other resources referenced by the resource.
   */
  public static function setCachedResource($entity_type_id, $id, array $data, $cache_references = FALSE) {
    // Cache the resource.
    static::$resourceCache[$id] = $data;

    // Get the list of fields referencing docstore resources.
    $reference_fields = static::getExternalEntityReferenceFields($entity_type_id);

    // Cache the referenced resources.
    foreach ($data as $field => $field_data) {
      if (!isset($reference_fields[$field])) {
        continue;
      }
      if (empty($reference_fields[$field]['cacheable']) && !$cache_references) {
        continue;
      }

      $references = NULL;

      // If the field has a uuid property, then assume it's a reference to
      // another resource. The docstore returns the entire data of children
      // resources, so we can chache them as well.
      if (is_array($field_data)) {
        // Single value.
        if (isset($field_data['uuid'])) {
          $references = [$field_data];
        }
        // Multiple values.
        elseif (!static::arrayIsAssociative($field_data)) {
          $references = $field_data;
        }
      }

      // Add the referenced data to the cache.
      if (!empty($references)) {
        foreach ($references as $item) {
          if (isset($item['uuid']) && count(array_keys($item)) > 1) {
            // Cater for the taxonomy term using 'name' or 'display_name'
            // instead of 'label' when retrieved as referenced items.
            if (!isset($item['label'])) {
              if (isset($item['display_name'])) {
                $item['label'] = $item['display_name'];
              }
              elseif (isset($item['name'])) {
                $item['label'] = $item['name'];
              }
            }
            static::$resourceCache[$item['uuid']] = $item;
          }
        }
      }
    }
  }

  /**
   * Reset the resource cache.
   */
  public static function resetResourceCache() {
    static::$resourceCache = [];
  }

  /**
   * Reset the resource count cache.
   */
  public static function resetResourceCountCache() {
    static::$resourceCountCache = [];
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

  /**
   * Get the list of fields that reference cacheable external entities.
   *
   * "Cacheable" means that the external entity doesn't have other fields than
   * the base fields (id, uuid, title) or is a document resource without nested
   * resources.
   *
   * @param string $entity_type_id
   *   External entity type id of the resource.
   *
   * @return array
   *   List of fields that reference external entities that have enough data
   *   returned as part of the referencing resource to be cached instead of
   *   doing an extra request to the docstore. The array is keyed with the field
   *   names and each item has an entity_type_id property and a cacheable
   *   property which is FALSE when we cannot use the data from the referencing
   *   entity to fill the cache.
   */
  public static function getExternalEntityReferenceFields($entity_type_id) {
    static $cache = [];

    if (!isset($cache[$entity_type_id])) {
      $field_definitions = \Drupal::service('entity_field.manager')
        ->getFieldDefinitions($entity_type_id, $entity_type_id);

      // Retrieve the storage client of the external entity.
      $storage_client = \Drupal::service('entity_type.manager')
        ->getStorage($entity_type_id)
        ->getStorageClient();

      /** @var \Drupal\external_entities\Plugin\ExternalEntities\FieldMapper\SimpleFieldMapper $field_mapper */
      $field_mapper = $storage_client->externalEntityType->getFieldMapper();
      $field_mapping = $field_mapper->getFieldMappings();

      $fields = [];
      foreach ($field_definitions as $field => $field_definition) {
        // Skip if the field is not an entity reference field or if there
        // is no mapping for the field.
        if ($field_definition->getType() !== 'entity_reference') {
          continue;
        }
        if (empty($field_mapping[$field]['target_id'])) {
          continue;
        }

        // Retrieve the type of referenced external entities.
        $target_entity_type_id = $field_definition
          ->getItemDefinition()
          ->getSetting('target_type');

        // Skip if the referenced entity type doesn't correspond to a
        // docstore resource.
        if (!static::isDocstoreExernalEntityType($target_entity_type_id)) {
          continue;
        }

        // Skip assessment documents.
        if ($target_entity_type_id === 'assessment_document') {
          continue;
        }

        // Extract the name of the docstore resource field from the
        // property mapped to the `target_id` property of the entity
        // reference field.
        $field_name = strtok($field_mapping[$field]['target_id'], '/');

        $fields[$field_name] = [
          'entity_type_id' => $target_entity_type_id,
          'cacheable' => static::isExternalEntityCacheable($target_entity_type_id),
        ];
      }
      $cache[$entity_type_id] = $fields;
    }

    return $cache[$entity_type_id];
  }

  /**
   * Check if an entity type is an external entity type.
   *
   * @param string $entity_type_id
   *   Entity type id.
   *
   * @return bool
   *   TRUE if the entity type is an external entity type.
   */
  public static function isDocstoreExernalEntityType($entity_type_id) {
    static $cache = [];

    if (!isset($cache[$entity_type_id])) {
      $storage = \Drupal::service('entity_type.manager')
        ->getStorage($entity_type_id);

      $class = $storage->getEntityType()->getClass();
      if ($class === 'Drupal\external_entities\Entity\ExternalEntity') {
        $storage_client = $storage->getStorageClient();
        $cache[$entity_type_id] = is_a($storage_client, static::class);
      }
      else {
        $cache[$entity_type_id] = FALSE;
      }
    }

    return $cache[$entity_type_id];
  }

  /**
   * Check if an external entity type is cacheable.
   *
   * "Cacheable" means that the external entity doesn't have other fields than
   * the base fields (id, uuid, title) or is a document resource without nested
   * resources.
   *
   * @param string $entity_type_id
   *   External entity type id.
   *
   * @return bool
   *   TRUE if we can use the data from the referencing resource instead
   *   of calling the docstore to get the data.
   */
  public static function isExternalEntityCacheable($entity_type_id) {
    if (!static::isDocstoreExernalEntityType($entity_type_id)) {
      return FALSE;
    }

    $entity_field_manager = \Drupal::service('entity_field.manager');

    // Get the field definitions for the external entity type.
    $field_definitions = $entity_field_manager
      ->getFieldDefinitions($entity_type_id, $entity_type_id);

    // Retrieve the storage client of the external entity.
    $storage_client = \Drupal::service('entity_type.manager')
      ->getStorage($entity_type_id)
      ->getStorageClient();

    // The docstore returns one level of nested documents. So if the nested
    // document doesn't have reference fields, then we can use the data
    // returned as part of the referencing resource directly without any extra
    // query.
    if (strpos($storage_client->getDocstoreEndpoint(), 'api/v1/documents/') !== FALSE) {
      foreach ($field_definitions as $field_name => $field_definition) {
        if ($field_definition->getType() === 'entity_reference') {
          return FALSE;
        }
      }
    }
    // For terms, the docstore only return the uuid and label, so we check if
    // the corresponding external entity has other fields. If not we can use the
    // term data returned as part of the referencing resource directly without
    // any extra query.
    else {
      $base_field_definitions = $entity_field_manager
        ->getBaseFieldDefinitions($entity_type_id);

      foreach ($field_definitions as $field_name => $field_definition) {
        if (!isset($base_field_definitions[$field_name])) {
          return FALSE;
        }
      }
    }

    return TRUE;
  }

}
