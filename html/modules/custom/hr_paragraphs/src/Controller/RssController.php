<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;

/**
 * Page controller for tabs.
 */
class RssController extends ControllerBase {

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

    try {
      $response = $this->httpClient->request('GET', $url);
      $xml = new \SimpleXmlElement($response->getBody());
    }
    catch (\Exception $exception) {
      return $items;
    }

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
