<?php

namespace Drupal\hr_paragraphs;

use Google_Client;
use GuzzleHttp\Client;
use Google_Service_Calendar;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Google_Service_Exception;
use DateTime;
use DateTimeInterface;

/**
 * Fetch events from Google Calendar.
 *
 * @package Drupal\hr_paragraphs
 */
class GoogleCalendar {
  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The file_system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a UserPasswordForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file_system.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    FileSystemInterface $file_system) {

    $this->configFactory = $config_factory;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('file_system')
    );
  }

  /**
   * Return a configured HttpClient object.
   */
  public function getEvents($calendar_id, $start, $end) {
    $events = [];

    $client = new Google_Client();
    $secret_file = $this->fileSystem->realpath('../hrinfo2022-5451701ca8f2.json');
    $client->setAuthConfig($secret_file);
    $client->setScopes([Google_Service_Calendar::CALENDAR_READONLY, Google_Service_Calendar::CALENDAR_EVENTS_READONLY]);

    // Config HTTP client and config timeout.
    $client->setHttpClient(new Client([
      'timeout' => 10,
      'connect_timeout' => 10,
      'verify' => FALSE,
    ]));

    $service = new Google_Service_Calendar($client);

    $range = [
      'timeMin' => date(DateTime::RFC3339, strtotime($start)),
      'timeMax' => date(DateTime::RFC3339, strtotime('+1 day', strtotime($end))),
    ];

    $opts = [
      'singleEvents' => TRUE,
      'orderBy' => 'startTime',
      'timeMin' => $range['timeMin'],
      'timeMax' => $range['timeMax'],
    ];

    // List events api.
    $response = $service->events->listEvents($calendar_id, $opts);
    $items = $response->getItems();

    /** @var \Google\Service\Calendar\Event $event */
    foreach ($items as $event) {
      $details = [
        'title' => $event->summary,
        'description' => $event->description,
        'location' => $event->location,
        'start' => $event->start->dateTime,//->format(DateTimeInterface::W3C),
        'end' => $event->end->dateTime,//->format(DateTimeInterface::W3C),
        'color' => $event->colorId,
        'attachments' => [],
      ];

      if ($event->attachments) {
        /** @var EventAttachment $attachment */
        foreach($event->attachments as $attachment) {
          $details['attachments'][] = [
            'filename' => $attachment->title,
            'url' => $attachment->fileUrl,
          ];
        }
      }

      $events[] = $details;
    }
  }

  /**
$calendar_id = 'c_shf84jkdn0jldkbn1a5camhq7c@group.calendar.google.com';
$start = '2022-01-01';
$end = '2022-03-01';

$client = \Drupal::service('hr_paragraphs.google_calendar');
$client->getEvents($calendar_id, $start, $end);*
   */
}
