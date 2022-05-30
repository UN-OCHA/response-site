<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\ExistingSite;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests RSS feed.
 */
class HrParagraphsClusterTabsTest extends ExistingSiteBase {

  /**
   * Test empty cluster.
   */
  public function testEmptyCluster() {
    $site_name = \Drupal::config('system.site')->get('name');
    $group_title = 'Empty cluster';

    $group = Group::create([
      'type' => 'cluster',
      'label' => $group_title,
    ]);
    $group->setPublished()->save();

    $this->drupalGet($group->toUrl());
    $this->assertSession()->titleEquals($group_title . ' | ' . $site_name);

    $inactive_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);
  }

  /**
   * Test cluster tabs.
   */
  public function testClusterTabs() {
    $site_name = \Drupal::config('system.site')->get('name');
    $group_title = 'Cluster with tabs';

    $group = Group::create([
      'type' => 'cluster',
      'label' => $group_title,
    ]);
    $group->setPublished()->save();

    $this->drupalGet($group->toUrl());
    $this->assertSession()->titleEquals($group_title . ' | ' . $site_name);

    $inactive_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable events.
    $group->set('field_ical_url', 'https://www.example.com/events');
    $group->save();

    $inactive_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable checkbox.
    $group->set('field_enabled_tabs', [
      ['value' => 'events'],
    ]);
    $group->save();

    $inactive_tabs = [
      'contacts',
      'pages',
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [
      'events',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable contacts.
    $group->set('field_offices_page', 'https://www.example.com/contacts');
    $group->save();

    $inactive_tabs = [
      'contacts',
      'pages',
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [
      'events',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable checkbox.
    $group->set('field_enabled_tabs', [
      ['value' => 'events'],
      ['value' => 'offices'],
    ]);
    $group->save();

    $inactive_tabs = [
      'pages',
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable pages.
    $group->set('field_pages_page', 'https://www.example.com/pages');
    $group->save();

    $inactive_tabs = [
      'pages',
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable checkbox.
    $group->set('field_enabled_tabs', [
      ['value' => 'events'],
      ['value' => 'offices'],
      ['value' => 'pages'],
    ]);
    $group->save();

    $inactive_tabs = [
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable reports.
    $group->set('field_documents_page', 'https://www.example.com/reports');
    $group->save();

    $inactive_tabs = [
      'reports',
    ];
    $active_tabs = [
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable checkbox.
    $group->set('field_enabled_tabs', [
      ['value' => 'events'],
      ['value' => 'offices'],
      ['value' => 'pages'],
      ['value' => 'documents'],
    ]);
    $group->save();

    $inactive_tabs = [
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable maps.
    $group->set('field_infographics', 'https://www.example.com/maps');
    $group->save();

    $inactive_tabs = [
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable checkbox.
    $group->set('field_enabled_tabs', [
      ['value' => 'events'],
      ['value' => 'offices'],
      ['value' => 'pages'],
      ['value' => 'documents'],
      ['value' => 'maps'],
    ]);
    $group->save();

    $inactive_tabs = [
      'data',
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable datasets.
    $group->set('field_hdx_alternate_source', 'https://www.example.com/datasets');
    $group->save();

    $inactive_tabs = [
      'data',
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable checkbox.
    $group->set('field_enabled_tabs', [
      ['value' => 'events'],
      ['value' => 'offices'],
      ['value' => 'pages'],
      ['value' => 'documents'],
      ['value' => 'maps'],
      ['value' => 'data'],
    ]);
    $group->save();

    $inactive_tabs = [
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'data',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable assessments.
    $group->set('field_assessments_page', 'https://www.example.com/assessments');
    $group->save();

    $inactive_tabs = [
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'data',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable checkbox.
    $group->set('field_enabled_tabs', [
      ['value' => 'events'],
      ['value' => 'offices'],
      ['value' => 'pages'],
      ['value' => 'documents'],
      ['value' => 'maps'],
      ['value' => 'data'],
      ['value' => 'assessments'],
    ]);
    $group->save();

    $inactive_tabs = [
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Disable all tabs.
    $group->set('field_enabled_tabs', []);
    $group->save();

    $inactive_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'data',
      'assessments',
    ];
    $active_tabs = [
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);
  }

  /**
   * Helper to check access to tabs.
   */
  protected function checkTabAccess($group, $active_tabs, $inactive_tabs) {
    $user = User::getAnonymousUser();

    $this->drupalGet($group->toUrl());

    foreach ($inactive_tabs as $tab) {
      $url = Url::fromRoute('hr_paragraphs.operation.' . $tab, [
        'group' => $group->id(),
      ]);

      $this->assertSession()->linkByHrefNotExists($url->toString());
      $this->assertFalse($url->access($user));
    }

    foreach ($active_tabs as $tab) {
      $url = Url::fromRoute('hr_paragraphs.operation.' . $tab, [
        'group' => $group->id(),
      ]);

      $this->assertSession()->linkByHrefExists($url->toString());
      $this->assertTrue($url->access($user));
    }
  }

}
