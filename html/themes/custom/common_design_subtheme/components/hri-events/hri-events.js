(function ($) {
  'use strict';

  // Collect all Event Details buttons.
  var eventButtons = document.querySelectorAll('.hri-event__copy .cd-button');
  var $eventTitles = $('.hri-event--has-desc .hri-event__title');

  // Prepare the descriptions to use jQuery UI dialogs.
  $eventTitles.each(function () {
    var id = $(this).attr('data-for');

    $('#' + id).removeClass('visually-hidden').dialog({
      autoOpen: false,
      minWidth: 500,
      title: $(this).html()
    });
  });

  // When clicking titles, open the associated dialog.
  $eventTitles.click(function () {
    var id = $(this).attr('data-for');

    $('#' + id).dialog('open');
  });

  // Process buttons so that clicking copies Event Details to clipboard.
  eventButtons.forEach(function (el) {
    el.addEventListener('click', function (ev) {
      var tempInput = document.createElement('input');
      var textToCopy = el.dataset.details;

      try {
        // Copy Event Details in browser bar to clipboard.
        document.body.appendChild(tempInput);
        tempInput.value = textToCopy;
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);

        // If we got this far, don't let the link click through.
        ev.preventDefault();
        ev.stopPropagation();

        // Show user feedback and remove after some time.
        el.classList.add('is--showing-message');

        setTimeout(function () {
          el.classList.remove('is--showing-message');
        }, 2500);
      }
      catch (err) {
        // Log errors to console.
        console.error(err);
      }
    });
  });
})(jQuery);
