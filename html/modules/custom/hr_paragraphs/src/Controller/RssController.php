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
   * Static cache.
   *
   * @var string
   */
  protected $staticCache = '';

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Fetch XML.
   *
   * @param string $url
   *   RSS feed url.
   *
   * @return \SimpleXmlElement|bool
   *   XML.
   */
  protected function getXml($url) {
    if (!empty($this->staticCache)) {
      return $this->staticCache;
    }

    try {
      $response = $this->httpClient->request('GET', $url);
      $this->staticCache = new \SimpleXmlElement($response->getBody());
      return $this->staticCache;
    }
    catch (\Exception $exception) {
      return FALSE;
    }
  }

  /**
   * Get channel link.
   *
   * @param string $url
   *   RSS feed url.
   *
   * @return string
   *   Link of the feed/page.
   */
  public function getRssChannelLink($url) : string {
    $xml = $this->getXml($url);
    if (!$xml) {
      return '';
    }

    if (!empty($xml->channel->link[0])) {
      return (string) $xml->channel->link[0];
    }

    return '';
  }

  /**
   * Get rss items.
   *
   * @param string $url
   *   RSS feed url.
   *
   * @return array
   *   List of items.
   */
  public function getRssItems($url) : array {
    $items = [];

    $xml = $this->getXml($url);
    if (!$xml) {
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
