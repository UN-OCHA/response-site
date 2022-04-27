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

    $inactive_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'datasets',
      'assessments',
    ];
    $active_tabs = [];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);
  }

  /**
   * Test operation tabs.
   */
  public function testOperationTabs() {
    $group_title = 'Operation with tabs';

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
    $active_tabs = [];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable events.
    $group->set('field_ical_url', 'https://www.example.com/events');
    $group->save();

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
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable contacts.
    $group->set('field_offices_page', 'https://www.example.com/contacts');
    $group->save();

    $inactive_tabs = [
      'pages',
      'reports',
      'maps',
      'datasets',
      'assessments',
    ];
    $active_tabs = [
      'contacts',
      'events',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable pages.
    $group->set('field_pages_page', 'https://www.example.com/pages');
    $group->save();

    $inactive_tabs = [
      'reports',
      'maps',
      'datasets',
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
      'maps',
      'datasets',
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
      'datasets',
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
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'datasets',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable assessments.
    $group->set('field_assessments_page', 'https://www.example.com/assessments');
    $group->save();

    $inactive_tabs = [
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'datasets',
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
      'datasets',
      'assessments',
    ];
    $active_tabs = [
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);
  }

  /**
   * Test operation tabs.
   */
  public function testOperationTabs2() {
    $group_title = 'Operation with tabs 2';

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
    $active_tabs = [];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable events.
    $group->set('field_ical_url', 'https://www.example.com/events');
    $group->save();

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
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable contacts.
    $group->set('field_offices_page', 'https://www.example.com/contacts');
    $group->save();

    $inactive_tabs = [
      'pages',
      'reports',
      'maps',
      'datasets',
      'assessments',
    ];
    $active_tabs = [
      'contacts',
      'events',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable pages.
    $group->set('field_pages_page', 'https://www.example.com/pages');
    $group->save();

    $inactive_tabs = [
      'reports',
      'maps',
      'datasets',
      'assessments',
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable reports.
    $group->set('field_reliefweb_documents', 'https://reliefweb.int/updates?view=reports');
    $group->save();

    $inactive_tabs = [
      'maps',
      'datasets',
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
    $group->set('field_maps_infographics_link', 'https://reliefweb.int/updates?view=maps');
    $group->save();

    $inactive_tabs = [
      'datasets',
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
    $group->set('field_hdx_dataset_link', 'https://data.humdata.org/dataset');
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
      'datasets',
    ];
    $this->checkTabAccess($group, $active_tabs, $inactive_tabs);

    // Enable assessments.
    $group->set('field_reliefweb_assessments', 'https://reliefweb.int/updates?advanced-search=%28F5%29&view=reports');
    $group->save();

    $inactive_tabs = [
    ];
    $active_tabs = [
      'events',
      'contacts',
      'pages',
      'reports',
      'maps',
      'datasets',
      'assessments',
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
      $this->assertSession()->linkByHrefNotExists($group->toUrl()->toString() . '/' . $tab);

      $url = Url::fromRoute('hr_paragraphs.operation.' . $tab, [
        'group' => $group->id(),
      ]);
      $this->assertFalse($url->access($user));
    }

    foreach ($active_tabs as $tab) {
      $this->assertSession()->linkByHrefExists($group->toUrl()->toString() . '/' . $tab);

      $url = Url::fromRoute('hr_paragraphs.operation.' . $tab, [
        'group' => $group->id(),
      ]);
      $this->assertTrue($url->access($user));
    }
  }

}
