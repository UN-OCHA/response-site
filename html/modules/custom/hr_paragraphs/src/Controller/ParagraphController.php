<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page controller for tabs.
 */
class ParagraphController extends ControllerBase {

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
   * The pager manager servie.
   *
   * @var \Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pagerManager;

  /**
   * Ical controller.
   *
   * @var \Drupal\hr_paragraphs\Controller\IcalController
   */
  protected $icalController;

  /**
   * Reliefweb controller.
   *
   * @var \Drupal\hr_paragraphs\Controller\ReliefwebController
   */
  protected $reliefwebController;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entity_type_manager, ClientInterface $http_client, PagerManagerInterface $pager_manager, $ical_controller, $reliefweb_controller) {
    $this->entityTypeManager = $entity_type_manager;
    $this->httpClient = $http_client;
    $this->pagerManager = $pager_manager;
    $this->icalController = $ical_controller;
    $this->reliefwebController = $reliefweb_controller;
  }

  /**
   * Helper to check if tab is active.
   */
  protected function tabIsActive($group, $tab) {
    if (is_numeric($group)) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    if (!$group) {
      return AccessResult::forbidden();
    }

    if (!$group->hasField('field_enabled_tabs')) {
      return AccessResult::forbidden();
    }

    $enabled_tabs = $group->field_enabled_tabs->getValue();
    array_walk($enabled_tabs, function (&$item) {
      $item = $item['value'];
    });

    return AccessResult::allowedIf(in_array($tab, $enabled_tabs));
  }

  /**
   * Check if offices is enabled.
   */
  public function hasOffices($group) {
    $active = $this->tabIsActive($group, 'offices');
    if (!$active) {
      return $active;
    }

    if (is_numeric($group)) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    if (!$group) {
      return AccessResult::forbidden();
    }

    if (!$group->hasField('field_offices_page')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIf(!$group->field_offices_page->isEmpty());
  }

  /**
   * Check if pages is enabled.
   */
  public function hasPages($group) {
    $active = $this->tabIsActive($group, 'pages');
    if (!$active) {
      return $active;
    }

    if (is_numeric($group)) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    if (!$group) {
      return AccessResult::forbidden();
    }

    if (!$group->hasField('field_pages_page')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIf(!$group->field_pages_page->isEmpty());
  }

  /**
   * Check if assessments is enabled.
   */
  public function hasAssessments($group) {
    return $this->tabIsActive($group, 'assessments');
  }

  /**
   * Check if datasets is enabled.
   */
  public function hasDatasets($group) {
    return $this->tabIsActive($group, 'datasets');
  }

  /**
   * Check if documents is enabled.
   */
  public function hasDocuments($group) {
    return $this->tabIsActive($group, 'documents');
  }

  /**
   * Check if maps is enabled.
   */
  public function hasInfographics($group) {
    return $this->tabIsActive($group, 'maps');
  }

  /**
   * Check if events is enabled.
   */
  public function hasEvents($group) {
    $active = $this->tabIsActive($group, 'events');
    if (!$active) {
      return $active;
    }

    if (is_numeric($group)) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    if (!$group) {
      return AccessResult::forbidden();
    }

    if (!$group->hasField('field_ical_url')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIf(!$group->field_ical_url->isEmpty());
  }

  /**
   * Get group title.
   */
  public function getGroupTitle($group) {
    if (is_numeric($group)) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    return $group->label->value;
  }

  /**
   * Return all offices of an operation, sector or cluster.
   */
  public function getOffices($group) {
    if ($group->field_offices_page->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No offices link defined.'),
      ];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $group->field_offices_page->first();

    // Redirect external links.
    if ($link->isExternal()) {
      return new TrustedRedirectResponse($link->getUrl()->getUri());
    }

    $entity_type = 'node';
    $view_mode = 'operation_tab';
    $params = $link->getUrl()->getRouteParameters();

    $office_page = $this->entityTypeManager->getStorage($entity_type)->load($params[$entity_type]);
    $view_builder = $this->entityTypeManager->getViewBuilder($entity_type);
    return $view_builder->view($office_page, $view_mode);
  }

  /**
   * Return all pages of an operation, sector or cluster.
   */
  public function getPages($group) {
    if ($group->field_pages_page->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No offices link defined.'),
      ];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $group->field_pages_page->first();

    $entity_type = 'node';
    $view_mode = 'operation_tab';
    $params = $link->getUrl()->getRouteParameters();

    $office_page = $this->entityTypeManager->getStorage($entity_type)->load($params[$entity_type]);
    $view_builder = $this->entityTypeManager->getViewBuilder($entity_type);
    return $view_builder->view($office_page, $view_mode);
  }

  /**
   * Return all datasets of an operation, sector or cluster.
   */
  public function getDatasets($group, Request $request) {
    $limit = 10;
    $offset = 0;

    if ($request->query->has('page')) {
      $offset = $request->query->getInt('page', 0) * $limit;
    }

    if ($group->field_operation->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('Operation not set.'),
      ];
    }

    // Get country.
    $country = $group->field_operation->entity->field_country->entity;

    $endpoint = 'https://data.humdata.org/api/3/action/package_search';
    $parameters = [
      'q' => 'groups:' . strtolower($country->field_iso_3->value),
      'rows' => $limit,
      'start' => $offset,
    ];

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

    $count = $results['result']['count'];
    $this->pagerManager->createPager($count, $limit);

    $data = [];
    foreach ($results['result']['results'] as $row) {
      $data[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'last_modified' => strtotime($row['last_modified']),
        'source' => $row['dataset_source'],
      ];
    }

    return [
      '#theme' => 'hdx_dataset',
      '#data' => $data,
      '#pager' => [
        '#type' => 'pager',
      ],
    ];
  }

  /**
   * Return all documents of an operation, sector or cluster.
   */
  public function getDocuments($group, Request $request) {
    if ($group->hasField('field_documents_page') && !$group->field_documents_page->isEmpty()) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
      $link = $group->field_documents_page->first();

      // Redirect external links.
      if ($link->isExternal()) {
        return new TrustedRedirectResponse($link->getUrl()->getUri());
      }
    }

    if ($group->field_operation->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('Operation not set.'),
      ];
    }

    $limit = 10;
    $offset = $request->query->getInt('page', 0) * $limit;
    $filters = $request->query->get('filters', []);
    $base_url = $request->getRequestUri();

    // Active facets.
    $active_facets = $this->reliefwebController->buildReliefwebActiveFacets($base_url, $filters);

    // Get country.
    $country = $group->field_operation->entity->field_country->entity;

    $parameters = $this->reliefwebController->buildReliefwebParameters($offset, $limit, $filters, $country->field_iso_3->value);
    $results = $this->reliefwebController->executeReliefwebQuery($parameters);

    $count = $results['totalCount'];
    $this->pagerManager->createPager($count, $limit);

    // Re-order facets.
    $facets = [];
    if (isset($results['embedded'])) {
      $facets = $this->reliefwebController->buildReliefwebFacets($base_url, $results['embedded'], $filters);
    }

    return [
      '#theme' => 'rw_river',
      '#data' => $this->reliefwebController->buildReliefwebObjects($results),
      '#total' => $count,
      '#facets' => $facets,
      '#active_facets' => $active_facets,
      '#pager' => [
        '#type' => 'pager',
      ],
    ];
  }

  /**
   * Return all reports of an operation, sector or cluster.
   */
  public function getReports($group, Request $request) {
    if ($group->hasField('field_documents_page') && !$group->field_documents_page->isEmpty()) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
      $link = $group->field_documents_page->first();

      // Redirect external links.
      if ($link->isExternal()) {
        return new TrustedRedirectResponse($link->getUrl()->getUri());
      }
    }

    if ($group->field_operation->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('Operation not set.'),
      ];
    }

    $limit = 10;
    $offset = $request->query->getInt('page', 0) * $limit;
    $filters = $request->query->get('filters', []);
    $base_url = $request->getRequestUri();

    // Active facets.
    $active_facets = $this->reliefwebController->buildReliefwebActiveFacets($base_url, $filters);

    // Get country.
    $country = $group->field_operation->entity->field_country->entity;

    $parameters = $this->reliefwebController->buildReliefwebParameters($offset, $limit, $filters, $country->field_iso_3->value);
    $parameters['filter']['conditions'][] = [
      'field' => 'format.id',
      'value' => [
        12,
        12570,
      ],
      'operator' => 'OR',
      'negate' => TRUE,
    ];

    $results = $this->reliefwebController->executeReliefwebQuery($parameters);

    $count = $results['totalCount'];
    $this->pagerManager->createPager($count, $limit);

    // Re-order facets.
    $facets = [];
    if (isset($results['embedded'])) {
      $facets = $this->reliefwebController->buildReliefwebFacets($base_url, $results['embedded'], $filters);
    }

    return [
      '#theme' => 'rw_river',
      '#data' => $this->reliefwebController->buildReliefwebObjects($results),
      '#total' => $count,
      '#facets' => $facets,
      '#active_facets' => $active_facets,
      '#pager' => [
        '#type' => 'pager',
      ],
    ];
  }

  /**
   * Return all documents of an operation, sector or cluster.
   */
  public function getInfographics($group, Request $request) {
    if ($group->hasField('field_infographics') && !$group->field_infographics->isEmpty()) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
      $link = $group->field_infographics->first();

      // Redirect external links.
      if ($link->isExternal()) {
        return new TrustedRedirectResponse($link->getUrl()->getUri());
      }
    }

    if ($group->field_operation->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('Operation not set.'),
      ];
    }

    $limit = 10;
    $offset = $request->query->getInt('page', 0) * $limit;
    $filters = $request->query->get('filters', []);
    $base_url = $request->getRequestUri();

    // Active facets.
    $active_facets = $this->reliefwebController->buildReliefwebActiveFacets($base_url, $filters);

    // Get country.
    $country = $group->field_operation->entity->field_country->entity;

    $parameters = $this->reliefwebController->buildReliefwebParameters($offset, $limit, $filters, $country->field_iso_3->value);
    $parameters['filter']['conditions'][] = [
      'field' => 'format.id',
      'value' => [
        12,
        12570,
      ],
      'operator' => 'OR',
      'negate' => FALSE,
    ];

    $results = $this->reliefwebController->executeReliefwebQuery($parameters);

    $count = $results['totalCount'];
    $this->pagerManager->createPager($count, $limit);

    // Re-order facets.
    $facets = [];
    if (isset($results['embedded'])) {
      $facets = $this->reliefwebController->buildReliefwebFacets($base_url, $results['embedded'], $filters);
    }

    return [
      '#theme' => 'rw_river',
      '#data' => $this->reliefwebController->buildReliefwebObjects($results),
      '#total' => $count,
      '#facets' => $facets,
      '#active_facets' => $active_facets,
      '#pager' => [
        '#type' => 'pager',
      ],
    ];
  }

  /**
   * Return all assessments of an operation, sector or cluster.
   */
  public function getAssessments($group, $type = 'list') {
    if ($group->field_operation->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('Operation not set.'),
      ];
    }

    $operation_uuid = $group->field_operation->entity->uuid();

    global $base_url;
    switch ($type) {
      case 'map':
        $src = $base_url . '/rest/assessments/map-data?f[0]=operations:' . $operation_uuid;
        $theme = 'hr_paragraphs_assessments_map';
        break;

      case 'table':
        $src = $base_url . '/rest/assessments/table-data?f[0]=operations:' . $operation_uuid;
        $theme = 'hr_paragraphs_assessments_table';
        break;

      case 'list':
        $src = $base_url . '/rest/assessments/list-data?f[0]=operations:' . $operation_uuid;
        $theme = 'hr_paragraphs_assessments_list';
        break;

      default:
        $src = $base_url . '/rest/assessments/list-data?f[0]=operations:' . $operation_uuid;
        $theme = 'hr_paragraphs_assessments_list';
        break;

    }

    return [
      '#theme' => $theme,
      '#base_url' => $base_url,
      '#src' => $src,
      '#component_url' => '/modules/custom/hr_paragraphs/component/build/',
    ];
  }

  /**
   * Return all events of an operation, sector or cluster.
   */
  public function getEvents($group) {
    if (is_numeric($group)) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    // Settings.
    $settings = [
      'header' => [
        'left' => 'prev,next today',
        'center' => 'title',
        'right' => 'month,agendaWeek,agendaDay,listMonth',
      ],
      'plugins' => [
        'listPlugin',
      ],
      'defaultDate' => date('Y-m-d'),
      'editable' => FALSE,
    ];

    // Set source to proxy.
    $datasource_uri = '/group/' . $group->id() . '/ical';
    $settings['events'] = $datasource_uri;

    // Calendar link.
    $calendar_url = $group->field_calendar_link->uri;

    return [
      'calendar' => [
        '#theme' => 'fullcalendar_calendar',
        '#calendar_id' => 'fullcalendar',
        '#calendar_settings' => $settings,
      ],
      'calendar_popup' => [
        '#type' => 'inline_template',
        '#template' => '
          <div id="fullCalModal" style="display:none;">
          <div>Date: <span id="modalStartDate"></span> <span id="modalEndDate"></span></div>
          <div><span id="modalDescription"></span></div>
          <div>Location: <span id="modalLocation"></span></div>
          <div><span id="modalAttachments"></span></div>
        </div>',
        '#attached' => [
          'library' => [
            'hr_paragraphs/fullcalendar',
          ],
        ],
      ],
      'calendar_link' => [
        '#theme' => 'fullcalendar_link',
        '#calendar_url' => $calendar_url,
      ],
    ];
  }

  /**
   * Proxy iCal requests.
   */
  public function getIcal($group, Request $request) {
    $range_start = $request->query->get('start') ?? date('Y-m-d');
    $range_end = $request->query->get('end') ?? date('Y-m-d', time() + 365 * 24 * 60 * 60);

    // Get iCal URL from group.
    if (is_numeric($group)) {
      $group = $this->entityTypeManager->getStorage('group')->load($group);
    }

    $output = $this->icalController->getIcalEvents($group, $range_start, $range_end);

    return new JsonResponse($output);
  }

}
