<?php

namespace Drupal\hr_paragraphs\Service;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ReliefWeb API client service.
 */
class ReliefWebApiClient {

  /**
   * The HTTP client to fetch the files with.
   */
  protected ClientInterface $httpClient;

  /**
   * Logger factory.
   */
  protected LoggerChannelFactoryInterface $loggerFactory;

  /**
   * Caches the results of API calls.
   */
  protected CacheBackendInterface $cache;

  /**
   * Config factory.
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * API URL.
   */
  protected string $apiUrl;

  /**
   * App name.
   */
  protected string $appName;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $http_client, LoggerChannelFactoryInterface $logger_factory, CacheBackendInterface $cache, ConfigFactoryInterface $config_factory) {
    $this->httpClient = $http_client;
    $this->loggerFactory = $logger_factory;
    $this->cache = $cache;
    $this->configFactory = $config_factory;

    $this->appName = $this->config('hr_paragraphs.settings')->get('reliefweb_api_appname') ?: 'response';

    // Get base url.
    $endpoint = $this->config('hr_paragraphs.settings')->get('reliefweb_api_endpoint') ?: 'https://api.reliefweb.int/v2/reports';
    $this->apiUrl = parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST);
  }

  /**
   * Gets the logger for a specific channel.
   *
   * @param string $channel
   *   The name of the channel. Can be any string, but the general practice is
   *   to use the name of the subsystem calling this.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger for the given channel.
   */
  protected function getLogger($channel) {
    return $this->loggerFactory->get($channel);
  }

  /**
   * Retrieves a configuration object.
   *
   * @param string $name
   *   The name of the configuration object to retrieve. The name corresponds to
   *   a configuration file. For \Drupal::config('my_module.admin'), the config
   *   object returned will contain the contents of my_module.admin
   *   configuration file.
   *
   * @return \Drupal\Core\Config\Config
   *   A configuration object.
   */
  protected function config($name) {
    return $this->configFactory->get($name);
  }

  /**
   * Execute reliefweb query.
   *
   * @param string $endpoint
   *   API endpoint.
   * @param array<string, mixed> $parameters
   *   Search parameters.
   *
   * @return array<string, mixed>
   *   Raw results.
   */
  public function executeReliefwebQuery(string $endpoint, array $parameters) : array {
    // Remove empty filters.
    if (!isset($parameters['filter']['conditions']) || empty(($parameters['filter']['conditions']))) {
      unset($parameters['filter']);
    }

    try {
      $this->getLogger('hr_paragraphs_reliefweb')->notice('Fetching data from @url', [
        '@url' => $endpoint,
      ]);

      $response = $this->httpClient->request(
        'GET',
        $endpoint,
        [
          'query' => $parameters,
          'headers' => [
            'accept-encoding' => 'gzip, deflate',
          ],
        ]
      );
    }
    catch (RequestException $exception) {
      $this->getLogger('hr_paragraphs_reliefweb')->error('Fetching data from @url failed with @message', [
        '@url' => $endpoint,
        '@message' => $exception->getMessage(),
      ]);

      if ($exception->getCode() === 404) {
        throw new NotFoundHttpException();
      }
      else {
        throw $exception;
      }
    }

    $body = $response->getBody() . '';
    $results = json_decode($body, TRUE);

    return $results;
  }

  /**
   * Get countries from ReliefWeb.
   */
  public function getCountries(): array {
    // Check cache first.
    $cache = $this->cache->get('reliefweb_countries');
    if ($cache) {
      return $cache->data;
    }

    $parameters = [
      'appname' => $this->appName,
      'limit' => 500,
      'preset' => 'latest',
      'fields[include]' => [
        'name',
        'url',
        'url_alias',
      ],
      'sort' => ['name:asc'],
    ];

    $endpoint = $this->apiUrl . '/v2/countries';
    $json = $this->executeReliefwebQuery($endpoint, $parameters);

    // Cache it forever.
    $this->cache->set('reliefweb_countries', $json, Cache::PERMANENT, ['hr_paragraphs:reliefweb_countries']);

    return $json;
  }

  /**
   * Get country data keyed by id.
   */
  public function getCountriesById(): array {
    $results = $this->getCountries();

    $countries = [];
    if (!empty($results['data'])) {
      foreach ($results['data'] as $country) {
        $countries[$country['id']] = $country;
      }
    }

    return $countries;
  }

  /**
   * Get countries list.
   */
  public function getCountriesList(): array {
    $results = $this->getCountries();

    $countries = [];
    if (!empty($results['data'])) {
      foreach ($results['data'] as $country) {
        $countries[$country['id']] = $country['fields']['name'];
      }
    }

    asort($countries, SORT_STRING | SORT_FLAG_CASE);
    return $countries;
  }

  /**
   * Get sources from ReliefWeb.
   */
  public function getSources(): array {
    // Check cache first.
    $cache = $this->cache->get('reliefweb_sources');
    if ($cache) {
      return $cache->data;
    }

    $offset = 0;

    $parameters = [
      'appname' => $this->appName,
      'offset' => $offset,
      'limit' => 999,
      'preset' => 'latest',
      'fields[include]' => [
        'name',
        'shortname',
        'url',
        'url_alias',
      ],
      'sort' => ['name:asc'],
    ];

    $endpoint = $this->apiUrl . '/v2/sources';

    $json = NULL;
    do {
      $parameters['offset'] = $offset;
      $json_part = $this->executeReliefwebQuery($endpoint, $parameters);

      if (!isset($json)) {
        $json = $json_part;
      }
      else {
        $json['data'] = array_merge($json['data'], $json_part['data']);
      }

      $offset += $parameters['limit'];
    } while (!empty($json_part['links']['next']));

    // Cache it forever.
    $this->cache->set('reliefweb_sources', $json, Cache::PERMANENT, ['hr_paragraphs:reliefweb_organizations']);

    return $json;
  }

  /**
   * Get organization data keyed by id.
   */
  public function getOrganizationsById(): array {
    $results = $this->getSources();

    $sources = [];
    if (!empty($results['data'])) {
      foreach ($results['data'] as $source) {
        $sources[$source['id']] = $source;
      }
    }

    return $sources;
  }

  /**
   * Get organizations list.
   */
  public function getOrganizationsList(): array {
    $results = $this->getSources();

    $organizations = [];
    if (!empty($results['data'])) {
      foreach ($results['data'] as $source) {
        $organizations[$source['id']] = $source['fields']['name'];
        if (!empty($source['fields']['shortname']) && $source['fields']['shortname'] !== $source['fields']['name']) {
          $organizations[$source['id']] .= ' (' . $source['fields']['shortname'] . ')';
        }
      }
    }

    asort($organizations, SORT_STRING | SORT_FLAG_CASE);
    return $organizations;
  }

}
