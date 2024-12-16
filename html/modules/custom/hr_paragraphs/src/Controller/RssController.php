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
   * Determines the type of feed (RSS or Atom) for a given URL.
   *
   * @param string $url
   *   The URL of the feed to check.
   *
   * @return string
   *   The type of feed (rss or atom), or an empty string
   *   if the feed could not be retrieved or is in an unknown format.
   */
  protected function getFeedType(string $url) : string {
    // Get the XML data for the given URL.
    $xml = $this->getXml($url);

    // If XML data could not be retrieved, return an empty string.
    if (!$xml) {
      return '';
    }

    // If the XML data contains a "channel" element, it is an RSS feed.
    if (!empty($xml->channel)) {
      return 'rss';
    }

    // If the XML data contains an "entry" element, it is an Atom feed.
    if (!empty($xml->entry)) {
      return 'atom';
    }

    // If the XML data is an unknown format, return an empty string.
    return '';
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
      $this->getLogger('hr_paragraphs_rss')->notice('Fetching data from @url', [
        '@url' => $url,
      ]);

      libxml_use_internal_errors(TRUE);
      $response = $this->httpClient->request(
        'GET',
        $url,
        [
          'headers' => [
            'Accept' => 'application/rss+xml',
          ],
        ]
      );
      $this->staticCache[$url] = new \SimpleXmlElement($response->getBody());
      return $this->staticCache[$url];
    }
    catch (\Exception $exception) {
      $this->getLogger('hr_paragraphs_rss')->error('Fetching data from @url failed with @message', [
        '@url' => $url,
        '@message' => $exception->getMessage(),
      ]);

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

    switch ($this->getFeedType($url)) {
      case 'rss':
        return (string) $xml->channel->link[0];

      case 'atom':
        return (string) $xml->link[0]['href'] ?? '';

    }

    return '';
  }

  /**
   * Get RSS items.
   *
   * @param string $url
   *   The RSS feed url.
   *
   * @return array<int, \Drupal\hr_paragraphs\RssItem>
   *   List of RSS items.
   */
  public function getRssItems($url) : array {
    switch ($this->getFeedType($url)) {
      case 'rss':
        return $this->getRssItemsFromFeed($url);

      case 'atom':
        return $this->getAtomItemsFromFeed($url);

    }

    return [];
  }

  /**
   * Returns an array of RssItem objects parsed from an RSS feed URL.
   *
   * @param string $url
   *   The URL of the RSS feed to parse.
   *
   * @return array<int, \Drupal\hr_paragraphs\RssItem>
   *   An array of RssItem objects.
   */
  public function getRssItemsFromFeed($url) : array {
    $items = [];

    $xml = $this->getXml($url);
    if (!$xml) {
      return $items;
    }

    try {
      foreach ($xml->channel->item as $entry) {
        $items[] = new RssItem(
          (string) $entry->title,
          (string) $entry->link,
          (string) $entry->description,
          strtotime($entry->pubDate),
        );
      }
    }
    catch (\Exception $e) {
      return $items;
    }

    return $items;

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
  public function getAtomItemsFromFeed($url) : array {
    $items = [];

    $xml = $this->getXml($url);
    if (!$xml) {
      return $items;
    }

    try {
      foreach ($xml->entry as $entry) {
        $items[] = new RssItem(
          (string) $entry->title,
          (string) $entry->link,
          (string) $entry->summary,
          strtotime($entry->updated),
        );
      }
    }
    catch (\Exception $e) {
      return $items;
    }

    return $items;
  }

}
