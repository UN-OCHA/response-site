<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Url;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page controller for tabs.
 */
class HdxController extends ControllerBase {

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
   * Build active facets for Hdx.
   */
  public function buildHdxActiveFacets(string $base_url, array $filters, array $all_facets) : array {
    $active_facets = [];

    foreach ($filters as $key => $keywords) {
      if (is_string($keywords)) {
        $name = $filters[$key];
        if (isset($all_facets[$key])) {
          foreach ($all_facets[$key]['items'] as $item) {
            if ($item['name'] == $name) {
              $name = $item['display_name'];
              break;
            }
          }
        }

        $title = $this->t('Remove @name', ['@name' => $name]);
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
          $name = $keyword;
          if (isset($all_facets[$key])) {
            foreach ($all_facets[$key]['items'] as $item) {
              if ($item['name'] == $keyword) {
                $name = $item['display_name'];
                break;
              }
            }
          }

          $title = $this->t('Remove @name', ['@name' => $name]);
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
   * Build facets for Hdx.
   */
  public function buildHdxFacets(string $base_url, array $embedded_facets, array $filters, array $query_filters) : array {
    $facet_blocks = [];

    $allowed_filters = $this->getHdxFilters();
    foreach (array_keys($allowed_filters) as $key) {
      $facets[$key] = $embedded_facets[$key];
    }

    foreach ($facets as $name => $facet) {
      $links = [];

      // Sort facets.
      uasort($facet['items'], function ($a, $b) {
        return strcmp($a['display_name'], $b['display_name']);
      });

      if (isset($facet['items']) && count($facet['items']) > 1) {
        foreach ($facet['items'] as $term) {
          $filter = [
            $name => $term['name'],
          ];

          // Check if facet is already active.
          if (isset($filters[$name])) {
            if (is_string($filters[$name]) && $filters[$name] == $filter[$name]) {
              continue;
            }
            if (is_array($filters[$name]) && in_array($filter[$name], $filters[$name])) {
              continue;
            }
          }

          // Remove facets part of the original url.
          if (isset($query_filters[$name])) {
            if (is_string($query_filters[$name]) && $query_filters[$name] == $filter[$name]) {
              continue;
            }
            if (is_array($query_filters[$name]) && in_array($filter[$name], $query_filters[$name])) {
              continue;
            }
          }

          $links[] = [
            'title' => $term['display_name'] . ' (' . $term['count'] . ')',
            'url' => Url::fromUserInput($base_url, [
              'query' => [
                'filters' => array_merge_recursive($filters, $filter),
              ],
            ]),
          ];
        }

        if (count($links) > 1) {
          $facet_blocks[$name] = [
            'title' => $this->getHdxFilters($name),
            'links' => $links,
          ];
        }
      }
    }

    return $facet_blocks;
  }

  /**
   * Build Hdx parameters.
   */
  public function buildHdxParameters(int $offset, int $limit, array $query_filters) : array {
    $parameters = [
      'q' => '',
      'fq' => '+dataset_type:dataset -extras_archived:true',
      'fq_list' => [],
      'facet.field' => [
        'groups',
        'res_format',
        'organization',
        'vocab_Topics',
        'license_id',
      ],
      'facet.limit' => 250,
      'sort' => 'score desc, if(gt(last_modified,review_date),last_modified,review_date) desc',
      'start' => $offset,
      'rows' => $limit,
    ];

    // Pasted filters from URL are using OR.
    foreach ($query_filters as $key => $values) {
      switch ($key) {
        case 'q':
        case 'sort':
          $parameters[$key] = $values;
          break;

        case 'groups':
        case 'res_format':
        case 'organization':
        case 'vocab_Topics':
        case 'license_id':
          if (is_array($values)) {
            $parameters['fq_list'][] = $key . ':"' . implode('" OR "', $values) . '"';
          }
          else {
            $parameters['fq_list'][] = $key . ':"' . $values . '"';
          }
          break;

      }
    }

    return $parameters;
  }

  /**
   * Allowed filters.
   */
  public function getHdxFilters($key = NULL) {
    $filters = [
      'groups' => $this->t('Groups'),
      'res_format' => $this->t('Formats'),
      'organization' => $this->t('Organizations'),
      'vocab_Topics' => $this->t('Tags'),
      'license_id' => $this->t('Licenses'),
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
   * Execute Hdx query.
   */
  public function executeHdxQuery(array $parameters) : array {
    $endpoint = 'https://data.humdata.org/api/3/action/package_search';

    try {
      $response = $this->httpClient->request(
        'POST',
        $endpoint,
        [
          RequestOptions::JSON => $parameters,
        ]
      );
    }
    catch (RequestException $exception) {
      if ($exception->getCode() === 404) {
        throw new NotFoundHttpException();
      }
    }

    $body = $response->getBody() . '';
    $results = json_decode($body, TRUE);

    return $results['result'];
  }

  /**
   * Build Hdx objects.
   */
  public function buildHdxObjects(array $results) : array {
    $data = [];

    foreach ($results['results'] as $row) {
      $id = $row['id'];
      $data[$id] = [
        'id' => $id,
        'url' => 'https://data.humdata.org/dataset/' . $row['name'],
        'title' => $row['title'],
        'body' => check_markup($row['notes'] ?? '', 'markdown'),
        'date_changed' => $row['review_date'] ?? $row['metadata_modified'],
        'organization' => $row['organization']['name'],
        'organization_img' => $row['organization']['image_url'],
        'primary_country' => $row['groups'][0]['title'],
        'countries' => $row['groups'],
        'format' => 'Dataset',
      ];

      if (isset($row['groups'])) {
        $groups = [];
        foreach ($row['groups'] as $group) {
          $groups[] = $group['display_name'];
        }
        $data[$id]['groups'] = implode(', ', $groups);
      }

      if (isset($row['resources'])) {
        $files = [];
        foreach ($row['resources'] as $file) {
          $files[] = [
            'url' => $file['download_url'],
            'filename' => $file['name'] ?? '',
            'description' => $file['description'] ?? '',
            'format' => $file['description'] ?? '',
          ];
        }
        $data[$id]['files'] = $files;
      }
    }

    return $data;
  }

  /**
   * Parse Hdx URL.
   */
  public function parseHdxUrl(string $url) : array {
    $path = parse_url($url, PHP_URL_PATH);
    $query = parse_url($url, PHP_URL_QUERY);

    if ($path === '/dataset') {
      return hr_paragraphs_parse_str($query);
    }

    if (strpos($path, '/group/') === 0) {
      $parts = explode('/', $path);
      return [
        'groups' => array_pop($parts),
      ];
    }

    return [];
  }

}
