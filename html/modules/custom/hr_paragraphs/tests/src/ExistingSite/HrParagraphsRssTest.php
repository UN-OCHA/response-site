<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\ExistingSite;

use Drupal\group\Entity\Group;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\hr_paragraphs\ExistingSite\Stub\StubRssController;
use Drupal\theme_switcher\Entity\ThemeSwitcherRule;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests RSS feed.
 *
 * @coversDefaultClass \Drupal\hr_paragraphs\Controller\RssController
 */
class HrParagraphsRssTest extends ExistingSiteBase {

  /**
   * An http client.
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $mock = new MockHandler([]);
    $handlerStack = HandlerStack::create($mock);
    $this->httpClient = new Client(['handler' => $handlerStack]);

    $rss_controller = new StubRssController($this->httpClient);
    $this->container->set('hr_paragraphs.rss_controller', $rss_controller);
    \Drupal::setContainer($this->container);
  }

  protected function renderIt($entity_type, $entity) {
    $theme_rule = ThemeSwitcherRule::load('operation_management');

    // Disabel the rule, throws an error on line 184 in
    // html/core/modules/path_alias/src/AliasManager.php.
    $theme_rule->disable()->save();

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
    $build = $view_builder->view($entity);
    $output = \Drupal::service('renderer')->renderRoot($build);

    $theme_rule->enable()->save();
    return $output->__toString();
  }

  /**
   * Test RSS feed on a page.
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

    $output = $this->renderIt('node', $node);

    $this->assertStringContainsString($page_title, $output);
    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringContainsString('DrupalCon News: Explore the Thriving Drupal Agency Ecosystem', $output);
    $this->assertStringContainsString('#! code: Drupal 9: Using The Caching API To Store Data', $output);
    $this->assertStringContainsString('Docksal: Docksal 1.17.0 Release', $output);
    $this->assertStringNotContainsString('Centarro: The ABCs of PDPs and PLPs', $output);
    $this->assertStringNotContainsString('Agiledrop.com Blog: Drupal DevDays 2022 – Revisiting my first in-person Drupal event', $output);
  }

  /**
   * Test RSS feed on a page.
   */
  public function testRssOnPageCustomReadMore() {
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
    $paragraph->set('field_rss_read_more', [
      'uri' => 'https://www.example.com/readmore',
    ]);
    $paragraph->set('field_rss_options', [
      ['value' => 'display_date'],
      ['value' => 'display_read_more'],
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

    $output = $this->renderIt('node', $node);

    $this->assertStringContainsString($page_title, $output);
    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringContainsString('DrupalCon News: Explore the Thriving Drupal Agency Ecosystem', $output);
    $this->assertStringContainsString('#! code: Drupal 9: Using The Caching API To Store Data', $output);
    $this->assertStringContainsString('Docksal: Docksal 1.17.0 Release', $output);
    $this->assertStringNotContainsString('Centarro: The ABCs of PDPs and PLPs', $output);
    $this->assertStringNotContainsString('Agiledrop.com Blog: Drupal DevDays 2022 – Revisiting my first in-person Drupal event', $output);

    $this->assertStringNotContainsString('https://www.drupal.org/planet', $output);
    $this->assertStringContainsString('https://www.example.com/readmore', $output);

  }

  /**
   * Test illegal RSS url.
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

    $output = $this->renderIt('node', $node);

    $this->assertStringContainsString($page_title, $output);
    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringNotContainsString('DrupalCon News: Explore the Thriving Drupal Agency Ecosystem', $output);
    $this->assertStringNotContainsString('#! code: Drupal 9: Using The Caching API To Store Data', $output);
    $this->assertStringNotContainsString('Docksal: Docksal 1.17.0 Release', $output);
    $this->assertStringNotContainsString('Centarro: The ABCs of PDPs and PLPs', $output);
    $this->assertStringNotContainsString('Agiledrop.com Blog: Drupal DevDays 2022 – Revisiting my first in-person Drupal event', $output);
  }

  /**
   * Test RSS without url.
   */
  public function testRssWithoutURLOnPage() {
    $author = $this->createUser([], null, true);
    $page_title = 'RSS broken test';
    $paragraph_title = 'A broken RSS feed';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'rss_feed',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->set('field_max_number_of_items', 3);

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

    $output = $this->renderIt('node', $node);

    $this->assertStringContainsString($page_title, $output);
    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringNotContainsString('DrupalCon News: Explore the Thriving Drupal Agency Ecosystem', $output);
    $this->assertStringNotContainsString('#! code: Drupal 9: Using The Caching API To Store Data', $output);
    $this->assertStringNotContainsString('Docksal: Docksal 1.17.0 Release', $output);
    $this->assertStringNotContainsString('Centarro: The ABCs of PDPs and PLPs', $output);
    $this->assertStringNotContainsString('Agiledrop.com Blog: Drupal DevDays 2022 – Revisiting my first in-person Drupal event', $output);
  }

  /**
   * Test RSS on group.
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
    $paragraph->set('field_rss_options', [
      ['value' => 'display_date'],
      ['value' => 'display_read_more'],
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

    $output = $this->renderIt('group', $group);

    $this->assertStringContainsString($group_title, $output);
    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringContainsString('https://www.drupal.org/planet', $output);
  }

}
