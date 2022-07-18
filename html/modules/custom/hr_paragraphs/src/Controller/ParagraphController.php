<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\group\Entity\Group;
use GuzzleHttp\ClientInterface;
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
   * Hdx controller.
   *
   * @var \Drupal\hr_paragraphs\Controller\HdxController
   */
  protected $hdxController;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ClientInterface $http_client, PagerManagerInterface $pager_manager, IcalController $ical_controller, ReliefwebController $reliefweb_controller, HdxController $hdx_controller) {
    $this->entityTypeManager = $entity_type_manager;
    $this->httpClient = $http_client;
    $this->pagerManager = $pager_manager;
    $this->icalController = $ical_controller;
    $this->reliefwebController = $reliefweb_controller;
    $this->hdxController = $hdx_controller;
  }

  /**
   * Helper to check if tab is active.
   */
  protected function tabIsActive(Group $group, string $tab) : bool {
    if (!$group->hasField('field_enabled_tabs')) {
      return FALSE;
    }

    $enabled_tabs = $group->field_enabled_tabs->getValue();
    array_walk($enabled_tabs, function (&$item) {
      $item = $item['value'];
    });

    return in_array($tab, $enabled_tabs);
  }

  /**
   * Check if offices is enabled.
   */
  public function hasContacts(Group $group) : AccessResult {
    $active = $this->tabIsActive($group, 'offices');
    if (!$active) {
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
  public function hasPages(Group $group) : AccessResult {
    $active = $this->tabIsActive($group, 'pages');
    if (!$active) {
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
  public function hasAssessments(Group $group) : AccessResult {
    $active = $this->tabIsActive($group, 'assessments');
    if (!$active) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIf(!$group->field_assessments_page->isEmpty() || !$group->field_reliefweb_assessments->isEmpty());
  }

  /**
   * Check if datasets is enabled.
   */
  public function hasDatasets(Group $group) : AccessResult {
    $active = $this->tabIsActive($group, 'data');
    if (!$active) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIf(!$group->field_hdx_alternate_source->isEmpty() || !$group->field_hdx_dataset_link->isEmpty());
  }

  /**
   * Check if documents is enabled.
   */
  public function hasDocuments(Group $group) : AccessResult {
    $active = $this->tabIsActive($group, 'documents');
    if (!$active) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIf(!$group->field_documents_page->isEmpty() || !$group->field_reliefweb_documents->isEmpty());
  }

  /**
   * Check if maps is enabled.
   */
  public function hasInfographics(Group $group) : AccessResult {
    $active = $this->tabIsActive($group, 'maps');
    if (!$active) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIf(!$group->field_infographics->isEmpty() || !$group->field_maps_infographics_link->isEmpty());
  }

  /**
   * Check if events is enabled.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Access.
   */
  public function hasEvents(Group $group) : AccessResult {
    $active = $this->tabIsActive($group, 'events');
    if (!$active) {
      return AccessResult::forbidden();
    }

    if (!$group->hasField('field_ical_url')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIf(!$group->field_ical_url->isEmpty() || !$group->field_calendar_alternate_link->isEmpty());
  }

  /**
   * Return all offices of an operation, sector or cluster.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   *
   * @return array<string, mixed>|\Drupal\Core\Routing\TrustedRedirectResponse
   *   Render array or redirect to external site.
   */
  public function getContacts(Group $group) : array|TrustedRedirectResponse {
    if ($group->field_offices_page->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No contacts link defined.'),
      ];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $group->field_offices_page->first();

    // Redirect external links.
    if ($link->isExternal()) {
      try {
        $redirect_to = new TrustedRedirectResponse($link->getUrl()->getUri());
        $redirect_to->addCacheableDependency($group);
        return $redirect_to;
      }
      catch (\Exception $exception) {
        // Ignore, deleted page.
        throw new NotFoundHttpException();
      }
    }

    $entity_type = 'node';
    $view_mode = 'operation_tab';

    try {
      $params = $link->getUrl()->getRouteParameters();
    }
    catch (\Exception $exception) {
      // Ignore, deleted page.
      throw new NotFoundHttpException();
    }

    $office_page = $this->entityTypeManager->getStorage($entity_type)->load($params[$entity_type]);
    $view_builder = $this->entityTypeManager->getViewBuilder($entity_type);
    return $view_builder->view($office_page, $view_mode);
  }

  /**
   * Render block for adding content.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   *
   * @return array<string, mixed>
   *   Render array.
   *
   * @see html/modules/contrib/group/src/Plugin/Block/GroupOperationsBlock.php
   */
  public function getOperations(Group $group) : array {
    $build = [];

    // The operations available in this block vary per the current user's group
    // permissions. It obviously also varies per group, but we cannot know for
    // sure how we got that group as it is up to the context provider to
    // implement that. This block will then inherit the appropriate cacheable
    // metadata from the context, as set by the context provider.
    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->setCacheContexts(['user.group_permissions']);

    if ($group->id()) {
      $links = [];

      // Retrieve the operations and cacheable metadata from the installed
      // content plugins.
      foreach ($group->getGroupType()->getInstalledContentPlugins() as $plugin) {
        /** @var \Drupal\group\Plugin\GroupContentEnablerInterface $plugin */
        $links += $plugin->getGroupOperations($group);
        $cacheable_metadata = $cacheable_metadata->merge($plugin->getGroupOperationsCacheableMetadata());
      }

      if ($links) {
        // Fix subgroup label.
        foreach ($links as &$link) {
          /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $title */
          $title = $link['title'];

          // Fix subgroup label.
          if ($title->getUntranslatedString() === 'Add @group_type subgroup') {
            $link['title'] = $this->t('Add @group_type', $title->getArguments());
          }

          // Fix leave label.
          if ($title->getUntranslatedString() === 'Leave group') {
            if ($group->bundle() == 'operation') {
              $link['title'] = $this->t('Leave Operation', $title->getArguments());
            }
            else {
              $link['title'] = $this->t('Leave Cluster or Working Group', $title->getArguments());
            }
          }

          // Fix join label.
          if ($title->getUntranslatedString() === 'Join group') {
            if ($group->bundle() == 'operation') {
              $link['title'] = $this->t('Join Operation', $title->getArguments());
            }
            else {
              $link['title'] = $this->t('Join Cluster or Working Group', $title->getArguments());
            }
          }
        }

        // Sort the operations by weight.
        uasort($links, '\Drupal\Component\Utility\SortArray::sortByWeightElement');

        // Create an operations element with all of the links.
        $build['#theme'] = 'links';
        $build['#links'] = $links;
      }
    }

    // Set the cacheable metadata on the build.
    $cacheable_metadata->applyTo($build);

    return $build;
  }

  /**
   * Return all pages of an operation, sector or cluster.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   *
   * @return array<string, mixed>
   *   Render array.
   */
  public function getPages(Group $group) : array {
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
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return array<string, mixed>|\Drupal\Core\Routing\TrustedRedirectResponse
   *   Render array or redirect to external site.
   */
  public function getDatasets(Group $group, Request $request) : array|TrustedRedirectResponse {
    if ($group->hasField('field_hdx_alternate_source') && !$group->field_hdx_alternate_source->isEmpty()) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
      $link = $group->field_hdx_alternate_source->first();

      // Redirect external links.
      if ($link->isExternal()) {
        $redirect_to = new TrustedRedirectResponse($link->getUrl()->getUri());
        $redirect_to->addCacheableDependency($group);
        return $redirect_to;
      }
    }

    if ($group->field_hdx_dataset_link->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('HDX dataset URL not set.'),
      ];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $group->field_hdx_dataset_link->first();
    $url = $link->getUrl()->getUri();

    $limit = 10;
    $offset = $request->query->getInt('page', 0) * $limit;
    $filters = $request->query->get('filters', []);

    $base_url = $request->getRequestUri();

    // Base filter from entered URL.
    $query_filters = $this->hdxController->parseHdxUrl($url);
    if (empty($query_filters)) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('Please make sure the HDX dataset URL is valid.'),
      ];
    }

    // Build Hdx query.
    $parameters = $this->hdxController->buildHdxParameters($offset, $limit, $query_filters);

    // Add filters.
    $hdx_query_filters = $this->hdxController->getHdxQueryFilters();
    foreach ($filters as $key => $filter) {
      if (in_array($key, $hdx_query_filters)) {
        $parameters[$key] = $filter;
      }
      else {
        if (is_array($filter)) {
          $parameters['fq_list'][] = $key . ':"' . implode('" AND "', $filter) . '"';
        }
        else {
          $parameters['fq_list'][] = $key . ':"' . $filter . '"';
        }
      }
    }

    $results = $this->hdxController->executeHdxQuery($parameters);

    // Active facets.
    $active_facets = $this->hdxController->buildHdxActiveFacets($base_url, $filters, $results['search_facets'] ?? []);

    $count = $results['count'];
    $this->pagerManager->createPager($count, $limit);

    // Re-order facets.
    $facets = [];
    if (isset($results['search_facets'])) {
      $facets = $this->hdxController->buildHdxFacets($base_url, $results['search_facets'], $filters, $query_filters);
    }

    return [
      '#theme' => 'river',
      '#service' => 'Humanitarian Data Exchange',
      '#service_url' => 'https://data.humdata.org',
      '#data' => $this->hdxController->buildHdxObjects($results),
      '#total' => $count,
      '#facets' => $facets,
      '#active_facets' => $active_facets,
      '#pager' => [
        '#type' => 'pager',
      ],
      '#group' => $group,
      '#cache' => [
        'tags' => [
          'group',
          $group->id(),
        ],
        'contexts' => [
          'url.query_args:filter',
          'url.query_args:sort',
          'url.query_args:page',
          'url.query_args:limit',
          'url.query_args:offset',
        ],
        'max-age' => 60 * 60,
      ],
    ];
  }

  /**
   * Return all documents of an operation, sector or cluster.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return array<string, mixed>|\Drupal\Core\Routing\TrustedRedirectResponse
   *   Render array or redirect to external site.
   */
  public function getReports(Group $group, Request $request) : array|TrustedRedirectResponse {
    if ($group->hasField('field_documents_page') && !$group->field_documents_page->isEmpty()) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
      $link = $group->field_documents_page->first();

      // Redirect external links.
      if ($link->isExternal()) {
        $redirect_to = new TrustedRedirectResponse($link->getUrl()->getUri());
        $redirect_to->addCacheableDependency($group);
        return $redirect_to;
      }
    }

    if ($group->field_reliefweb_documents->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('Reliefweb URL not set.'),
      ];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $group->field_reliefweb_documents->first();
    $url = $link->getUrl()->getUri();
    return $this->getReliefwebDocuments($request, $group, $url);
  }

  /**
   * Return all documents of an operation, sector or cluster.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return array<string, mixed>|\Drupal\Core\Routing\TrustedRedirectResponse
   *   Render array or redirect to external site.
   */
  public function getInfographics(Group $group, Request $request) : array|TrustedRedirectResponse {
    if ($group->hasField('field_infographics') && !$group->field_infographics->isEmpty()) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
      $link = $group->field_infographics->first();

      // Redirect external links.
      if ($link->isExternal()) {
        $redirect_to = new TrustedRedirectResponse($link->getUrl()->getUri());
        $redirect_to->addCacheableDependency($group);
        return $redirect_to;
      }
    }

    if ($group->get('field_maps_infographics_link')->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('Reliefweb URL not set.'),
      ];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $group->field_maps_infographics_link->first();
    $url = $link->getUrl()->getUri();
    return $this->getReliefwebDocuments($request, $group, $url);
  }

  /**
   * Return all reports of an operation, sector or cluster.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   * @param string $url
   *   Url.
   *
   * @return array<string, mixed>
   *   Render array.
   */
  public function getReliefwebDocuments(Request $request, Group $group, string $url) : array {
    $limit = 10;
    $offset = $request->query->getInt('page', 0) * $limit;
    $filters = $request->query->get('filters', []);
    $base_url = $request->getRequestUri();

    // Active facets.
    $active_facets = $this->reliefwebController->buildReliefwebActiveFacets($base_url, $filters);

    $parameters = $this->reliefwebController->buildReliefwebParameters($offset, $limit, $filters);

    // Base filter from entered URL.
    $conditions = $this->reliefwebController->parseReliefwebUrl($url);

    // Check for search paramater as well.
    if (isset($conditions['_query'])) {
      $parameters['query'] = [
        'value' => $conditions['_query'],
        'operator' => 'AND',
      ];
      unset($conditions['_query']);
    }

    foreach ($conditions as $condition) {
      $negative_operators = [
        'and-without',
        'or-without',
      ];
      $negate = FALSE;

      if (in_array($condition['operator'], $negative_operators)) {
        $negate = TRUE;
      }

      $parameters['filter']['conditions'][] = [
        'field' => $condition['field'],
        'value' => $condition['value'],
        'negate' => $negate,
      ];
    }

    $results = $this->reliefwebController->executeReliefwebQuery($parameters);

    $count = $results['totalCount'];
    $this->pagerManager->createPager($count, $limit);

    // Re-order facets.
    $facets = [];
    if (isset($results['embedded'])) {
      $facets = $this->reliefwebController->buildReliefwebFacets($base_url, $results['embedded'], $filters);
    }

    return [
      '#theme' => 'river',
      '#service' => 'ReliefWeb',
      '#service_url' => 'https://reliefweb.int',
      '#data' => $this->reliefwebController->buildReliefwebObjects($results),
      '#total' => $count,
      '#facets' => $facets,
      '#active_facets' => $active_facets,
      '#pager' => [
        '#type' => 'pager',
      ],
      '#group' => $group,
      '#cache' => [
        'tags' => [
          'group',
          $group->id(),
        ],
        'contexts' => [
          'url.path',
          'url.query_args:filter',
          'url.query_args:sort',
          'url.query_args:page',
          'url.query_args:limit',
          'url.query_args:offset',
        ],
        'max-age' => 60 * 60,
      ],
    ];
  }

  /**
   * Return all assessments of an operation, sector or cluster.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return array<string, mixed>|\Drupal\Core\Routing\TrustedRedirectResponse
   *   Render array or redirect to external site.
   */
  public function getAssessments(Group $group, Request $request) : array|TrustedRedirectResponse {
    if ($group->hasField('field_assessments_page') && !$group->field_assessments_page->isEmpty()) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
      $link = $group->field_assessments_page->first();

      // Redirect external links.
      if ($link->isExternal()) {
        $redirect_to = new TrustedRedirectResponse($link->getUrl()->getUri());
        $redirect_to->addCacheableDependency($group);
        return $redirect_to;
      }
    }

    if ($group->field_reliefweb_assessments->isEmpty()) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('Reliefweb URL not set.'),
      ];
    }

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
    $link = $group->field_reliefweb_assessments->first();
    $url = $link->getUrl()->getUri();
    return $this->getReliefwebDocuments($request, $group, $url);
  }

  /**
   * Return all events of an operation, sector or cluster.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   *
   * @return array<string, mixed>|\Drupal\Core\Routing\TrustedRedirectResponse
   *   Render array or redirect to external site.
   */
  public function getEvents(Group $group) : array|TrustedRedirectResponse {
    if ($group->hasField('field_calendar_alternate_link') && !$group->field_calendar_alternate_link->isEmpty()) {
      /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
      $link = $group->field_calendar_alternate_link->first();

      // Redirect external links.
      if ($link->isExternal()) {
        $redirect_to = new TrustedRedirectResponse($link->getUrl()->getUri());
        $redirect_to->addCacheableDependency($group);
        return $redirect_to;
      }
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
    $calendar_url = '';
    if ($group->hasField('field_calendar_link') && !$group->field_calendar_link->isEmpty()) {
      $calendar_url = $group->get('field_calendar_link')->getValue()[0]['uri'];
    }

    return [
      'calendar' => [
        '#theme' => 'fullcalendar_calendar',
        '#calendar_id' => 'fullcalendar',
        '#calendar_settings' => $settings,
        '#group' => $group,
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
      '#cache' => [
        'tags' => [
          'group',
          $group->id(),
        ],
        'contexts' => [
          'url.path',
          'url.query_args:filter',
          'url.query_args:sort',
          'url.query_args:page',
          'url.query_args:limit',
          'url.query_args:offset',
        ],
        'max-age' => 60 * 60,
      ],
    ];
  }

  /**
   * Proxy iCal requests.
   */
  public function getIcal(Group $group, Request $request) : JsonResponse {
    $range_start = $request->query->get('start') ?? date('Y-m-d');
    $range_end = $request->query->get('end') ?? date('Y-m-d', time() + 365 * 24 * 60 * 60);

    $output = $this->icalController->getIcalEvents($group, $range_start, $range_end);
    return new JsonResponse($output);
  }

}
