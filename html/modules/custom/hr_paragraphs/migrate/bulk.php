<?php

// phpcs:ignoreFile

use Drupal\group\Entity\Group;

include_once __DIR__ . '/common.php';

$group_ids = [
//  25,
//  27,
//  28,
//  35,
//  37,
  38,
  39,
  40,
  41,
  43,
  46,
  49,
//  50,
//  51,
//  53,
//  54,
//  57,
//  59,
//  60,
//  62,
//  65,
//  66,
//  68,
//  69,
//  70,
];

$group_ids = [152451];
foreach ($group_ids as $group_id) {
  $group = Group::load($group_id);
  print("Processing: {$group->label()} ({$group_id})\n");
  add_panes_to_entity($group);
}
