<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\date_recur\DateRecurHelper;
use Drupal\group\Entity\Group;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page controller for tabs.
 */
class IcalController extends ControllerBase {

  /**
   * The HTTP client to fetch the files with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Get ICal events.
   *
   * @param \Drupal\group\Entity\Group $group
   *   Group.
   * @param string $range_start
   *   Start date as string.
   * @param string $range_end
   *   End date as string.
   * @param bool $ignore_exceptions
   *   Flag that allows this controller to be used as helper.
   *
   * @return array<int, mixed>
   *   List of events found.
   */
  public function getIcalEvents(Group $group, ?string $range_start = NULL, ?string $range_end = NULL, bool $ignore_exceptions = FALSE) : array {
    $range_start = $range_start ?? date('Y-m-d');
    $range_end = $range_end ?? date('Y-m-d', time() + 365 * 24 * 60 * 60);

    $url = $group->field_ical_url->value;

    // Fetch and parse iCal.
    try {
      $this->getLogger('hr_paragraphs_ical')->notice('Fetching data from @url', [
        '@url' => $url,
      ]);

      $response = $this->httpClient->request(
        'GET',
        $url,
      );
    }
    catch (RequestException $exception) {
      $this->getLogger('hr_paragraphs_ical')->error('Fetching data from @url failed with @message', [
        '@url' => $url,
        '@message' => $exception->getMessage(),
      ]);

      if ($exception->getCode() === 404) {
        // Just return an empty array if used as a helper function.
        if ($ignore_exceptions) {
          return [];
        }
        throw new NotFoundHttpException();
      }

      return [];
    }

    $body = $response->getBody() . '';
    $cal = new CalFileParser();
    $events = $cal->parse($body);

    $output = [];

    if (!is_array($events)) {
      return $output;
    }

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

      // We cannot use the W3C format, as times must not include timezones.
      // And another thing, exclude the time altogether in case of all-day
      // events, so we can render them properly.
      if (isset($event['X-MICROSOFT-CDO-ALLDAYEVENT']) && $event['X-MICROSOFT-CDO-ALLDAYEVENT'] === 'TRUE') {
        $event['DTEND'] = $event['DTSTART'];
        $date_format = 'Y-m-d';
        $all_day_event = TRUE;
      }
      else {
        $date_format = 'Y-m-d\\TH:i:s';
        $all_day_event = FALSE;
      }

      // Make sure DTEND is set.
      if (!isset($event['DTEND'])) {
        $event['DTEND'] = $event['DTSTART'];
      }

      // Handle recurring events.
      if (isset($event['RRULE'])) {
        // Track excluded dates.
        $excluded_dates = [];
        if (isset($event['EXDATE'])) {
          if (is_array($event['EXDATE'])) {
            foreach ($event['EXDATE'] as $ex_date) {
              $excluded_dates[] = $ex_date->format('Y-m-d');
            }
          }
          else {
            $excluded_dates[] = $event['EXDATE']->format('Y-m-d');
          }
        }

        $iterationCount = 0;
        $maxIterations = 40;

        try {
          $rule = DateRecurHelper::create($event['RRULE'], $event['DTSTART'], $event['DTEND']);
          if ($range_start && $range_end) {
            $generator = $rule->generateOccurrences(new \DateTime($range_start), new \DateTime($range_end));
          }
          else {
            $generator = $rule->generateOccurrences(new \DateTime());
          }

          foreach ($generator as $occurrence) {
            // Check excluded dates.
            if (in_array($occurrence->getStart()->format('Y-m-d'), $excluded_dates)) {
              continue;
            }

            /*
             * The generator bumps the end datetime to midnight the next day
             * for al-day events. This causes them to display across two
             * cells in the calendar. Fix that by overriding the end date
             * and ensuring the format includes no time component.
             *
             * @see https://humanitarian.atlassian.net/browse/RWR-512
             */
            $output[] = [
              'title' => $event['SUMMARY'] ?? '',
              'description' => $event['DESCRIPTION'] ?? '',
              'location' => $event['LOCATION'] ?? '',
              'backgroundColor' => $this->setBackgroundColor($event['CATEGORIES'] ?? ''),
              'start' => $occurrence->getStart()->format($date_format),
              'end' => $all_day_event ? $occurrence->getStart()->format($date_format) : $occurrence->getEnd()->format($date_format),
              'timezone' => $event['DTSTART']->getTimezone()->getName(),
              'timezone_string' => $event['timezone_string'] ?? $event['DTSTART']->getTimezone()->getName(),
              'attachments' => $attachments,
            ];

            $iterationCount++;
            if ($iterationCount >= $maxIterations) {
              break;
            }
          }
        }
        catch (\Exception $e) {
          $this->getLogger('hr_paragraphs_ical')->notice('Exception when building event list: @msg', [
            '@msg' => $e->getMessage(),
          ]);

          return [];
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
            'title' => $event['SUMMARY'] ?? '',
            'description' => $event['DESCRIPTION'] ?? '',
            'location' => $event['LOCATION'] ?? '',
            'backgroundColor' => $this->setBackgroundColor($event['CATEGORIES'] ?? ''),
            'start' => $event['DTSTART']->format($date_format),
            'end' => $event['DTEND']->format($date_format),
            'timezone' => $event['DTSTART']->getTimezone()->getName(),
            'timezone_string' => $event['timezone_string'] ?? $event['DTSTART']->getTimezone()->getName(),
            'attachments' => $attachments,
          ];
        }
        else {
          $output[] = [
            'title' => $event['SUMMARY'] ?? '',
            'description' => $event['DESCRIPTION'] ?? '',
            'location' => $event['LOCATION'] ?? '',
            'backgroundColor' => $this->setBackgroundColor($event['CATEGORIES'] ?? ''),
            'start' => $event['DTSTART']->format($date_format),
            'end' => $event['DTEND']->format($date_format),
            'timezone' => $event['DTSTART']->getTimezone()->getName(),
            'timezone_string' => $event['timezone_string'] ?? $event['DTSTART']->getTimezone()->getName(),
            'attachments' => $attachments,
          ];
        }
      }
    }

    // Add local time without timezone info.
    foreach ($output as &$row) {
      $row['local_start'] = substr($row['start'], 0, 19);
      $row['local_end'] = substr($row['end'], 0, 19);
    }

    return $output;
  }

  /**
   * Calculate color value from string.
   */
  protected function setBackgroundColor($category) {
    if (empty($category)) {
      return '';
    }

    return '#' . substr(md5($category), 0, 6);
  }

}
