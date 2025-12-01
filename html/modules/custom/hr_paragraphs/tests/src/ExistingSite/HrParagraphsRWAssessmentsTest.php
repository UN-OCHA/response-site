<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\ExistingSite;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\hr_paragraphs\Controller\ParagraphController;
use Drupal\hr_paragraphs\Controller\ReliefwebController;
use Drupal\hr_paragraphs\Service\ReliefWebApiClient;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\hr_paragraphs\Traits\RWTestDataTrait;
use Drupal\theme_switcher\Entity\ThemeSwitcherRule;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests ReliefWeb pages.
 */
class HrParagraphsRWAssessmentsTest extends ExistingSiteBase {

  use RWTestDataTrait;

  /**
   * An http client.
   */
  protected $httpClient;

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
   * Test RW data on group.
   */
  public function testExternalAssessmentsTabOnOperation() {
    $group_title = 'Operation X';

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);
    $group->set('field_reliefweb_assessments', 'https://reliefweb.int/updates?advanced-search=%28C13%29_%28OT271%29_%28T4587%29_%28F5%29');
    $group->set('field_assessments_page', 'https://www.example.com');
    $group->setPublished()->save();

    /** @var \Drupal\Core\Routing\TrustedRedirectResponse $output */
    $output = $this->renderGroupTabRedirect($group);
    $this->assertEquals('https://www.example.com', $output->getTargetUrl());

    $url = Url::fromRoute('hr_paragraphs.operation.assessments', [
      'group' => $group->id(),
    ]);

    $this->drupalGet($url);
    // @todo: $this->assertSession()->pageTextNotContains('Assessments');
  }

  /**
   * Test RW data on group.
   */
  public function testAssessmentsTabOnOperation() {
    $group_title = 'Operation X';

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);
    $group->set('field_reliefweb_assessments', 'https://reliefweb.int/updates?advanced-search=%28C13%29_%28OT271%29_%28T4587%29_%28F5%29');
    $group->setPublished()->save();

    $output = $this->renderGroupTab($group);
    $this->assertStringContainsString('Afghanistan Emergency Food Security Assessment, August – September 2018', $output);
    $this->assertStringContainsString('WFP, Govt. Afghanistan, FAO, FSC', $output);

    $url = Url::fromRoute('hr_paragraphs.operation.assessments', [
      'group' => $group->id(),
    ]);

    $this->drupalGet($url);
    $this->assertSession()->elementTextEquals('css', 'h1.cd-page-title', 'Assessments');
  }

  /**
   * Test RW data on group.
   */
  public function testRiverOnOperation() {
    $group_title = 'Operation X';

    $paragraph = Paragraph::create([
      'type' => 'reliefweb_river',
    ]);
    $paragraph->set('field_reliefweb_url', 'https://reliefweb.int/updates?advanced-search=%28C13%29_%28OT271%29_%28T4587%29_%28F5%29');
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

    $mock = new MockHandler([
      new Response(200, [], $this->getTestRw1()),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $this->httpClient = new Client(['handler' => $handlerStack]);

    $reliefweb_api_client = new ReliefWebApiClient($this->httpClient, \Drupal::service('logger.factory'), \Drupal::cache(), \Drupal::configFactory());
    $reliefweb_controller = new ReliefwebController($reliefweb_api_client);

    $this->container->set('hr_paragraphs.reliefweb_controller', $reliefweb_controller);
    \Drupal::setContainer($this->container);

    $output = $this->renderIt('group', $group);
    $this->assertStringContainsString('Afghanistan Emergency Food Security Assessment, August – September 2018', $output);
    $this->assertStringContainsString('WFP, Govt. Afghanistan, FAO, FSC', $output);
  }

  /**
   * Test RW data on group.
   */
  public function testDocumentOnOperation() {
    $group_title = 'Operation X';

    $paragraph = Paragraph::create([
      'type' => 'reliefweb_document',
    ]);
    $paragraph->set('field_reliefweb_url', 'https://reliefweb.int/report/afghanistan/afghanistan-food-security-alert-june-20-2011');
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

    $mock = new MockHandler([
      new Response(200, [], $this->getTestRw2()),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $this->httpClient = new Client(['handler' => $handlerStack]);

    $reliefweb_api_client = new ReliefWebApiClient($this->httpClient, \Drupal::service('logger.factory'), \Drupal::cache(), \Drupal::configFactory());
    $reliefweb_controller = new ReliefwebController($reliefweb_api_client);
    $this->container->set('hr_paragraphs.reliefweb_controller', $reliefweb_controller);
    \Drupal::setContainer($this->container);

    $output = $this->renderIt('group', $group);
    $this->assertStringContainsString('Afghanistan Food Security Alert: June 20, 2011', $output);
    $this->assertStringContainsString('Poor rainfed wheat harvest in northern Afghanistan raises need for assistance', $output);
    $this->assertStringContainsString('FEWS NET', $output);
  }

  protected function renderGroupTab($group) {
    $theme_rule = ThemeSwitcherRule::load('operation_management');

    // Disable the rule, throws an error on line 184 in
    // html/core/modules/path_alias/src/AliasManager.php.
    $theme_rule->disable()->save();

    // Use data from trait.
    $mock = new MockHandler([
      new Response(200, [], $this->getTestRw1()),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $http_client = new Client(['handler' => $handlerStack]);
    $reliefweb_api_client = new ReliefWebApiClient($http_client, \Drupal::service('logger.factory'), \Drupal::cache(), \Drupal::configFactory());
    $reliefweb_controller = new ReliefwebController($reliefweb_api_client);

    $paragraph_controller = new ParagraphController(
      \Drupal::service('entity_type.manager'),
      \Drupal::service('http_client'),
      \Drupal::service('pager.manager'),
      \Drupal::service('hr_paragraphs.ical_controller'),
      $reliefweb_controller,
      \Drupal::service('hr_paragraphs.hdx_controller'),
    );

    // Render it.
    $request = new Request([
    ]);

    $request->server->set('REQUEST_URI', 'https://example.com/dataset/');
    $build = $paragraph_controller->getAssessments($group, $request);
    $output = \Drupal::service('renderer')->renderRoot($build);

    $theme_rule->enable()->save();
    return $output;
  }

  protected function renderGroupTabRedirect($group) {
    $theme_rule = ThemeSwitcherRule::load('operation_management');

    // Disable the rule, throws an error on line 184 in
    // html/core/modules/path_alias/src/AliasManager.php.
    $theme_rule->disable()->save();

    // Use data from trait.
    $mock = new MockHandler([
      new Response(200, [], $this->getTestRw1()),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $http_client = new Client(['handler' => $handlerStack]);

    $reliefweb_api_client = new ReliefWebApiClient($http_client, \Drupal::service('logger.factory'), \Drupal::cache(), \Drupal::configFactory());
    $reliefweb_controller = new ReliefwebController($reliefweb_api_client);

    $paragraph_controller = new ParagraphController(
      \Drupal::service('entity_type.manager'),
      \Drupal::service('http_client'),
      \Drupal::service('pager.manager'),
      \Drupal::service('hr_paragraphs.ical_controller'),
      $reliefweb_controller,
      \Drupal::service('hr_paragraphs.hdx_controller'),
    );

    // Render it.
    $request = new Request([
    ]);

    $output =  $paragraph_controller->getAssessments($group, $request);

    $theme_rule->enable()->save();
    return $output;
  }
}
