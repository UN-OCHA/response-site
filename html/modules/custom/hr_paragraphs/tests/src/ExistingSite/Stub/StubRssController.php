<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Tests\hr_paragraphs\Traits\RssTestDataTrait;
use GuzzleHttp\ClientInterface;

/**
 * Page controller for tabs.
 */
class StubRssController extends RssController {

  use RssTestDataTrait;

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
   */
  public function getRssItems($url) : array {
    $items = [];

    if ($url !== 'https://www.drupal.org/planet/rss.xml') {
      return $items;
    }

    $xml = new \SimpleXmlElement($this->getTestRss1());
    foreach ($xml->channel->item as $entry) {
      $items[] = [
        'title' => (string) $entry->title,
        'link' => (string) $entry->link,
        'description' => (string) $entry->description,
        'date' => strtotime($entry->pubDate),
      ];
    }

    return $items;
  }

}
