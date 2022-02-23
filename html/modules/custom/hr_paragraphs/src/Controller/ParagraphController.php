<?php

namespace Drupal\hr_paragraphs\Controller;

use DateTime;
use DateTimeInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\date_recur\DateRecurHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
   * {@inheritdoc}
   */
  public function __construct(\Drupal\Core\Entity\EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
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
    return $this->tabIsActive($group, 'offices');
  }

  /**
   * Check if assessments is enabled.
   */
  public function hasAssessments($group) {
    return $this->tabIsActive($group, 'assessments');
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

    return AccessResult::allowedIf(!$group->field_ical_url->isEmpty());
  }

  /**
   * Return all offices of an operation, sector or cluster.
   */
  public function getOffices($group) {
    if ($group->field_operation->isEmpty()) {
      return array(
        '#type' => 'markup',
        '#markup' => $this->t('Operation not set.'),
      );
    }

    $operation_uuid = $group->field_operation->entity->uuid();

    $entity_id = 'office';
    $view_mode = 'teaser';

    $office_uuids = $this->entityTypeManager->getStorage($entity_id)->getQuery()->condition('operations', $operation_uuid)->execute();
    $offices = $this->entityTypeManager->getStorage($entity_id)->loadMultiple($office_uuids);

    $view_builder = $this->entityTypeManager->getViewBuilder($entity_id);
    return $view_builder->viewMultiple($offices, $view_mode);
  }

  /**
   * Return all assessments of an operation, sector or cluster.
   */
  public function getAssessments($group, $type = 'list') {
    if ($group->field_operation->isEmpty()) {
      return array(
        '#type' => 'markup',
        '#markup' => $this->t('Operation not set.'),
      );
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
        'right' => 'month,agendaWeek,agendaDay',
      ],
      'defaultDate' => date('Y-m-d'),
      'editable' => FALSE,
    ];

    // Set source to proxy.
    $datasource_uri = '/group/' . $group->id() . '/ical';
    $settings['events'] = $datasource_uri;

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
          ]
        ],
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
    $url = $group->field_ical_url->value;

    // Feych and parse iCal.
    $cal = new CalFileParser();
    $events = $cal->parse($url);

    $output = [];
    foreach ($events as $event) {
      // Collect attachments.
      $attachments = [];
      foreach ($event as $key => $value) {
        if (strpos($key, 'ATTACH;FILENAME=') !== FALSE) {
          $str_length = strlen('ATTACH;FILENAME=');
          $attachments[] = [
            'filename' => substr($key, $str_length, strpos($key, ';', $str_length) - $str_length),
            'url' => $value,
          ];
        }
      }

      if (isset($event['RRULE'])) {
        $iterationCount = 0;
        $maxIterations = 40;

        $rule = DateRecurHelper::create($event['RRULE'], $event['DTSTART'], $event['DTEND']);
        if ($range_start && $range_end) {
          $generator = $rule->generateOccurrences(new DateTime($range_start), new DateTime($range_end));
        }
        else {
          $generator = $rule->generateOccurrences(new DateTime());
        }

        foreach ($generator as $occurrence) {
          $output[] = [
            'title' => $event['SUMMARY'],
            'description' => $event['DESCRIPTION'],
            'location' => $event['LOCATION'],
            'start' => $occurrence->getStart()->format(DateTimeInterface::W3C),
            'end' => $occurrence->getEnd()->format(DateTimeInterface::W3C),
            'attachments' => $attachments,
          ];

          $iterationCount++;
          if ($iterationCount >= $maxIterations) {
            break;
          }
        }
      }
      else {
        if ($range_start && $range_end) {
          if ($event['DTSTART']->format('Y-m-d') > $range_end) {
            continue;
          }
          if ($event['DTEND']->format('Y-m-d') < $range_start) {
            continue;
          }

          $output[] = [
            'title' => $event['SUMMARY'],
            'description' => $event['DESCRIPTION'],
            'location' => $event['LOCATION'],
            'start' => $event['DTSTART']->format(DateTimeInterface::W3C),
            'end' => $event['DTEND']->format(DateTimeInterface::W3C),
            'attachments' => $attachments,
          ];
        }
        else {
          $output[] = [
            'title' => $event['SUMMARY'],
            'description' => $event['DESCRIPTION'],
            'location' => $event['LOCATION'],
            'start' => $event['DTSTART']->format(DateTimeInterface::W3C),
            'end' => $event['DTEND']->format(DateTimeInterface::W3C),
            'attachments' => $attachments,
          ];
        }
      }
    }

    return new JsonResponse($output);
  }
}
