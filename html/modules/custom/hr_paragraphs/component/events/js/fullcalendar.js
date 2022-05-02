/**
 * @file
 * Loads the full calendar.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.HRFullCalendarApi = {
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
      eventClick: function(info) {
        var dateFormat = 'DD.MM.YYYY hh:mmA';
        var startdate = info.start.format(dateFormat);
        var enddate = startdate;

        if (info.end != null) {
          enddate = info.end.format(dateFormat);
        }

        $('#modalID').html(info.id);
        $('#modalDescription').html(info.description);
        $('#modalLocation').html(info.location);
        $('#modalStartDate').html(startdate);
        $('#modalEndDate').html(enddate);

        if (info.attachments) {
          var output = '';
          output += '<ul>';
          info.attachments.forEach(attachment => {
            output += '<li><a href="' + attachment.url + '" target="_blank" rel="nofollow noopener">' + attachment.filename + '</a></li>';
          });
          output += '</ul>';
          $('#modalAttachments').html(output);
        }

        $('#fullCalModal').dialog({
          title: info.title
        });
      },
      eventRender: function (event, element, view) {
        // Add tabindex=0 to allow keyboard to focus on each event.
        element.attr('tabindex', '0');
      },
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
