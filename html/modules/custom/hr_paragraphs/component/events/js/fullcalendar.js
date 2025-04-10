/**
 * @file
 * Loads the full calendar.
 */

(function () {
  'use strict';
  Drupal.behaviors.HRFullCalendarApi = {
    attach: function (context, drupalSettings) {

      if (typeof drupalSettings.fullcalendar_api === 'undefined' || typeof drupalSettings.fullcalendar_api.instances === 'undefined') {
        return false;
      }

      var id;
      for (id in drupalSettings.fullcalendar_api.instances) {
        if (drupalSettings.fullcalendar_api.instances.hasOwnProperty(id)) {
          _fullCalendarApiInit(id, drupalSettings.fullcalendar_api.instances[id]);
        }
      }
    }
  };

  /**
   * Initialize the FullCalendar instance.
   */
  function _fullCalendarApiInit(id, calendarSettings) {

    var calendarEl = document.getElementById(id);
    var calendar = new FullCalendar.Calendar(calendarEl, {
      events: {
        url: calendarSettings.ical_source,
        format: 'ics'
      }
    });

    var extension = {
      eventClick: function(info) {
        console.log(info);
        var dateFormat = 'DD.MM.YYYY hh:mmA';
        var startdate = info.start.format(dateFormat);
        var enddate = startdate;

        if (info.end != null) {
          enddate = info.end.format(dateFormat);
        }

        document.getElementById('modalID').innerHTML(info.id);
        document.getElementById('modalDescription').innerHTML(info.description);
        document.getElementById('modalLocation').innerHTML(info.location);
        document.getElementById('modalStartDate').innerHTML(info.startdate);
        document.getElementById('modalEndDate').innerHTML(info.enddate);

        if (info.attachments) {
          var output = '';
          output += '<ul>';
          info.attachments.forEach(attachment => {
            output += '<li><a href="' + attachment.url + '" target="_blank" rel="nofollow noopener">' + attachment.filename + '</a></li>';
          });
          output += '</ul>';
          document.getElementById('modalAttachments').innerHTML(output);
        }

        document.getElementById('fullCalModal').alert(info.title);
      },
      eventRender: function (event, element, view) {
        // Allow keyboard to focus on each event.
        element.attr('tabindex', '0');

        // When [Enter] is pressed on focused event, open the modal dialog.
        element.keypress(function(ev){
          // Check if the [Enter] key was pressed.
          if (ev.keyCode == 13) {
            // Prepare event metadata
            var dateFormat = 'DD.MM.YYYY hh:mmA';
            var startdate = event.start.format(dateFormat);
            var enddate = startdate;

            if (event.end != null) {
              enddate = event.end.format(dateFormat);
            }

            // Populate the modal dialog with this event's metadata.
            document.getElementById('modalID').innerHTML(event.id);
            document.getElementById('modalDescription').innerHTML(event.description);
            document.getElementById('modalLocation').innerHTML(event.location);
            document.getElementById('modalStartDate').innerHTML(event.startdate);
            document.getElementById('modalEndDate').innerHTML(event.enddate);

            // Display the modal.
            document.getElementById('fullCalModal').alert(event.title);
          }
        })
      },
      customButtons: {
        iCalButton: {
          text: 'ical',
          click: function() {
            // Populate the modal dialog.
            document.getElementById('icalSource').innerHTML(calendarSettings.ical_source);

            // Display the modal,
            document.getElementById('iCalModal').alert('Copy source link');
          }
        }
      },
    };
    for (var key in extension) {
      calendarSettings[key] = extension[key];
    }

    // Add copy to clipboard.
    let copyButton = document.querySelector('.fullcalendar__copy button');
    if (copyButton) {
      copyButton.addEventListener('click', function (e) {
        try {
          let src = document.getElementById('icalSource');

          var tempInput = document.createElement('input');
          document.body.appendChild(tempInput);
          tempInput.value = src.innerHTML;
          tempInput.select();
          document.execCommand('copy');
          document.body.removeChild(tempInput);

          e.preventDefault();
          e.stopPropagation();
          document.getElementById('iCalModal').alert('close');
        }
        catch (err) {
          // Fail silently.
        }
      });
    }

    calendar.render();

  }
})();
