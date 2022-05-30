<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\ExistingSite;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests RSS feed.
 */
class HrParagraphsOperationTabTitlesTest extends ExistingSiteBase {

  /**
   * Test empty operation.
   */
  public function testOperationTabTitles() {
    $site_name = \Drupal::config('system.site')->get('name');
    $group_title = 'My operation';

    $contacts = Node::create([
      'type' => 'page',
      'title' => 'My contacts',
    ]);
    $contacts->setPublished()->save();

    $pages = Node::create([
      'type' => 'page',
      'title' => 'My pages',
    ]);
    $pages->setPublished()->save();

    $group = Group::create([
      'type' => 'operation',
      'label' => $group_title,
    ]);

    $group->set('field_ical_url', 'https://www.example.com/events');
    $group->set('field_offices_page', 'internal:' . $contacts->toUrl()->toString());
    $group->set('field_pages_page', 'internal:' . $pages->toUrl()->toString());
    $group->set('field_reliefweb_documents', 'https://reliefweb.int/updates?view=reports');
    $group->set('field_maps_infographics_link', 'https://reliefweb.int/updates?view=maps');
    $group->set('field_hdx_dataset_link', 'https://data.humdata.org/dataset');
    $group->set('field_reliefweb_assessments', 'https://reliefweb.int/updates?advanced-search=%28F5%29&view=reports');
    $group->setPublished()->save();

    // Add pages to group.
    $group->addContent($contacts, 'group_node:' . $contacts->bundle());
    $group->addContent($pages, 'group_node:' . $pages->bundle());

    $this->drupalGet($group->toUrl());
    $this->assertSession()->titleEquals($group_title . ' | ' . $site_name);
    $this->assertSession()->elementTextEquals('css', 'h1.cd-page-title', $group_title);

    $tabs = [
      'events' => 'Events',
      'contacts' => 'Contacts',
      'pages' => 'Pages',
      'reports' => 'Reports',
      'maps' => 'Maps / Infographics',
      'data' => 'Data',
      'assessments' => 'Assessments',
    ];

    foreach ($tabs as $tab => $title) {
      $url = Url::fromRoute('hr_paragraphs.operation.' . $tab, [
        'group' => $group->id(),
      ]);

      $this->drupalGet($url);
      $this->assertSession()->titleEquals($group_title . ' - ' . $title . ' | ' . $site_name);
      $this->assertSession()->elementTextEquals('css', 'h1.cd-page-title', $title);
    }

    $tabs = [
      'contacts' => 'My contacts',
      'pages' => 'My pages',
    ];

    foreach ($tabs as $tab => $title) {
      $url = Url::fromRoute('hr_paragraphs.operation.' . $tab, [
        'group' => $group->id(),
      ]);

      $this->drupalGet($url);
      $this->assertSession()->pageTextNotContains($title);
    }
  }

}
