(function () {
  'use strict';

  Drupal.behaviors.tableOfContents = {
    attach: function (context, settings) {
      const tabs = context.querySelectorAll('#block-operation-tabs .tabs li');
      if (!tabs || tabs.length <= 1) {
        return;
      }

      window.addEventListener('resize', function () {
        let first = tabs[0];
        for (let i = 1; i++; i < tabs.length) {
          if (Math.abs(first.getBoundingClientRect().top, tabs[i].getBoundingClientRect().top) > 10) {
            first.style.flex = '100%';
            return;
          }
        }
      });

      window.dispatchEvent(new Event('resize'));
    }
  }
})();
