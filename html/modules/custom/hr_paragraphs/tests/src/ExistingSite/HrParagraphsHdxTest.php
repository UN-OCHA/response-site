<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\ExistingSite;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\hr_paragraphs\Controller\HdxController;
use Drupal\hr_paragraphs\Controller\ParagraphController;
use Drupal\Tests\hr_paragraphs\Traits\HdxTestDataTrait;
use Drupal\theme_switcher\Entity\ThemeSwitcherRule;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests RSS feed.
 */
class HrParagraphsHdxTest extends ExistingSiteBase {

  use HdxTestDataTrait;

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

    $hdx_controller = new HdxController($this->httpClient);
    $this->container->set('hr_paragraphs.hdx_controller', $hdx_controller);
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

    $hdx_controller = new HdxController($this->httpClient);
    $this->container->set('hr_paragraphs.hdx_controller', $hdx_controller);
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

    $hdx_controller = new HdxController($this->httpClient);
    $this->container->set('hr_paragraphs.hdx_controller', $hdx_controller);
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
   * Test hdx data on group.
   */
  public function testHdxOnOperation() {
    $this->setHttpDataResult($this->getTestHdx1());

    $group_title = 'Operation X';

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);

    $group->set('field_hdx_dataset_link', 'https://data.humdata.org/dataset?groups=afg&groups=syr&organization=fao&organization=hdx');
    $group->setPublished()->save();

    $output = $this->renderGroupTab($group);
    $this->assertStringContainsString('Afghanistan - Subnational Administrative Boundaries', $output);
    $this->assertStringContainsString('Afghanistan administrative level 0-2 and UNAMA region gazetteer and P-code geoservices', $output);

    $url = Url::fromRoute('hr_paragraphs.operation.data', [
      'group' => $group->id(),
    ]);

    $this->drupalGet($url);
    $this->assertSession()->elementTextEquals('css', 'h1.cd-page-title', 'Data');
  }

  /**
   * Test hdx data on group.
   */
  public function testHdxOnOperationIllegalURL() {
    $this->setHttpDataResult($this->getTestHdx1());

    $group_title = 'Operation X';

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);

    $group->set('field_hdx_dataset_link', 'https://www.example.com/data');
    $group->setPublished()->save();

    $output = $this->renderGroupTab($group);
    $this->assertStringContainsString('Please make sure the HDX dataset URL is valid.', $output);
    $this->assertStringNotContainsString('Afghanistan - Subnational Administrative Boundaries', $output);
    $this->assertStringNotContainsString('Afghanistan administrative level 0-2 and UNAMA region gazetteer and P-code geoservices', $output);

    $url = Url::fromRoute('hr_paragraphs.operation.data', [
      'group' => $group->id(),
    ]);

    $this->drupalGet($url);
    $this->assertSession()->elementTextEquals('css', 'h1.cd-page-title', 'Data');
  }

  protected function renderGroupTab($group) {
    $theme_rule = ThemeSwitcherRule::load('operation_management');

    // Disable the rule, throws an error on line 184 in
    // html/core/modules/path_alias/src/AliasManager.php.
    $theme_rule->disable()->save();

    // Use data from trait.
    $mock = new MockHandler([
      new Response(200, [], $this->getTestHdx1()),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $http_client = new Client(['handler' => $handlerStack]);
    $hdx_controller = new HdxController($http_client);

    $paragraph_controller = new ParagraphController(
      \Drupal::service('entity_type.manager'),
      \Drupal::service('http_client'),
      \Drupal::service('pager.manager'),
      \Drupal::service('hr_paragraphs.ical_controller'),
      \Drupal::service('hr_paragraphs.reliefweb_controller'),
      $hdx_controller,
    );

    // Render it.
    $request = new HttpRequest([
      'filters' => [
        'res_format' => 'XLSX',
      ],
    ]);
    $request->server->set('REQUEST_URI', 'https://example.com/dataset/');
    $build = $paragraph_controller->getDatasets($group, $request);
    $output = \Drupal::service('renderer')->renderRoot($build);

    $theme_rule->enable()->save();
    return $output;
  }
}
