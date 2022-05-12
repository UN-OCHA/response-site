<?php

// phpcs:ignoreFile

use Drupal\group\Entity\Group;
use Drupal\paragraphs\Entity\Paragraph;

function get_country_id_from_iso3($iso3) {
  $iso_to_rw = [
    'AFG' => 13,
    'ALA' => 14,
    'ALB' => 15,
    'DZA' => 16,
    'ASM' => 17,
    'AND' => 18,
    'AGO' => 19,
    'AIA' => 20,
    'ATG' => 21,
    'ARG' => 22,
    'ARM' => 23,
    'ABW' => 24,
    'AUS' => 25,
    'AUT' => 26,
    'AZE' => 27,
    'AZO' => 28,
    'BHS' => 29,
    'BHR' => 30,
    'BGD' => 31,
    'BRB' => 32,
    'BLR' => 33,
    'BEL' => 34,
    'BLZ' => 35,
    'BEN' => 36,
    'BMU' => 37,
    'BTN' => 38,
    'BOL' => 39,
    'BIH' => 40,
    'BWA' => 41,
    'BRA' => 42,
    'VGB' => 43,
    'BRN' => 44,
    'BGR' => 45,
    'BFA' => 46,
    'BDI' => 47,
    'KHM' => 48,
    'CMR' => 49,
    'CAN' => 50,
    'CAI' => 51,
    'CPV' => 52,
    'CYM' => 53,
    'CAF' => 54,
    'TCD' => 55,
    'CHI' => 56,
    'CHL' => 57,
    'CHN' => 58,
    'HKG' => 59,
    'MAC' => 60,
    'TWN' => 61,
    'CXR' => 62,
    'CCK' => 63,
    'COL' => 64,
    'COM' => 65,
    'COG' => 66,
    'COK' => 67,
    'CRI' => 68,
    'CIV' => 69,
    'HRV' => 70,
    'CUB' => 71,
    'CYP' => 72,
    'CZE' => 73,
    'PRK' => 74,
    'COD' => 75,
    'DNK' => 76,
    'DJI' => 77,
    'DMA' => 78,
    'DOM' => 79,
    'EAI' => 80,
    'ECU' => 81,
    'EGY' => 82,
    'SLV' => 83,
    'GNQ' => 84,
    'ERI' => 85,
    'EST' => 86,
    'ETH' => 87,
    'FLK' => 88,
    'FRO' => 89,
    'FJI' => 90,
    'FIN' => 91,
    'FRA' => 92,
    'GUF' => 93,
    'PYF' => 94,
    'GAB' => 96,
    'GLI' => 97,
    'GMB' => 98,
    'GEO' => 100,
    'DEU' => 101,
    'GHA' => 102,
    'GIB' => 103,
    'GRC' => 104,
    'GRL' => 105,
    'GRD' => 106,
    'GLP' => 107,
    'GUM' => 108,
    'GTM' => 109,
    'GIN' => 110,
    'GNB' => 111,
    'GUY' => 112,
    'HTI' => 113,
    'HMD' => 114,
    'VAT' => 115,
    'HND' => 116,
    'HUN' => 117,
    'ISL' => 118,
    'IND' => 119,
    'IDN' => 120,
    'IRN' => 121,
    'IRQ' => 122,
    'IRL' => 123,
    'ILM' => 124,
    'ISR' => 125,
    'ITA' => 126,
    'JAM' => 127,
    'JPN' => 128,
    'JOR' => 129,
    'KAZ' => 130,
    'KEN' => 131,
    'KIR' => 132,
    'KWT' => 133,
    'KGZ' => 134,
    'LAO' => 135,
    'LVA' => 136,
    'LBN' => 137,
    'LSO' => 138,
    'LBR' => 139,
    'LBY' => 140,
    'LIE' => 141,
    'LTU' => 142,
    'LUX' => 143,
    'MDG' => 144,
    'MDR' => 145,
    'MWI' => 146,
    'MYS' => 147,
    'MDV' => 148,
    'MLI' => 149,
    'MLT' => 150,
    'MHL' => 151,
    'MTQ' => 152,
    'MRT' => 153,
    'MUS' => 154,
    'MYT' => 155,
    'MEX' => 156,
    'FSM' => 157,
    'MDA' => 158,
    'MCO' => 159,
    'MNG' => 160,
    'MNE' => 161,
    'MSR' => 162,
    'MAR' => 163,
    'MOZ' => 164,
    'MMR' => 165,
    'NAM' => 166,
    'NRU' => 167,
    'NPL' => 168,
    'NLD' => 169,
    'ANT' => 170,
    'NCL' => 171,
    'NZL' => 172,
    'NIC' => 173,
    'NER' => 174,
    'NGA' => 175,
    'NIU' => 176,
    'NFK' => 177,
    'MNP' => 178,
    'NOR' => 179,
    'PSE' => 180,
    'OMN' => 181,
    'PAK' => 182,
    'PLW' => 183,
    'PAN' => 184,
    'PNG' => 185,
    'PRY' => 186,
    'PER' => 187,
    'PHL' => 188,
    'PCN' => 189,
    'POL' => 190,
    'PRT' => 191,
    'PRI' => 192,
    'QAT' => 193,
    'KOR' => 194,
    'REU' => 195,
    'ROU' => 196,
    'RUS' => 197,
    'RWA' => 198,
    'SHN' => 199,
    'KNA' => 200,
    'LCA' => 201,
    'SPM' => 202,
    'VCT' => 203,
    'WSM' => 204,
    'SMR' => 205,
    'STP' => 206,
    'SAU' => 207,
    'SEN' => 208,
    'SRB' => 209,
    'SYC' => 210,
    'SLE' => 211,
    'SGP' => 212,
    'SVK' => 213,
    'SVN' => 214,
    'SLB' => 215,
    'SOM' => 216,
    'ZAF' => 217,
    'ESP' => 218,
    'LKA' => 219,
    'SDN' => 220,
    'SUR' => 221,
    'SJM' => 222,
    'SWZ' => 223,
    'SWE' => 224,
    'CHE' => 225,
    'SYR' => 226,
    'TJK' => 227,
    'THA' => 228,
    'MKD' => 229,
    'TLS' => 230,
    'TGO' => 231,
    'TKL' => 232,
    'TON' => 233,
    'TTO' => 234,
    'TUN' => 235,
    'TUR' => 236,
    'TKM' => 237,
    'TCA' => 238,
    'TUV' => 239,
    'UGA' => 240,
    'UKR' => 241,
    'ARE' => 242,
    'GBR' => 243,
    'TZA' => 244,
    'USA' => 245,
    'VIR' => 246,
    'URY' => 247,
    'UZB' => 248,
    'VUT' => 249,
    'VEN' => 250,
    'VNM' => 251,
    'WLF' => 252,
    'ESH' => 253,
    'WLD' => 254,
    'YEM' => 255,
    'ZMB' => 256,
    'ZWE' => 257,
    'SSD' => 8657,
    'BLM' => 14890,
    'MAF' => 14891,
    'SXM' => 14892,
    'CUW' => 14893,
    'BES' => 14894,
  ];

  if (isset($iso_to_rw[strtoupper($iso3)])) {
    return $iso_to_rw[strtoupper($iso3)];
  }

  return '';
}

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
    $row_counter++;

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

    print "Processing {$data['name']}\n";

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
