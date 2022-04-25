(function () {
  'use strict';

  // Collect all URL buttons.
  var eventButtons = document.querySelectorAll('.hri-event__copy .cd-button');

  // Process links co they copy URL to clipboard.
  eventButtons.forEach(function (el) {
    el.addEventListener('click', function (ev) {
      var tempInput = document.createElement('input');
      var textToCopy = el.dataset.details;

      try {
        // Copy URL in browser bar to clipboard.
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
})();
