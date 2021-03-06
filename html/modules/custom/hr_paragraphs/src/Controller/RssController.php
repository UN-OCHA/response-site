<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\hr_paragraphs\RssItem;
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
   * @var \SimpleXMLElement[]
   */
  protected $staticCache = [];

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
    if (!empty($this->staticCache[$url])) {
      return $this->staticCache[$url];
    }

    try {
      libxml_use_internal_errors(TRUE);
      $response = $this->httpClient->request('GET', $url);
      $this->staticCache[$url] = new \SimpleXmlElement($response->getBody());
      return $this->staticCache[$url];
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
   * @return array<int, \Drupal\hr_paragraphs\RssItem>
   *   List of items.
   */
  public function getRssItems($url) : array {
    $items = [];

    $xml = $this->getXml($url);
    if (!$xml) {
      return $items;
    }

    foreach ($xml->channel->item as $entry) {
      $items[] = new RssItem(
        (string) $entry->title,
        (string) $entry->link,
        (string) $entry->description,
        strtotime($entry->pubDate),
      );
    }

    return $items;
  }

}
