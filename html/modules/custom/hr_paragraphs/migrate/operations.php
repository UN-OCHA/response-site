<?php

// phpcs:ignoreFile

use Drupal\group\Entity\Group;
use Drupal\paragraphs\Entity\Paragraph;

include_once __DIR__ . '/common.php';

function create_operations() {
  $handle = fopen(__DIR__ . '/operations.csv', 'r');

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

    if ($data['active'] != 'active') {
      continue;
    }

    if ($data['published'] != 1) {
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
      'type' => 'operation',
      'label' => $data['name'],
    ]);

    // Add sidebar.
    $op_sidebar_content = Paragraph::create([
      'type' => 'group_pages',
    ]);
    $op_sidebar_content->isNew();
    $op_sidebar_content->save();

    $op_sidebar_children = Paragraph::create([
      'type' => 'child_groups',
    ]);
    $op_sidebar_children->isNew();
    $op_sidebar_children->save();

    $group->set('field_sidebar_menu', [
      $op_sidebar_content,
      $op_sidebar_children,
    ]);

    // Add ReliefWeb tabs and rivers.
    $rw_country_id = get_country_id_from_iso3($data['iso3']);
    if (!empty($rw_country_id)) {
      $group->set('field_reliefweb_assessments', 'https://reliefweb.int/updates?advanced-search=%28PC' . $rw_country_id . '%29_%28F5%29');
      $group->set('field_maps_infographics_link', 'https://reliefweb.int/updates?view=maps&advanced-search=%28PC' . $rw_country_id . '%29');
      $group->set('field_reliefweb_documents', 'https://reliefweb.int/updates?advanced-search=%28PC' . $rw_country_id . '%29&view=reports');

      $paragraph_assessments = Paragraph::create([
        'type' => 'reliefweb_river',
        'field_title' => 'ReliefWeb Assessments',
      ]);
      $paragraph_assessments->set('field_reliefweb_url', 'https://reliefweb.int/updates?advanced-search=%28PC' . $rw_country_id . '%29_%28F5%29');
      $paragraph_assessments->isNew();
      $paragraph_assessments->save();

      $paragraph_maps = Paragraph::create([
        'type' => 'reliefweb_river',
        'field_title' => 'ReliefWeb Maps / Infographics',
      ]);
      $paragraph_maps->set('field_reliefweb_url', 'https://reliefweb.int/updates?view=maps&advanced-search=%28PC' . $rw_country_id . '%29');
      $paragraph_maps->isNew();
      $paragraph_maps->save();

      $paragraph_reports = Paragraph::create([
        'type' => 'reliefweb_river',
        'field_title' => 'ReliefWeb Reports',
      ]);
      $paragraph_reports->set('field_reliefweb_url', 'https://reliefweb.int/updates?advanced-search=%28PC' . $rw_country_id . '%29&view=reports');
      $paragraph_reports->isNew();
      $paragraph_reports->save();

      $group->set('field_paragraphs', [
        $paragraph_reports,
        $paragraph_maps,
        $paragraph_assessments,
      ]);
    }

    // Add HDX tab.
    $group->set('field_hdx_dataset_link', 'https://data.humdata.org/group/' . strtolower($data['iso3']));

    $group->setPublished()->save();
  }

  fclose($handle);
}

create_operations();
