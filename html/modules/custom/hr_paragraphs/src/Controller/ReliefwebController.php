<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Url;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page controller for tabs.
 */
class ReliefwebController extends ControllerBase {

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
   * Advanced search operator mapping.
   *
   * @var array
   */
  protected $advancedSearchOperators = [
    'with' => '(',
    'without' => '!(',
    'and-with' => ')_(',
    'and-without' => ')_!(',
    'or-with' => ').(',
    'or-without' => ').!(',
    'or' => '.',
    'and' => '_',
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entity_type_manager, ClientInterface $http_client) {
    $this->entityTypeManager = $entity_type_manager;
    $this->httpClient = $http_client;
  }

  /**
   * Build active facets for Reliefweb.
   */
  public function buildReliefwebActiveFacets(string $base_url, array $filters) : array {
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
   * Build facets for Reliefweb.
   */
  public function buildReliefwebFacets(string $base_url, array $embedded_facets, array $filters) : array {
    $facet_blocks = [];

    $allowed_filters = $this->getReliefwebFilters();
    foreach (array_keys($allowed_filters) as $key) {
      $facets[$key] = $embedded_facets['facets'][$key];
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
            'title' => $this->getReliefwebFilters($name),
            'links' => $links,
          ];
        }
      }
    }

    return $facet_blocks;
  }

  /**
   * Build reliefweb parameters.
   */
  public function buildReliefwebParameters(int $offset, int $limit, array $query_filters, string $iso3 = '') : array {
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
      'appname' => 'hrinfo',
      'offset' => $offset,
      'limit' => $limit,
      'preset' => 'latest',
      'fields[include]' => [
        'id',
        'disaster_type.name',
        'url',
        'title',
        'date.changed',
        'source.shortname',
        'country.name',
        'primary_country.name',
        'file.url',
        'file.preview.url-thumb',
        'file.description',
        'file.filename',
        'format.name',
      ],
      'filter' => [
        'operator' => 'AND',
        'conditions' => [],
      ],
      'facets' => [],
    ];

    if (!empty($iso3)) {
      $parameters['filter']['conditions'][] = [
        'field' => 'primary_country.iso3',
        'value' => strtolower($iso3),
        'operator' => 'OR',
      ];
    }

    foreach ($facet_filters as $facet_filter) {
      $parameters['filter']['conditions'][] = $facet_filter;
    }

    $allowed_filters = $this->getReliefwebFilters();
    foreach (array_keys($allowed_filters) as $key) {
      // Date is a special case, needs an interval.
      if (strpos($key, 'date') !== FALSE) {
        $parameters['facets'][] = [
          'field' => $key,
          'interval' => 'month',
        ];
      }
      else {
        $parameters['facets'][] = [
          'field' => $key,
          'limit' => 2000,
          'sort' => 'value:asc',
        ];
      }
    }

    return $parameters;
  }

  /**
   * Allowed filters.
   */
  public function getReliefwebFilters($key = NULL) {
    $filters = [
      'source.name' => $this->t('Organization'),
      'theme.name' => $this->t('Theme'),
      'format.name' => $this->t('Format'),
      'disaster_type' => $this->t('Disaster type'),
      'language.name' => $this->t('Language'),
      'date.original' => $this->t('Original date'),
      'date.changed' => $this->t('Posting date'),
      'disaster.name' => $this->t('Disaster'),
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
   * Execute reliefweb query.
   */
  public function executeReliefwebQuery(array $parameters) : array {
    $endpoint = 'https://api.reliefweb.int/v1/reports';

    try {
      $response = $this->httpClient->request(
        'GET',
        $endpoint,
        [
          'query' => $parameters,
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

    return $results;
  }

  /**
   * Build reliefweb objects.
   */
  public function buildReliefwebObjects(array $results) : array {
    $data = [];

    foreach ($results['data'] as $row) {
      $url = $row['fields']['url'];
      $title = $row['fields']['title'] ?? $row['fields']['name'];
      $data[$title] = [
        'id' => $row['fields']['id'],
        'title' => $title,
        'url' => $url,
        'date_changed' => $row['fields']['date']['changed'],
        'format' => $row['fields']['format'][0]['name'],
        'primary_country' => $row['fields']['primary_country']['name'],
      ];

      if (isset($row['fields']['source'])) {
        $sources = [];
        foreach ($row['fields']['source'] as $source) {
          $sources[] = $source['shortname'];
        }
        $data[$title]['sources'] = implode(', ', $sources);
      }

      if (isset($row['fields']['disaster_type'])) {
        $disaster_types = [];
        foreach ($row['fields']['disaster_type'] as $disaster_type) {
          $disaster_types[] = $disaster_type['name'];
        }
        $data[$title]['disaster_types'] = $disaster_types;
      }

      if (isset($row['fields']['country'])) {
        $countries = [];
        foreach ($row['fields']['country'] as $country) {
          $countries[] = $country['name'];
        }
        $data[$title]['countries'] = $countries;
      }

      if (isset($row['fields']['file'])) {
        $files = [];
        foreach ($row['fields']['file'] as $file) {
          $files[] = [
            'preview' => isset($file['preview']['url-thumb']) ? $this->reliefwebFixUrl($file['preview']['url-thumb']) : '',
            'url' => $this->reliefwebFixUrl($file['url']),
            'filename' => $file['filename'] ?? '',
            'description' => $file['description'] ?? '',
          ];
        }
        $data[$title]['files'] = $files;
      }
    }

    return $data;
  }

  /**
   * Fix URL for reliefweb.
   */
  protected function reliefwebFixUrl($url) {
    $url = str_replace('#', '%23', $url);
    $url = str_replace(' ', '%20', $url);
    $url = str_replace('http://', 'https://', $url);

    return $url;
  }

  /**
   * Determine query type.
   */
  public function reliefwebQueryType(string $url) : string {
    $parsed = UrlHelper::parse($url);
    $params = UrlHelper::filterQueryParameters($parsed['query']);

    if (isset($params['advanced-search'])) {
      return 'advanced-search';
    }

    if (isset($params['search'])) {
      return 'search';
    }

    return 'unsupported';
  }

  /**
   * Parse reliefweb URL.
   */
  public function parseReliefwebUrl(string $url) : array {
    $conditions = [];
    $exclude = [
      'q',
      'page',
    ];

    $parsed = UrlHelper::parse($url);
    $params = UrlHelper::filterQueryParameters($parsed['query'], $exclude);

    if (isset($params['advanced-search'])) {
      $parameter = $params['advanced-search'];
      // Validate.
      $pattern = '/^(((^|[._])!?)\(([A-Z]+(-?\d+|\d+-\d*|[0-9a-z-]+)([._](?!\)))?)+\))+$/';
      if (preg_match($pattern, $parameter) !== 1) {
        return [];
      }

      // Parse parameter.
      $matches = [];
      $pattern = '/(!?\(|\)[._]!?\(|[._])([A-Z]+)(\d+-\d*|-?\d+|[0-9a-z-]+)/';
      if (preg_match_all($pattern, $parameter, $matches, PREG_SET_ORDER) === FALSE) {
        return [];
      }

      $operators = array_flip($this->advancedSearchOperators);
      $filters = $this->getFilters();

      foreach ($matches as $match) {
        $operator = $operators[$match[1]];
        $code = $match[2];
        $value = $match[3];

        if (isset($filters[$code])) {
          $filter = $filters[$code];
          $type = $filter['type'];

          $condition = [
            'code' => $code,
            'type' => $type,
            'field' => $filter['field'],
            'value' => $value,
            'operator' => $operator,
          ];

          switch ($type) {
            case 'reference':
              // For references, we'll validate later after accumulating them.
              $values[$type][$code][$value] = $value;
              break;

            case 'fixed':
              $values[$type][$code][$value] = $filter['values'][$value] ?? NULL;
              break;

            case 'date':
              $dates = hr_paragraphs_validate_date_filter_values($code, [$value]);
              $values[$type][$code][$value] = $dates;
              // We store the dates for convenience when working with the filter
              // values in other functions like advancedSearchToApiFilter().
              $condition['processed'] = $dates;
              break;
          }

          $conditions[] = $condition;
        }
      }

      return $conditions;
    }

    if (isset($params['search'])) {
      $parameter = $params['search'];
      return [$parameter];
    }

    return $conditions;
  }

  /**
   * Validate date filter values.
   *
   * @param string $code
   *   Filter code.
   * @param array $values
   *   Filter values.
   *
   * @return array
   *   Dates with a 'from' or a 'to' key or both.
   */
  public function validateDateFilterValues($code, array $values) {
    if (empty($values)) {
      return [];
    }
    // We only accept one range.
    $values = $values[0];

    $values = array_map(function ($value) {
      if (strlen($value) !== 8 || !ctype_digit($value)) {
        return NULL;
      }
      $date = date_create_immutable_from_format('Ymd|', $value, timezone_open('UTC'));
      return $date;
    }, explode('-', $values, 2));

    $dates = [];
    if (count($values) === 1) {
      if (!empty($values[0])) {
        $dates['from'] = $values[0];
        $dates['to'] = $values[0];
      }
    }
    else {
      // If the to date is before the from date, we inverse the dates.
      if (!empty($values[0]) && !empty($values[1]) && $values[1] < $values[0]) {
        $temp = $values[0];
        $values[0] = $values[1];
        $values[1] = $temp;
      }

      if (!empty($values[0])) {
        $dates['from'] = $values[0];
      }
      if (!empty($values[1])) {
        $dates['to'] = $values[1];
      }
    }

    return $dates;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      'PC' => [
        'name' => $this->t('Primary country'),
        'type' => 'reference',
        'vocabulary' => 'country',
        'field' => 'primary_country.id',
        'widget' => [
          'type' => 'autocomplete',
          'label' => $this->t('Search for a primary country'),
          'resource' => 'countries',
        ],
        'operator' => 'AND',
      ],
      'C' => [
        'name' => $this->t('Country'),
        'type' => 'reference',
        'vocabulary' => 'country',
        'field' => 'country.id',
        'widget' => [
          'type' => 'autocomplete',
          'label' => $this->t('Search for a country'),
          'resource' => 'countries',
        ],
        'operator' => 'AND',
      ],
      'S' => [
        'name' => $this->t('Organization'),
        'type' => 'reference',
        'vocabulary' => 'source',
        'field' => 'source.id',
        'widget' => [
          'type' => 'autocomplete',
          'label' => $this->t('Search for an organization'),
          'resource' => 'sources',
          'parameters' => [
            'filter' => [
              'field' => 'content_type',
              'value' => 'report',
            ],
          ],
        ],
        'operator' => 'AND',
      ],
      'OT' => [
        'name' => $this->t('Organization type'),
        'type' => 'reference',
        'vocabulary' => 'organization_type',
        'field' => 'source.type.id',
        'widget' => [
          'type' => 'options',
          'label' => $this->t('Select an organization type'),
        ],
        'operator' => 'OR',
      ],
      'D' => [
        'name' => $this->t('Disaster'),
        'type' => 'reference',
        'vocabulary' => 'disaster',
        'field' => 'disaster.id',
        'widget' => [
          'type' => 'autocomplete',
          'label' => $this->t('Search for a disaster'),
          'resource' => 'disasters',
          'parameters' => [
            'sort' => 'date:desc',
          ],
        ],
        'operator' => 'OR',
      ],
      'DT' => [
        'name' => $this->t('Disaster type'),
        'type' => 'reference',
        'vocabulary' => 'disaster_type',
        'exclude' => [
          // Complex Emergency.
          41764,
        ],
        'field' => 'disaster_type.id',
        'widget' => [
          'type' => 'options',
          'label' => $this->t('Select a disaster type'),
        ],
        'operator' => 'AND',
      ],
      'T' => [
        'name' => $this->t('Theme'),
        'type' => 'reference',
        'vocabulary' => 'theme',
        'field' => 'theme.id',
        'widget' => [
          'type' => 'options',
          'label' => $this->t('Select a theme'),
        ],
        'operator' => 'AND',
      ],
      'F' => [
        'name' => $this->t('Content format'),
        'type' => 'reference',
        'vocabulary' => 'content_format',
        'field' => 'format.id',
        'widget' => [
          'type' => 'options',
          'label' => $this->t('Select a content format'),
        ],
        'operator' => 'OR',
      ],
      'L' => [
        'name' => $this->t('Language'),
        'type' => 'reference',
        'vocabulary' => 'language',
        'exclude' => [
          // Other.
          31996,
        ],
        'field' => 'language.id',
        'widget' => [
          'type' => 'options',
          'label' => $this->t('Select a language'),
        ],
        'operator' => 'OR',
      ],
      'DO' => [
        'name' => $this->t('Original publication date'),
        'type' => 'date',
        'field' => 'date.original',
        'widget' => [
          'type' => 'date',
          'label' => $this->t('Select original publication date'),
        ],
      ],
      'DA' => [
        'name' => $this->t('Posting date on ReliefWeb'),
        'type' => 'date',
        'field' => 'date.created',
        'widget' => [
          'type' => 'date',
          'label' => $this->t('Select posting date on ReliefWeb'),
        ],
      ],
    ];
  }

}
