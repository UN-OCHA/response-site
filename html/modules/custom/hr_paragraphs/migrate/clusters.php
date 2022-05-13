<?php

// phpcs:ignoreFile

use Drupal\group\Entity\Group;

include_once __DIR__ . '/common.php';

function create_clusters() {
  $handle = fopen(__DIR__ . '/clusters.csv', 'r');

  // First line is header.
  $header = fgetcsv($handle, 0, ',', '"');
  $header_lowercase = array_map('strtolower', $header);

  foreach ($header_lowercase as $index => $field_name) {
    $header_lowercase[$index] = trim($field_name);
  }

  $row_counter = 0;
  while ($row = fgetcsv($handle, 0, ',', '"')) {
    $data = [];
    for ($i = 0; $i < count($row); $i++) {
      $data[$header_lowercase[$i]] = trim($row[$i]);
    }

    if ($data['operation active'] != 'active') {
      continue;
    }

    if ($data['published'] != 1) {
      continue;
    }

    if ($data['operation published'] != 1) {
      continue;
    }

    $row_counter++;
    print "{$row_counter}. Processing {$data['name']}\n";

    // Load operation.
    $operation = Group::load($data['operation id']);
    if (!$operation) {
      print "{$row_counter}. Operation {$data['operation id']} not found\n";
      continue;
    }

    // Delete group if it exists.
    if ($group = Group::load($data['id'])) {
      $group->delete();
    }

    $group = Group::create([
      'id' => $data['id'],
      'type' => 'cluster',
      'label' => $data['name'],
    ]);

    // Add ReliefWeb tabs.
    $group->set('field_reliefweb_assessments', $operation->field_reliefweb_assessments->first()->getUrl()->getUri() . '&search=' . htmlentities($data['name']));
    $group->set('field_maps_infographics_link', $operation->field_maps_infographics_link->first()->getUrl()->getUri() . '&search=' . htmlentities($data['name']));
    $group->set('field_reliefweb_documents', $operation->field_reliefweb_documents->first()->getUrl()->getUri() . '&search=' . htmlentities($data['name']));

    $group->set('field_enabled_tabs', [
      ['value' => 'documents'],
      ['value' => 'maps'],
      ['value' => 'assessments'],
    ]);

    // Use sidebar from operation.
    $group->set('field_sidebar_from_operation', TRUE);
    $group->setPublished()->save();

    // Fetch and add panes.
    add_panes_to_entity($group);

    // Add cluster to operation.
    $operation = Group::load($data['operation id']);
    if ($operation) {
      $operation->addContent($group, 'subgroup:' . $group->bundle());
    }
  }

  fclose($handle);
}

create_clusters();
