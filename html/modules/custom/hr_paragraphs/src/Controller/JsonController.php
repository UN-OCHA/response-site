<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\ocha_docstore_files\Plugin\ExternalEntities\StorageClient\RestJson;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * An ocha_map controller.
 */
class JsonController extends ControllerBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The HTTP client to fetch the files with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entity_type_manager, ClientInterface $http_client) {
    $this->entityTypeManager = $entity_type_manager;
    $this->httpClient = $http_client;
  }

  /**
   * Returns a map.
   */
  public function map() {
    global $base_url;
    $src = $base_url . '/rest/assessments/map-data';

    return [
      '#theme' => 'hr_paragraphs_assessments_map',
      '#base_url' => $base_url,
      '#src' => $src,
      '#component_url' => '/modules/custom/hr_paragraphs/component/build/',
    ];
  }

  /**
   * Returns a table.
   */
  public function table() {
    global $base_url;
    $src = $base_url . '/rest/assessments/table-data?sort=-field_date';

    return [
      '#theme' => 'hr_paragraphs_assessments_table',
      '#base_url' => $base_url,
      '#src' => $src,
      '#component_url' => '/modules/custom/hr_paragraphs/component/build/',
    ];
  }

  /**
   * Returns a list.
   */
  public function list() {
    global $base_url;
    $src = $base_url . '/rest/assessments/list-data?sort=-field_date';

    return [
      '#theme' => 'hr_paragraphs_assessments_list',
      '#base_url' => $base_url,
      '#src' => $src,
      '#component_url' => '/modules/custom/hr_paragraphs/component/build/',
    ];
  }

  /**
   * Return map data.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Docstore API request.
   */
  public function assessmentsMapData(Request $request) {
    return $this->fetchDocstoreData('assessment', 'http://docstore.local.docksal/api/v1/documents/assessments', $request, 0, 9999, 'limited');
  }

  /**
   * Return list data.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Docstore API request.
   */
  public function assessmentsListData(Request $request) {
    return $this->fetchDocstoreData('assessment', 'http://docstore.local.docksal/api/v1/documents/assessments', $request);
  }

  /**
   * Return table data.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Docstore API request.
   */
  public function assessmentsTableData(Request $request) {
    return $this->fetchDocstoreData('assessment', 'http://docstore.local.docksal/api/v1/documents/assessments', $request);
  }

  /**
   * Fetch data from docstore.
   */
  protected function fetchDocstoreData($entity_id, $endpoint, $request, $offset = 0, $limit = 50, $set = 'full') {
    $parameters = [];

    // Check for pager.
    if ($request->query->has('page')) {
      $offset = $request->query->get('page');
    }

    $parameters['page'] = [
      'limit' => $limit,
      'offset' => $offset,
    ];

    // Add filters.
    $parameters['filter'] = [];
    $active_filters = [];
    if ($request->query->has('f')) {
      $filters = $request->query->get('f');
      foreach ($filters as $filter) {
        $parts = explode(':', $filter);
        $active_filters[$parts[0]][$parts[1]] = TRUE;
        $parameters['filter'][$parts[0]] = $parts[1];
      }
    }

    $endpoint = hr_paragraphs_get_endpoint_base($endpoint);
    $docstore_data = $this->getFromDocstore($entity_id, $endpoint, $parameters);

    $document_uuids = [];
    foreach ($docstore_data['results'] as $result) {
      $document_uuids[] = $result['uuid'];
    }
    $documents = $this->entityTypeManager->getStorage($entity_id)->loadMultiple($document_uuids);

    // Prepare results.
    $data['search_results'] = [];

    /** @var \Drupal\external_entities\Entity\ExternalEntity $documents[] */
    foreach ($documents as $document) {
      $date = '';
      if (!empty($document->field_date->value)) {
        $date = date('d.m.Y', strtotime($document->field_date->value));
      }

      $record = [
        'uuid' => $document->id(),
        'title' => $document->label(),
        'field_locations_lat_lon' => array_map(function ($item) {
          return $item->entity->field_geolocation->lat . ',' . $item->entity->field_geolocation->lon;
        }, iterator_to_array($document->field_locations->filterEmptyItems())),
      ];

      if ($set === 'full') {
        $record['field_organizations'] = array_map(function ($item) {
          return $item->entity->uuid();
        }, iterator_to_array($document->field_organizations->filterEmptyItems()));
        $record['field_locations_label'] = array_map(function ($item) {
          return $item->entity->label();
        }, iterator_to_array($document->field_locations->filterEmptyItems()));
        $record['field_organizations_label'] = array_map(function ($item) {
          return $item->entity->label();
        }, iterator_to_array($document->field_organizations->filterEmptyItems()));
        $record['field_asst_organizations_label'] = array_map(function ($item) {
          return $item->entity->label();
        }, iterator_to_array($document->field_asst_organizations->filterEmptyItems()));
        $record['field_status'] = $document->field_status->entity ? $document->field_status->entity->label() : '';
        $record['field_ass_date'] = $date;
      }

      $data['search_results'][] = $record;
    }

    // Handle facets.
    $facets = $docstore_data['_facets'];
    foreach ($facets as $key => $facet_info) {
      $field = $facet_info['id'];
      $key = $facet_info['id'];

      $options = [];
      foreach ($facet_info['items'] as $facet_value) {
        $options[] = [
          'key' => $field . ':' . $facet_value['filter'],
          'label' => $facet_value['label'] . ' (' . $facet_value['count'] . ')',
          'active' => isset($active_filters[$field][$facet_value['filter']]),
        ];
      }

      uasort($options, function ($a, $b) {
        return strnatcasecmp($a['label'], $b['label']);
      });

      $data['facets'][$key]['label'] = $facet_info['label'];
      $data['facets'][$key]['name'] = $field;
      $data['facets'][$key]['options'] = array_values($options);
    }

    // Pager.
    $data['pager']['current_page'] = (int) $offset;
    $data['pager']['total_pages'] = ceil($docstore_data['_count'] / $limit);

    // Set the default cache.
    $cache = new CacheableMetadata();
    $cache->addCacheTags([
      'assessment',
      'assessment_document',
    ]);
    $cache->addCacheableDependency($documents);

    // Add the cache contexts for the request parameters.
    $cache->addCacheContexts([
      'url',
      'url.query_args',
    ]);

    $response = new CacheableJsonResponse($data, 200);
    $response->addCacheableDependency($cache);
    return $response;
  }

  public function getFromDocstore($entity_type_id, $endpoint, array $parameters = [], $cache = TRUE) {
    try {
      $response = $this->httpClient->request(
        'GET',
        $endpoint,
        [
          'query' => $parameters,
        ]
      );
    } catch (RequestException $exception) {
      if ($exception->getCode() === 404) {
        throw new NotFoundHttpException();
      }
    }

    $body = $response->getBody() . '';
    $results = json_decode($body, TRUE);

    // Cache the resources.
    if (!empty($results['results'])) {
      foreach ($results['results'] as $resource) {
        RestJson::setCachedResource($entity_type_id, $resource['uuid'], $resource);
      }
    }
    elseif (isset($results['uuid'])) {
      RestJson::setCachedResource($entity_type_id, $results['uuid'], $results);
    }

    return $results;
  }

}
