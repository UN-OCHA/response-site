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

}
