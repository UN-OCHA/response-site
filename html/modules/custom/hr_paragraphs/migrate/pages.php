<?php

// phpcs:ignoreFile

use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

include_once __DIR__ . '/common.php';

function create_pages() {
  $handle = fopen(__DIR__ . '/pages.csv', 'r');

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

    if ($data['type'] != 'hr_page') {
      continue;
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
    if ($node = Node::load($data['id'])) {
      $node->delete();
    }

    $node = Node::create([
      'nid' => $data['id'],
      'type' => 'page',
      'title' => $data['name'],
    ]);

    $node->setPublished()->save();

    // Fetch and add panes.
    add_panes_to_entity($group);

    // Add cluster to operation.
    $operation = Group::load($data['operation id']);
    if ($operation) {
      $operation->addContent($node, 'group_node:' . $node->bundle());
    }
  }

  fclose($handle);
}

create_pages();
