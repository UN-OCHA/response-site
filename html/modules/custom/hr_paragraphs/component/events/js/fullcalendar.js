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
          _fullCalendarApiInit(id, drupalSettings.fullcalendar_api.instances[id], context, drupalSettings);
        }
      }
    }
  };

  /**
   * Initialize the FullCalendar instance.
   */
  function _fullCalendarApiInit(id, calendarSettings, context, drupalSettings) {

    var iCalSource = calendarSettings.ical_source;
    delete calendarSettings.ical_source;

    var handleModal = function(info) {
      var event_info = info.event._def.extendedProps;
      var startdate = event_info.local_start;
      var enddate = startdate;

      if (info.end != null) {
        enddate = event_info.local_end;
      }

      document.getElementById('modalTitle').innerHTML = info.event.title;
      document.getElementById('modalLocation').innerHTML = event_info.location;
      document.getElementById('modalStartDate').innerHTML = startdate;
      document.getElementById('modalEndDate').innerHTML = enddate;

      if (event_info.attachments) {
        var output = '';
        output += '<ul>';
        event_info.attachments.forEach(attachment => {
          output += '<li><a href="' + attachment.url + '" target="_blank" rel="nofollow noopener">' + attachment.filename + '</a></li>';
        });
        output += '</ul>';
        document.getElementById('modalAttachments').innerHTML = output;
      }

      document.getElementById('fullCalModal').style.display = 'flex';
    }
    var calendarEl = document.getElementById(id);
    if (calendarEl.classList.contains('processed')) {
      return;
    }
    // Add some settings.
    var extra_settings = {
      eventClick: function(info) {
        handleModal(info);
      },
      eventDidMount: function(info) {
        // Allow keyboard to focus on each event.
        info.el.setAttribute('tabindex', '0');
        // When [Enter] is pressed on focused event, open the modal dialog.
        info.el.addEventListener('keypress', (ev) => {
          // Check if the [Enter] key was pressed.
          if (ev.keyCode == 13) {
            handleModal(info);
          }
        });
      },
      customButtons: {
        iCalButton: {
          text: 'ical',
          click: function() {
            // Populate the modal dialog.
            document.getElementById('icalSource').innerHTML = iCalSource;
            // Display the modal,
            document.getElementById('iCalModal').style.display = 'flex';
          }
        }
      },
    };
    for (var key in extra_settings) {
      calendarSettings[key] = extra_settings[key];
    }


    var calendar = new FullCalendar.Calendar(calendarEl, calendarSettings);
    calendarEl.classList.add('processed');

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
