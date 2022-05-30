<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\ExistingSite;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\hr_paragraphs\Controller\HdxController;
use Drupal\hr_paragraphs\Controller\ParagraphController;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\hr_paragraphs\Traits\HdxTestDataTrait;
use Drupal\theme_switcher\Entity\ThemeSwitcherRule;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests contacts page.
 */
class HrParagraphsContactsTest extends ExistingSiteBase {

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
  public function testContactsOnOperation() {
    $paragraph_title = 'List of contacts';
    $node_title = 'My contacts';
    $group_title = 'Operation X';

    $paragraph = Paragraph::create([
      'type' => 'text_block',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->isNew();
    $paragraph->save();

    $contacts = Node::create([
      'type' => 'page',
      'title' => $node_title,
    ]);
    $contacts->set('field_paragraphs', [
      $paragraph,
    ]);
    $contacts->setPublished()->save();

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);
    $group->set('field_offices_page', 'internal:' . $contacts->toUrl()->toString());
    $group->setPublished()->save();

    $output = $this->renderGroupTab($group);
    $this->assertStringContainsString($paragraph_title, $output);

    $url = Url::fromRoute('hr_paragraphs.operation.contacts', [
      'group' => $group->id(),
    ]);

    $this->drupalGet($url);
    $this->assertSession()->elementTextEquals('css', 'h1.cd-page-title', 'Contacts');
    $this->assertSession()->pageTextNotContains($node_title);
}

  protected function renderGroupTab($group) {
    $theme_rule = ThemeSwitcherRule::load('operation_management');

    // Disable the rule, throws an error on line 184 in
    // html/core/modules/path_alias/src/AliasManager.php.
    $theme_rule->disable()->save();

    $paragraph_controller = new ParagraphController(
      \Drupal::service('entity_type.manager'),
      \Drupal::service('http_client'),
      \Drupal::service('pager.manager'),
      \Drupal::service('hr_paragraphs.ical_controller'),
      \Drupal::service('hr_paragraphs.reliefweb_controller'),
      \Drupal::service('hr_paragraphs.hdx_controller'),
    );

    // Render it.
    $request = new Request([
      'filters' => [
        'res_format' => 'XLSX',
      ],
    ]);
    $request->server->set('REQUEST_URI', 'https://example.com/dataset/');
    $build = $paragraph_controller->getContacts($group, $request);
    $output = \Drupal::service('renderer')->renderRoot($build);

    $theme_rule->enable()->save();
    return $output;
  }
}
