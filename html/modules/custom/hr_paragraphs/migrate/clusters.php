<?php

// phpcs:ignoreFile

use Drupal\group\Entity\Group;

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

    // Delete group if it exists.
    if ($group = Group::load($data['id'])) {
      $group->delete();
    }

    $group = Group::create([
      'id' => $data['id'],
      'type' => 'cluster',
      'label' => $data['name'],
    ]);

    $group->set('field_sidebar_from_operation', TRUE);
    $group->setPublished()->save();

    // Add cluster to operation.
    $operation = Group::load($data['operation id']);
    if ($operation) {
      $operation->addContent($group, 'subgroup:' . $group->bundle());
    }
  }

  fclose($handle);
}

create_clusters();
