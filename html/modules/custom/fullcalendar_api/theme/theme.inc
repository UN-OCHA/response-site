<?php

/**
 * @file
 * Theme and template preprocess functions for FullCalendar API module.
 */

use Drupal\Core\Language\Language;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Theme function for fullcalendar_calendar_datasource.
 *
 * Renders a calendar using passed in datasource URL.
 *
 * This datasource should be a JSON feed set up externally.
 *
 * @params
 *   datasource_uri string the path of the JSON data feed
 *   calendar_id string the CSS id of the calendar DOM object
 *   calendar_settings array of settings to override FullCalendar.js defaults
 */
function theme_fullcalendar_calendar_datasource($vars) {
  $datasource_uri = $vars['datasource_uri'];
  $calendar_id = $vars['calendar_id'];
  $settings = $vars['calendar_settings'];

  $full_url = Url::fromUri($datasource_uri, ['absolute' => TRUE]);

  $settings['events'] = $full_url;

  return [
    '#theme' => 'fullcalendar_calendar',
    '#calendar_id' => $calendar_id,
    '#calendar_settings' => $settings,
  ];
}

/**
 * Theme function for fullcalendar_calendar_entity.
 *
 * Renders an array of entities with date fields as calendar items.
 *
 * @params
 *   entities array of event objects
 *   date_field string field name of valid date field
 *   calendar_id string the CSS id of the calendar DOM object
 *   calendar_settings array of settings to override FullCalendar.js defaults
 */
function theme_fullcalendar_calendar_entity($vars) {
  $entities = $vars['entities'];
  $date_field_map = $vars['date_field_map'];
  $calendar_id = $vars['calendar_id'];
  $settings = $vars['calendar_settings'];

  if (!is_array($date_field_map)) {
    throw new \Exception('Invalid parameter: Date field must be an array.');
  }

  foreach ($date_field_map as $date_field) {
    // Check if field is valid date type.
    $entity_type = 'node';
    $date_field_info = FieldStorageConfig::loadByName($entity_type, $date_field);
    $haystack = ['date', 'datetime', 'datestamp', 'ep_datetime'];
    if (
      !$date_field_info ||
      !in_array($date_field_info['type'], $haystack)
    ) {
      throw new \Exception(strtr('Field name %field_name is not a valid date field.', ['%field_name' => $date_field]));
    }
  }

  // Get entity_type -> bundle mappings so we can have mixed entity types
  // in our calendar events.
  $entity_info = \Drupal::service('entity_type.manager')->getDefinitions();
  $entity_bundle_types = [];
  foreach ($entity_info as $entity_type => $data) {
    foreach (array_keys($data['bundles']) as $bundle) {
      $entity_bundle_types[$bundle] = $entity_type;
    }
  }

  foreach ($entities as $entity) {
    // If "bundle" is not defined, use "type".
    if (!isset($entity->bundle)) {
      $entity->bundle = $entity->type;
    }
    $entity_type = $entity_bundle_types[$entity->bundle];
    $date_field = $date_field_map[$entity->bundle];

    $start = NULL;
    $end = NULL;
    // If the date type is not "datestamp" (timestamp), pass the string to
    // DateTime constructor. It will automatically detect the date format and
    // give us a timestamp.
    $date_field_value = $entity->{$date_field}[Language::LANGCODE_NOT_SPECIFIED][0];
    $date_type = $date_field_value['date_type'];
    switch ($date_type) {
      case 'date':
      case 'datetime':
      case 'ep_datetime':
        $dateTime = new \DateTime($date_field_value['value']);
        $start = $dateTime->getTimestamp();
        if (!empty($date_field_value['value2'])) {
          $endDateTime = new \DateTime($date_field_value['value2']);
          $end = $endDateTime->getTimestamp();
        }
        break;

      case 'datestamp':
        // Date value is already a timestamp.
        $start = $date_field_value['value'];
        if (!empty($date_field_value['value2'])) {
          $end = $date_field_value['value2'];
        }
        break;
    }

    [$entity_id] = entity_extract_ids($entity_type, $entity);

    $entity_array = [
      'id' => $entity_id,
      'entityType' => $entity_type,
      'bundle' => $entity->bundle,
      'title' => $entity->title,
      'dateField' => $date_field,
      'start' => date('c', $start),
      'className' => [
        'event-' . $entity_type . '-' . $entity->type,
      ],
    ];
    // If className has been passed in, append.
    if (!empty($entity->className)) {
      if (!is_array($entity->className)) {
        $entity->className = [$entity->className];
      }
      $entity_array['className'] = array_merge($entity_array['className'], $entity->className);
    }
    if (!empty($end)) {
      $entity_array['end'] = date('c', $end);
    }

    $settings['events'][] = $entity_array;
  }

  // At this point validation is complete.
  // Pass the settings data to the theme_fullcalendar_calendar function.
  return [
    '#theme' => 'fullcalendar_calendar',
    '#calendar_id' => $calendar_id,
    '#calendar_settings' => $settings,
  ];

}
