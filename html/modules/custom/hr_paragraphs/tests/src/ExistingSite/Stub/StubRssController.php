<?php

namespace Drupal\Tests\hr_paragraphs\ExistingSite\Stub;

use Drupal\hr_paragraphs\Controller\RssController;
use Drupal\Tests\hr_paragraphs\Traits\RssTestDataTrait;

/**
 * Page controller for tabs.
 */
class StubRssController extends RssController {

  use RssTestDataTrait;

  /**
   * {@inheritdoc}
   */
  public function getRssChannelLink($url) : string {
    if ($url !== 'https://www.drupal.org/planet/rss.xml') {
      return '';
    }

    return 'https://www.drupal.org/planet';
  }

  /**
   * {@inheritdoc}
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
