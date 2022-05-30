<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\ExistingSite;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\hr_paragraphs\Controller\IcalController;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\hr_paragraphs\Traits\IcalTestDataTrait;
use Drupal\theme_switcher\Entity\ThemeSwitcherRule;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests RSS feed.
 */
class HrParagraphsICalTest extends ExistingSiteBase {

  use IcalTestDataTrait;

  /**
   * An http client.
   */
  protected $httpClient;

  /**
   * Set HTTP response.
   */
  protected function setHttpDataResult($data): void {
    $mock = new MockHandler([
      new Response(200, [], $data),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $this->httpClient = new Client(['handler' => $handlerStack]);

    $ical_controller = new IcalController($this->httpClient);
    $this->container->set('hr_paragraphs.ical_controller', $ical_controller);
    \Drupal::setContainer($this->container);
  }

  /**
   * Set HTTP excpetion.
   */
  protected function setHttpException(): void {
    $mock = new MockHandler([
      new RequestException('Request exception', new Request('GET', '')),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $this->httpClient = new Client(['handler' => $handlerStack]);

    $ical_controller = new IcalController($this->httpClient);
    $this->container->set('hr_paragraphs.ical_controller', $ical_controller);
    \Drupal::setContainer($this->container);
  }

  /**
   * Set 404 HTTP excpetion.
   */
  protected function setHttpException404(): void {
    $mock = new MockHandler([
      new Response(404, ['Content-Length' => 0]),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $this->httpClient = new Client(['handler' => $handlerStack]);

    $ical_controller = new IcalController($this->httpClient);
    $this->container->set('hr_paragraphs.ical_controller', $ical_controller);
    \Drupal::setContainer($this->container);
  }

  protected function renderIt($entity_type, $entity) {
    $theme_rule = ThemeSwitcherRule::load('operation_management');

    // Disable the rule, throws an error on line 184 in
    // html/core/modules/path_alias/src/AliasManager.php.
    $theme_rule->disable()->save();

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
    $build = $view_builder->view($entity);
    $output = \Drupal::service('renderer')->renderRoot($build);

    $theme_rule->enable()->save();
    return $output->__toString();
  }

  /**
   * Test upcoming events on group.
   */
  public function testUpcomingEventsOnOperation() {
    $this->setHttpDataResult($this->getTestIcal1());

    $group_title = 'Operation X';
    $paragraph_title = 'ICal data';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'upcoming_events',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->isNew();
    $paragraph->save();

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);

    $group->set('field_ical_url', 'https://www.example.com/events');
    $group->set('field_paragraphs', [
      $paragraph,
    ]);
    $group->setPublished()->save();

    $output = $this->renderIt('group', $group);

    $this->assertStringContainsString($group_title, $output);
    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringContainsString('ESNFI Cluster Reporthub Online Training', $output);
  }

  /**
   * Test events tab on group.
   *
   * Testing actual calendar data isn't possible.
   */
  public function testEventsTabOnOperation() {
    $this->setHttpDataResult($this->getTestIcal1());

    $group_title = 'Operation X';
    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);

    $group->set('field_ical_url', 'https://www.example.com/events');
    $group->setPublished()->save();

    $url = Url::fromRoute('hr_paragraphs.operation.events', [
      'group' => $group->id(),
    ]);

    $this->drupalGet($url);
    $this->assertSession()->elementTextEquals('css', 'h1.cd-page-title', 'Events');
  }

  /**
   * Test illegal iCal.
   */
  public function testIllegalIcal() {
    $this->setHttpDataResult($this->getTestIcal2());

    $group_title = 'Operation X';
    $paragraph_title = 'ICal data';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'upcoming_events',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->isNew();
    $paragraph->save();

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);

    $group->set('field_ical_url', 'https://www.example.com/events');
    $group->set('field_paragraphs', [
      $paragraph,
    ]);
    $group->setPublished()->save();

    $output = $this->renderIt('group', $group);

    $this->assertStringContainsString($group_title, $output);
    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringNotContainsString('This will not parse', $output);
  }

  /**
   * Test exception.
   */
  public function testHttpException() {
    $this->setHttpException();

    $group_title = 'Operation X';
    $paragraph_title = 'ICal data';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'upcoming_events',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->isNew();
    $paragraph->save();

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);

    $group->set('field_ical_url', 'https://www.example.com/events');
    $group->set('field_paragraphs', [
      $paragraph,
    ]);
    $group->setPublished()->save();

    $output = $this->renderIt('group', $group);

    $this->assertStringContainsString($group_title, $output);
    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringNotContainsString('This will not parse', $output);
  }

  /**
   * Test 404 exception.
   */
  public function testHttpException404() {
    $this->setHttpException404();

    $group_title = 'Operation X';
    $paragraph_title = 'ICal data';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'upcoming_events',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->isNew();
    $paragraph->save();

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);

    $group->set('field_ical_url', 'https://www.example.com/events');
    $group->set('field_paragraphs', [
      $paragraph,
    ]);
    $group->setPublished()->save();

    $output = $this->renderIt('group', $group);

    $this->assertStringContainsString($group_title, $output);
    $this->assertStringContainsString($paragraph_title, $output);

    $this->assertStringNotContainsString('This will not parse', $output);
  }

}
