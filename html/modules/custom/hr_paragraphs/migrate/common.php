<?php

// phpcs:ignoreFile

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

function fetch_panes_from_node($nid) {
  $url = 'http://hrinfo.docksal.site/node/' . $nid . '/panelist';

  $options = array(
    'headers' => array(
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
    ),
  );

  $response = \Drupal::httpClient()->get($url, $options);

  if ($response->getStatusCode() == 200) {
    return json_decode($response->getBody() . '', TRUE);
  }

  return FALSE;
}

function fetch_rw_document_url($hrinfo_url) {
  /** @var \Drupal\hr_paragraphs\Controller\ReliefwebController */
  $reliefweb_controller = \Drupal::service('hr_paragraphs.reliefweb_controller');
  $parameters = $reliefweb_controller->buildReliefwebParameters(0, 1, []);

  // Remove facets.
  unset($parameters['facets']);
  $parameters['filter']['conditions'][] = [
    'field' => 'origin',
    'value' => $hrinfo_url,
  ];


  $results = $reliefweb_controller->executeReliefwebQuery($parameters);
  if (empty($results['data'])) {
    return FALSE;
  }

  $docs = $reliefweb_controller->buildReliefwebObjects($results);
  $doc = reset($docs);

  return $doc['url'];
}

function fix_inline_images_and_urls($html) {
  if (empty($html)) {
    return $html;
  }

  $doc = new DOMDocument();
  $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);

  $tags = $doc->getElementsByTagName('img');
  foreach ($tags as $tag) {
    $src = $tag->getAttribute('src');
    $image = file_get_contents($src);

    /** @var \Drupal\file\Entity\File $file */
    $file = file_save_data($image);

    $tag->setAttribute('src', $file->createFileUrl());
  }

  $tags = $doc->getElementsByTagName('a');
  foreach ($tags as $tag) {
    $href = $tag->getAttribute('href');

    $href = str_replace('https://www.humanitarianresponse.info/en/operations/', '/', $href);
    $href = str_replace('https://www.humanitarianresponse.info/fr/operations/', '/', $href);
    $href = str_replace('https://www.humanitarianresponse.info/ru/operations/', '/', $href);
    $href = str_replace('https://www.humanitarianresponse.info/es/operations/', '/', $href);
    $href = str_replace('https://www.humanitarianresponse.info/operations/', '/', $href);
    $href = str_replace('https://www.humanitarianresponse.info/en/', '/', $href);
    $href = str_replace('https://www.humanitarianresponse.info/fr/', '/', $href);
    $href = str_replace('https://www.humanitarianresponse.info/ru/', '/', $href);
    $href = str_replace('https://www.humanitarianresponse.info/es/', '/', $href);
    $href = str_replace('https://www.humanitarianresponse.info/', '/', $href);

    $tag->setAttribute('href', $href);
  }

  return $doc->saveHTML();
}

function add_panes_to_entity(&$entity) {
  $panes = fetch_panes_from_node($entity->id());
  if (!$panes) {
    return;
  }

  if (!$panes['panes']) {
    return;
  }

  $changed = FALSE;
  foreach ($panes['panes'] as $pane) {
    switch ($pane['type']) {
      case 'hr_layout_rss_feeds':
        $paragraph = Paragraph::create([
          'type' => 'rss_feed',
        ]);
        if (isset($pane['title']) && !empty($pane['title'])) {
          $paragraph->set('field_title', $pane['title']);
        }
        $paragraph->set('field_rss_link', [
          'uri' => $pane['rss'],
        ]);

        $paragraph->isNew();
        $paragraph->save();

        $entity->field_paragraphs[] = $paragraph;
        $changed = TRUE;
        break;

      case 'fieldable_panels_pane':
      case 'custom':
      case 'node_body':
        if (empty($pane['title']) && empty($pane['body'])) {
          break;
        }

        // Skip tokenized content.
        if (strpos($pane['body'], '[[{') !== FALSE) {
          break;
        }

        $paragraph = Paragraph::create([
          'type' => 'text_block',
        ]);

        if (isset($pane['title']) && !empty($pane['title'])) {
          $paragraph->set('field_title', $pane['title']);
        }

        $paragraph->set('field_text', fix_inline_images_and_urls($pane['body']));
        $paragraph->field_text->format = 'basic_html';

        $paragraph->isNew();

        $entity->field_paragraphs[] = $paragraph;
        $changed = TRUE;
        break;

      case 'hr_documents':
      case 'hr_infographics':
      case 'hr_infographics_key_infographics':
      case 'hr_documents_key_documents':
        foreach ($pane['target_ids'] as $target) {
          $reliefweb_url = fetch_rw_document_url('https://www.humanitarianresponse.info/en/' . $target);
          if (empty($reliefweb_url)) {
            continue;
          }

          $paragraph = Paragraph::create([
            'type' => 'reliefweb_document',
          ]);
          if (isset($pane['title']) && !empty($pane['title'])) {
            $paragraph->set('field_title', $pane['title']);
          }

          $paragraph->set('field_reliefweb_url', $reliefweb_url);

          $paragraph->isNew();

          $entity->field_paragraphs[] = $paragraph;
          $changed = TRUE;
        }
        break;

      case 'hr_layout_standard':
        $keys = [
          'gid',
          'hno',
          'monr',
          'srp',
          'opr',
          'orp',
        ];
        foreach ($keys as $key) {
          if (!$pane[$key]) {
            continue;
          }

          if (!isset($pane[$key]['entity_bundle']) || $pane[$key]['entity_bundle'] == 'hr_operation') {
            continue;
          }

          $reliefweb_url = fetch_rw_document_url('https://www.humanitarianresponse.info/en/' . $pane[$key]['target_id']);
          if (empty($reliefweb_url)) {
            continue;
          }

          $paragraph = Paragraph::create([
            'type' => 'reliefweb_document',
          ]);
          if (isset($pane['title']) && !empty($pane['title'])) {
            $paragraph->set('field_title', $pane['title']);
          }

          $paragraph->set('field_reliefweb_url', $reliefweb_url);

          $paragraph->isNew();

          $entity->field_paragraphs[] = $paragraph;
          $changed = TRUE;
        }
        break;

      case 'fts_visualization':
        // Skip it.
        break;

      default:
        print("unsupported type: {$pane['type']}\n");
    }
  }

  if ($changed) {
    $entity->save();
  }
}
