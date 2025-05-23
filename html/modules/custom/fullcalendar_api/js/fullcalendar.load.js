/**
 * @file
 * Loads the full calendar.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.fullCalendarApi = {
    attach: function (context, drupalSettings) {

      if ($.type(drupalSettings.fullcalendar_api) === 'undefined' || $.type(drupalSettings.fullcalendar_api.instances) === 'undefined') {
        return false;
      }
      var id;
      for (id in drupalSettings.fullcalendar_api.instances) {
        if (drupalSettings.fullcalendar_api.instances.hasOwnProperty(id)) {
          _fullCalendarApiInit(id, drupalSettings.fullcalendar_api.instances[id], context, drupalSettings);
        }
      }

    }
  };

  /**
   * Initialize the FullCalendar instance.
   */
  function _fullCalendarApiInit(id, calendarSettings, context, drupalSettings) {
    var calendar = $('#' + id, context);

    // Merge in event callbacks.
    $.extend(calendarSettings, {
      eventDrop: function (event, delta, revertFunc) {

        // Create a simple object to send to ajax function.
        var dataObj = {
          id: event.id,
          title: event.title,
          entityType: event.entityType ? event.entityType : null,
          bundle: event.bundle ? event.bundle : null,
          allDay: event.allDay,
          dateField: event.dateField,
          startTime: event.start.unix(),
          endTime: event.end ? event.end.unix() : null
        };

        if (!confirm("Are you sure about this change?")) {
          revertFunc();
        } else {
          // Save the entity via AJAX.
          $.ajax({
            method: 'POST',
            url: '//' + window.location.hostname + drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'fullcalendar-api/ajax/update/drop/' + event.id,
            data: dataObj
          }).done(function (response) {
            // @todo return success?
            if (response === 'failure') {
              revertFunc();
            }
          });
        }

      }
    });

    // Use the hash parameters, if they exist. Hash is of the form:
    //   <viewName>/<ISO-date>
    //   Ex. month/2015-06
    var origHash = window.location.hash;
    if (origHash.length > 1) {
      var params = origHash.substring(1).split('/');
      // @todo validate
      $.extend(calendarSettings, {
        defaultView: params[0],
        defaultDate: params[1]
      });
    }

    // Run any custom actions before attaching the calendar.
    $(document, context).trigger('fullCalendarApiCalendar.preprocess', [calendar, calendarSettings]);

    calendar.fullCalendar(calendarSettings);

  }
})(jQuery);
