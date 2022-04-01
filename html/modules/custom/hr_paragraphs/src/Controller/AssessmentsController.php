<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\ocha_docstore_files\Plugin\ExternalEntities\StorageClient\RestJson;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Url;

/**
 * An assessments controller.
 */
class AssessmentsController extends ControllerBase {

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
   * Build active facets for Assessments.
   */
  public function buildAssessmentsActiveFacets(string $base_url, array $filters) : array {
    $active_facets = [];

    foreach ($filters as $key => $keywords) {
      if (is_string($keywords)) {
        $title = $this->t('Remove @name', ['@name' => $filters[$key]]);
        $cloned_filters = $filters;
        unset($cloned_filters[$key]);
        $active_facets[] = [
          'title' => $title,
          'url' => Url::fromUserInput($base_url, [
            'query' => [
              'filters' => $cloned_filters,
            ],
          ]),
        ];
      }
      else {
        foreach ($keywords as $index => $keyword) {
          $title = $this->t('Remove @name', ['@name' => $filters[$key][$index]]);
          $cloned_filters = $filters;
          unset($cloned_filters[$key][$index]);
          $active_facets[] = [
            'title' => $title,
            'url' => Url::fromUserInput($base_url, [
              'query' => [
                'filters' => $cloned_filters,
              ],
            ]),
          ];
        }
      }
    }

    return $active_facets;
  }

  /**
   * Build facets for Assessments.
   */
  public function buildAssessmentsFacets(string $base_url, array $embedded_facets, array $filters) : array {
    $facet_blocks = [];
    return $facet_blocks;
    $allowed_filters = $this->getAssessmentsFilters();
    foreach (array_keys($allowed_filters) as $key) {
      $facets[$key] = $embedded_facets[$key] ?? [];
    }

    foreach ($facets as $name => $facet) {
      $links = [];
      if (isset($facet['data']) && count($facet['data']) > 1) {
        foreach ($facet['data'] as $term) {
          // Date is a special case.
          if (strpos($name, 'date') !== FALSE) {
            $filter = [
              $name => date('Y-m-d', strtotime($term['value'])) . ':' . date('Y-m-t', strtotime($term['value'])),
            ];
          }
          else {
            $filter = [
              $name => $term['value'],
            ];
          }

          // Check if facet is already active.
          if (isset($filters[$name])) {
            if (is_string($filters[$name]) && $filters[$name] == $filter[$name]) {
              continue;
            }
            if (is_array($filters[$name]) && in_array($filter[$name], $filters[$name])) {
              continue;
            }
          }

          // Date is a special case.
          if (strpos($name, 'date') !== FALSE) {
            if ($term['count'] > 0) {
              $links[] = [
                'title' => date('F Y', strtotime($term['value'])) . ' (' . $term['count'] . ')',
                'url' => Url::fromUserInput($base_url, [
                  'query' => [
                    'filters' => array_merge_recursive($filters, $filter),
                  ],
                ]),
              ];
            }
          }
          else {
            $links[] = [
              'title' => $term['value'] . ' (' . $term['count'] . ')',
              'url' => Url::fromUserInput($base_url, [
                'query' => [
                  'filters' => array_merge_recursive($filters, $filter),
                ],
              ]),
            ];
          }
        }

        // Reverse order for date filter.
        if (strpos($name, 'date') !== FALSE) {
          $links = array_reverse($links);
        }

        if (count($links) > 1) {
          $facet_blocks[] = [
            'title' => $this->getAssessmentsFilters($name),
            'links' => $links,
          ];
        }
      }
    }

    return $facet_blocks;
  }

  /**
   * Build Assessments parameters.
   */
  public function buildAssessmentsParameters(int $offset, int $limit, array $query_filters) : array {
    $facet_filters = [];

    foreach ($query_filters as $key => $keywords) {
      // Date is a special case.
      if (strpos($key, 'date') !== FALSE) {
        $from_to = explode(':', $keywords);
        $facet_filters[] = [
          'field' => $key,
          'value' => [
            'from' => $from_to[0] . 'T00:00:00+00:00',
            'to' => $from_to[1] . 'T23:59:59+00:00',
          ],
          'operator' => 'AND',
        ];
      }
      else {
        $facet_filters[] = [
          'field' => $key,
          'value' => $keywords,
          'operator' => 'OR',
        ];
      }
    }

    $parameters = [
      'filter' => [],
    ];

    foreach ($facet_filters as $facet_filter) {
      $parameters['filter']['facet_' . $facet_filter['field']] = [
        'path' => $facet_filter['field'],
        'value' => $facet_filter['value'],
        'operator' => is_array($facet_filter['value']) ? 'IN' : '=',
      ];

    }

    return $parameters;
  }

  /**
   * Allowed filters.
   */
  public function getAssessmentsFilters($key = NULL) {
    $filters = [
      'organizations' => $this->t('Organization'),
      'locations' => $this->t('Locations'),
    ];

    if ($key) {
      if (array_key_exists($key, $filters)) {
        return $filters[$key];
      }
      else {
        return FALSE;
      }
    }
    else {
      return $filters;
    }
  }

  /**
   * Execute Assessments query.
   */
  public function executeAssessmentsQuery(array $parameters) : array {
    $endpoint = 'http://docstore.local.docksal/api/v1/documents/assessments';
    $endpoint = hr_paragraphs_get_endpoint_base($endpoint);

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

    return $results;
  }

  /**
   * Build Assessments objects.
   */
  public function buildAssessmentsObjects(array $results) : array {
    $data = [];

    foreach ($results as $row) {
      $data[] = [
        'uuid' => $row['uuid'],
        'title' =>$row['title'],
        'url' => 'https://assessments.hpc.tools/assessments' . $row['uuid'],
        'date' => $row['ar_dates'] ?? [],
        'organizations' => $row['organizations'] ?? [],
        'status' => $row['ar_status'] ?? [],
      ];
    }

    return $data;
  }

  /**
   * Parse Assessments URL.
   */
  public function parseAssessmentsUrl(string $url) : array {
    $mapping = [
      'ass_list_locations' => 'locations',
      'ass_list_operations' => 'operations',
    ];

    $url = str_replace('https://assessments.hpc.tools/assessments/', '', $url);
    $parts = explode('/', $url);
    array_shift($parts);

    $conditions = [];
    foreach ($parts as $index => $key) {
      if ($index % 2 == 0) {
        if (isset($mapping[$key])) {
          $key = $mapping[$key];
        }
        if (!isset($conditions[$key])) {
          $conditions[$key] = [];
        }
        $conditions[$key][] = $parts[$index+1];
      }
    }

    return $conditions;
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

}
