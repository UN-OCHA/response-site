<?php

// phpcs:ignoreFile

use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

include_once __DIR__ . '/common.php';

function create_pages() {
  $handle = load_tsv_file('pages.tsv');

  // Headers.
  $header_lowercase = [
    "id",
    "name",
    "published",
    "type",
    "changed",
    "author",
    "email",
    "url",
    "operation",
    "operation published",
    "operation active",
    "operation url",
    "operation id",
    "operation type",
  ];

  $row_counter = 0;
  while ($row = fgetcsv($handle, 0, "\t")) {
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
    print "{$row_counter}. Processing {$data['name']} ({$data['id']})\n";

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
    add_panes_to_entity($node);

    // Add cluster to operation.
    $operation = Group::load($data['operation id']);
    if ($operation) {
      $operation->addContent($node, 'group_node:' . $node->bundle());
    }
  }

  fclose($handle);
}

create_pages();
