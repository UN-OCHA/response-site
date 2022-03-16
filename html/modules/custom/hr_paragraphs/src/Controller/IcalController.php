<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\date_recur\DateRecurHelper;
use GuzzleHttp\ClientInterface;

/**
 * Page controller for tabs.
 */
class IcalController extends ControllerBase {

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
   * Get ICal events.
   */
  public function getIcalEvents($group, $range_start = NULL, $range_end = NULL) : array {
    $range_start = $range_start ?? date('Y-m-d');
    $range_end = $range_end ?? date('Y-m-d', time() + 365 * 24 * 60 * 60);

    $url = $group->field_ical_url->value;

    // Fetch and parse iCal.
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
          $generator = $rule->generateOccurrences(new \DateTime($range_start), new \DateTime($range_end));
        }
        else {
          $generator = $rule->generateOccurrences(new \DateTime());
        }

        foreach ($generator as $occurrence) {
          $output[] = [
            'title' => $event['SUMMARY'],
            'description' => $event['DESCRIPTION'],
            'location' => $event['LOCATION'],
            'start' => $occurrence->getStart()->format(\DateTimeInterface::W3C),
            'end' => $occurrence->getEnd()->format(\DateTimeInterface::W3C),
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
            'start' => $event['DTSTART']->format(\DateTimeInterface::W3C),
            'end' => $event['DTEND']->format(\DateTimeInterface::W3C),
            'attachments' => $attachments,
          ];
        }
        else {
          $output[] = [
            'title' => $event['SUMMARY'],
            'description' => $event['DESCRIPTION'],
            'location' => $event['LOCATION'],
            'start' => $event['DTSTART']->format(\DateTimeInterface::W3C),
            'end' => $event['DTEND']->format(\DateTimeInterface::W3C),
            'attachments' => $attachments,
          ];
        }
      }
    }

    return $output;
  }

}
