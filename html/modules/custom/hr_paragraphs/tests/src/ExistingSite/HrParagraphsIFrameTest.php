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
class HrParagraphsIFrameTest extends ExistingSiteBase {

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
   * Test Iframe on group.
   */
  public function testEmptyIframeOnOperation() {
    $group_title = 'Operation X';
    $paragraph_title = 'IFrame';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'iframe',
    ]);
    $paragraph->set('field_title', $paragraph_title);
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
  }

  /**
   * Test Iframe on group.
   */
  public function testIframeUrlOnOperation() {
    $group_title = 'Operation X';
    $paragraph_title = 'IFrame with Url';
    $test_url = 'https://www.example.com';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'iframe',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->set('field_iframe_url', $test_url);
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
    $this->assertStringContainsString($test_url, $output);
    $this->assertStringContainsString('width="100%"', $output);
    $this->assertStringContainsString('height="500px"', $output);
  }

  /**
   * Test Iframe on group.
   */
  public function testIframeEmbedOnOperation() {
    $group_title = 'Operation X';
    $paragraph_title = 'IFrame with Url';
    $test_url = 'https://www.example.com';
    $test_embed = '<iframe id="inlineFrameExample" title="Inline Frame Example" width="300" height="200" src="' . $test_url . '"></iframe>';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'iframe',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->set('field_embed_code', $test_embed);
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
    $this->assertStringContainsString($test_url, $output);
    $this->assertStringContainsString('width="300"', $output);
    $this->assertStringContainsString('height="200"', $output);
  }

  /**
   * Test Iframe on group.
   */
  public function testIframeEmbedRatio169OnOperation() {
    $group_title = 'Operation X';
    $paragraph_title = 'IFrame with Url';
    $test_url = 'https://www.example.com';
    $test_embed = '<iframe id="inlineFrameExample" title="Inline Frame Example" width="300" height="200" src="' . $test_url . '"></iframe>';
    $ratio = 'ratio-16-9';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'iframe',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->set('field_embed_code', $test_embed);
    $paragraph->set('field_iframe_aspect_ratio', $ratio);

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
    $this->assertStringContainsString($test_url, $output);
    $this->assertStringContainsString($ratio, $output);
    $this->assertStringContainsString('width="300"', $output);
    $this->assertStringContainsString('height="200"', $output);
  }

  /**
   * Test Iframe on group.
   */
  public function testIframeEmbedRatio43OnOperation() {
    $group_title = 'Operation X';
    $paragraph_title = 'IFrame with Url';
    $test_url = 'https://www.example.com';
    $test_embed = '<iframe id="inlineFrameExample" title="Inline Frame Example" width="300" height="200" src="' . $test_url . '"></iframe>';
    $ratio = 'ratio-4-3';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'iframe',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->set('field_embed_code', $test_embed);
    $paragraph->set('field_iframe_aspect_ratio', $ratio);

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
    $this->assertStringContainsString($test_url, $output);
    $this->assertStringContainsString($ratio, $output);
    $this->assertStringContainsString('width="300"', $output);
    $this->assertStringContainsString('height="200"', $output);
  }

  /**
   * Test Iframe on group.
   */
  public function testIframeEmbedRatioAutoOnOperation() {
    $group_title = 'Operation X';
    $paragraph_title = 'IFrame with Url';
    $test_url = 'https://www.example.com';
    $test_embed = '<iframe id="inlineFrameExample" title="Inline Frame Example" width="300" height="200" src="' . $test_url . '"></iframe>';
    $ratio = 'ratio-auto';

    // RSS.
    $paragraph = Paragraph::create([
      'type' => 'iframe',
    ]);
    $paragraph->set('field_title', $paragraph_title);
    $paragraph->set('field_embed_code', $test_embed);
    $paragraph->set('field_iframe_aspect_ratio', $ratio);

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
    $this->assertStringContainsString($test_url, $output);
    $this->assertStringContainsString($ratio, $output);
    $this->assertStringContainsString('width="300"', $output);
    $this->assertStringContainsString('height="200"', $output);
  }

}
