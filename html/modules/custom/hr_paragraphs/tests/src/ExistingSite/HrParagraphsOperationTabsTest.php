<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\ExistingSite;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\hr_paragraphs\ExistingSite\Stub\StubRssController;
use Drupal\theme_switcher\Entity\ThemeSwitcherRule;
use Drupal\user\Entity\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests RSS feed.
 */
class HrParagraphsOperationTabsTest extends ExistingSiteBase {

  /**
   * Test empty operation.
   */
  public function testEmptyOperation() {
    $group_title = 'Empty operation';

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);
    $group->setPublished()->save();

    $this->drupalGet($group->toUrl());
    $this->assertSession()->titleEquals($group_title . ' | ReliefWeb Operations');

    $tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'datasets',
      'assessments',
    ];

    foreach ($tabs as $tab) {
      $this->assertSession()->linkByHrefNotExists($group->toUrl()->toString() . '/' . $tab);
    }
  }

  /**
   * Test empty operation.
   */
  public function testOperationWithEvents() {
    $group_title = 'Operation with events';

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);
    $group->setPublished()->save();

    $this->drupalGet($group->toUrl());
    $this->assertSession()->titleEquals($group_title . ' | ReliefWeb Operations');

    $inactive_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'datasets',
      'assessments',
    ];

    $user = User::getAnonymousUser();
    foreach ($inactive_tabs as $tab) {
      $this->assertSession()->linkByHrefNotExists($group->toUrl()->toString() . '/' . $tab);

      $url = Url::fromRoute('hr_paragraphs.operation.' . $tab, [
        'group' => $group->id(),
      ]);
      $this->assertFalse($url->access($user));
    }

    $group->set('field_ical_url', 'https://www.example.com/events');;
    $group->save();

    $this->drupalGet($group->toUrl());
    $this->assertSession()->titleEquals($group_title . ' | ReliefWeb Operations');

    $inactive_tabs = [
      'contacts',
      'pages',
      'reports',
      'maps',
      'datasets',
      'assessments',
    ];
    $active_tabs = [
      'events',
    ];

    foreach ($active_tabs as $tab) {
      $this->assertSession()->linkByHrefExists($group->toUrl()->toString() . '/' . $tab);

      $url = Url::fromRoute('hr_paragraphs.operation.' . $tab, [
        'group' => $group->id(),
      ]);
      $this->assertTrue($url->access($user));
    }
  }

}
