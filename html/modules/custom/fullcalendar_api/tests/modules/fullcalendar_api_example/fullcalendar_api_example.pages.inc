<?php

/**
 * @file
 * Page callbacks for FullCalendar API Examples module.
 */

/**
 * Page callback for /fullcalendar/calendar.
 */
function fullcalendar_api_example_calendar_page() {

  // Array of FullCalendar settings.
  $settings = [
    'header' => [
      'left' => 'prev,next today',
      'center' => 'title',
      'right' => 'month,agendaWeek,agendaDay',
    ],
    'defaultDate' => '2015-02-12',
    'editable' => TRUE,
    // Allow "more" link when too many events.
    'eventLimit' => TRUE,
    'events' => [
        [
          'title' => 'All Day Event',
          'start' => '2015-02-01',
        ],
        [
          'title' => 'Long Event',
          'start' => '2015-02-07',
          'end' => '2015-02-10',
        ],
        [
          'id' => 999,
          'title' => 'Repeating Event',
          'start' => '2015-02-09T16:00:00',
        ],
        [
          'id' => 999,
          'title' => 'Repeating Event',
          'start' => '2015-02-16T16:00:00',
        ],
        [
          'title' => 'Conference',
          'start' => '2015-02-11',
          'end' => '2015-02-13',
        ],
        [
          'title' => 'Meeting',
          'start' => '2015-02-12T10:30:00',
          'end' => '2015-02-12T12:30:00',
        ],
        [
          'title' => 'Lunch',
          'start' => '2015-02-12T12:00:00',
        ],
        [
          'title' => 'Meeting',
          'start' => '2015-02-12T14:30:00',
        ],
        [
          'title' => 'Happy Hour',
          'start' => '2015-02-12T17:30:00',
        ],
        [
          'title' => 'Dinner',
          'start' => '2015-02-12T20:00:00',
        ],
        [
          'title' => 'Birthday Party',
          'start' => '2015-02-13T07:00:00',
        ],
        [
          'title' => 'Click for Google',
          'url' => 'http://google.com/',
          'start' => '2015-02-28',
        ],
    ],
  ];

  return _theme('fullcalendar_calendar', [
    'calendar_id' => 'fullcalendar',
    'calendar_settings' => $settings,
  ]);
}

/**
 * Page callback for /fullcalendar/datasource.
 */
function fullcalendar_api_example_datasource_page() {
  // Array of FullCalendar settings.
  $settings = [
    'header' => [
      'left' => 'prev,next today',
      'center' => 'title',
      'right' => 'month,agendaWeek,agendaDay',
    ],
    'defaultDate' => '2015-02-12',
    'editable' => TRUE,
  ];
  // @see fullcalendar_api_example.ajax.inc.
  $datasource_uri = 'fullcalendar/json-data';
  return _theme('fullcalendar_calendar_datasource', [
    'calendar_id' => 'fullcalendar',
    'calendar_settings' => $settings,
    'datasource_uri' => $datasource_uri,
  ]);
}

/**
 * Page callback for /fullcalendar/entity.
 */
function fullcalendar_api_example_entity_page() {
  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'article');
  $result = $query->execute();
  if (isset($result['node'])) {
    $entities = \Drupal::service('entity_type.manager')->getStorage('node');

    // Array of custom calendar settings. These override the defaults set
    // in the theme function.
    $custom_settings = [
      'header' => [
        'left' => 'month,agendaWeek',
        'right' => 'prev title next',
      ],
      'weekends' => TRUE,
      'firstDay' => 1,
      'defaultDate' => date('Y-m-d'),
    ];

    return _theme('fullcalendar_calendar_entity', [
      'entities' => $entities,
      'date_field_map' => ['article' => 'field_date'],
      'calendar_id' => 'fullcalendar-entity',
      'calendar_settings' => $custom_settings,
    ]);
  }

  $markup = '<p>Calendar not available.</p>';
  $markup .= '<ol><li>Add a required field called <em>field_date</em>';
  $markup .= 'to the Article content type.</li> <li>Add some content.</li>';
  $markup .= '<li>Visit this page again.</li>';

  return [
    '#markup' => $markup,
  ];
}

/**
 * Page callback for /fullcalendar/callbacks.
 */
function fullcalendar_api_example_callbacks_page() {
  $build = [];

  // Array of FullCalendar settings.
  $settings = [
    'header' => [
      'left' => 'prev,next today',
      'center' => 'title',
      'right' => 'month,agendaWeek,agendaDay',
    ],
    'defaultDate' => '2015-02-12',
    'editable' => TRUE,
    // Allow "more" link when too many events.
    'eventLimit' => TRUE,
    'events' => [
      [
        'title' => 'All Day Event',
        'start' => '2015-02-01',
      ],
      [
        'title' => 'Long Event',
        'start' => '2015-02-07',
        'end' => '2015-02-10',
      ],
      [
        'title' => 'Conference',
        'start' => '2015-02-11',
        'end' => '2015-02-13',
      ],
    ],
  ];

  // Add our custom js file which will implement FullCalendar's callbacks.
  $build['calendar'] = _theme('fullcalendar_calendar', [
    'calendar_id' => 'fullcalendar',
    'calendar_settings' => $settings,
  ]);
  return $build;
}
