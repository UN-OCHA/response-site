<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\ExistingSite;

use Drupal\group\Entity\Group;
use Drupal\hr_paragraphs\Controller\RssController;
use Drupal\paragraphs\Entity\Paragraph;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests RSS feed.
 */
class HrParagraphsRssTest extends ExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $mock = new MockHandler([]);
    $handlerStack = HandlerStack::create($mock);
    $http_client = new Client(['handler' => $handlerStack]);

    /** @var \Drupal\hr_paragraphs\Controller\RssController $rss_controller */
    $rss_controller = new RssController($http_client);

    $container = $this->kernel->getContainer();
    $container->set('hr_paragraphs.rss_controller', $rss_controller);
    \Drupal::setContainer($this->container);
  }

  /**
   * Test XML import.
   */
  public function testRssOnPage() {
    $author = $this->createUser([], null, true);
    $page_title = 'RSS test';
    $paragraph_title = 'Drupal Planet RSS';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'rss_feed',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->set('field_max_number_of_items', 3);
    $paragraph->set('field_rss_link', [
      'uri' => 'https://www.drupal.org/planet/rss.xml',
    ]);

    $paragraph->isNew();
    $paragraph->save();

    $node = $this->createNode([
      'title' => $page_title,
      'type' => 'page',
      'uid' => $author->id(),
      'created' => time(),
    ]);
    $node->set('field_paragraphs', [
      $paragraph,
    ]);
    $node->setPublished()->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->pageTextContains($page_title);
    $this->assertSession()->pageTextContains($paragraph_title);

    $this->assertSession()->pageTextContains('DrupalCon News: Explore the Thriving Drupal Agency Ecosystem');
    $this->assertSession()->pageTextContains('#! code: Drupal 9: Using The Caching API To Store Data');
    $this->assertSession()->pageTextContains('Docksal: Docksal 1.17.0 Release');
    $this->assertSession()->pageTextNotContains('Centarro: The ABCs of PDPs and PLPs');
    $this->assertSession()->pageTextNotContains('Agiledrop.com Blog: Drupal DevDays 2022 – Revisiting my first in-person Drupal event');
  }

  /**
   * Test XML import.
   */
  public function testBrokenRssOnPage() {
    $author = $this->createUser([], null, true);
    $page_title = 'RSS broken test';
    $paragraph_title = 'A broken RSS feed';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'rss_feed',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->set('field_max_number_of_items', 3);
    $paragraph->set('field_rss_link', [
      'uri' => 'https://www.example.com/broken/rss.xml',
    ]);

    $paragraph->isNew();
    $paragraph->save();

    $node = $this->createNode([
      'title' => $page_title,
      'type' => 'page',
      'uid' => $author->id(),
      'created' => time(),
    ]);
    $node->set('field_paragraphs', [
      $paragraph,
    ]);
    $node->setPublished()->save();

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->pageTextContains($page_title);
    $this->assertSession()->pageTextContains($paragraph_title);
  }

  /**
   * Test XML import.
   */
  public function testRssOnOperation() {
    $group_title = 'Operation X';
    $paragraph_title = 'An RSS feed';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'rss_feed',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->set('field_max_number_of_items', 3);
    $paragraph->set('field_rss_link', [
      'uri' => 'https://www.drupal.org/planet/rss.xml',
    ]);

    $paragraph->isNew();
    $paragraph->save();

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);
    $group->set('field_paragraphs', [
      $paragraph,
    ]);
    $group->setPublished()->save();

    $this->drupalGet($group->toUrl());
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->pageTextContains($group_title);
    $this->assertSession()->pageTextContains($paragraph_title);
  }
}
