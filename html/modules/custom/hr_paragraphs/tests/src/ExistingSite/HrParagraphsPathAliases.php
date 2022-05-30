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
class HrParagraphsPathAliases extends ExistingSiteBase {

  /**
   * Test cluster and node aliases.
   */
  public function testPathAliases() {
    $site_name = \Drupal::config('system.site')->get('name');
    $operation_title = 'My operation ' . rand(99999, 9999999);
    $group_title = 'My cluster ' . rand(99999, 9999999);

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

    $operation = Group::create([
      'type' => 'operation',
      'label' => $operation_title,
    ]);
    $operation->setPublished()->save();

    $group = Group::create([
      'type' => 'cluster',
      'label' => $group_title,
    ]);

    $group->set('field_offices_page', 'internal:' . $contacts->toUrl()->toString());
    $group->set('field_pages_page', 'internal:' . $pages->toUrl()->toString());

    // Enable checkbox.
    $group->set('field_enabled_tabs', [
      ['value' => 'offices'],
      ['value' => 'pages'],
    ]);

    $group->setPublished()->save();

    // Add cluster to operation.
    $operation->addContent($group, 'subgroup:' . $group->bundle());

    // Add pages to cluster.
    $group->addContent($contacts, 'group_node:' . $contacts->bundle());
    $group->addContent($pages, 'group_node:' . $pages->bundle());

    // Check URL aliases.
    $operation_url = '/' . strtolower(str_replace(' ', '-', $operation_title));
    $group_url = $operation_url . '/' . strtolower(str_replace(' ', '-', $group_title));
    $contacts_url = $group_url . '/' . strtolower(str_replace(' ', '-', 'My contacts'));

    $this->assertEquals($operation->toUrl()->toString(), $operation_url);
    $this->assertEquals($group->toUrl()->toString(), $group_url);
    $this->assertEquals($contacts->toUrl()->toString(), $contacts_url);

    // Check landing page.
    $this->drupalGet($group->toUrl());
    $this->assertSession()->titleEquals($operation_title . ': ' . $group_title . ' | ' . $site_name);
    $this->assertSession()->elementTextEquals('css', 'h1.cd-page-title', $group_title);

    // Change group title.
    $group_title = 'My cluster ' . rand(99999, 9999999);
    $group->set('label', $group_title);
    $group->save();

    // Check URL aliases, these are not changed.
    $this->assertEquals($operation->toUrl()->toString(), $operation_url);
    $this->assertEquals($group->toUrl()->toString(), $group_url);
    $this->assertEquals($contacts->toUrl()->toString(), $contacts_url);

    // Change node title.
    $contacts->set('title', 'Still my contacts');
    $contacts->save();

    // Check URL aliases, these are not changed.
    $this->assertEquals($operation->toUrl()->toString(), $operation_url);
    $this->assertEquals($group->toUrl()->toString(), $group_url);
    $this->assertEquals($contacts->toUrl()->toString(), $contacts_url);
  }

}
