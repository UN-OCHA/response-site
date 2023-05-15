/**
 * HR.info iFrame embeds.
 */

(function iife() {
  var autoIframes = document.querySelectorAll('.hri-iframe-tableau');

  // Did we find iframes with the 'auto' aspect-ratio setting?
  if (autoIframes) {
    var width = document.querySelector('.cd-layout__content').scrollWidth;

    // For every iframe found, set its CSS aspect-ratio using the attributes
    // on the container element: data-width, data-height.
    autoIframes.forEach(function (el) {
      var viz;

      var divElement = el.querySelector('.vizContainer');
      if (divElement) {
            url = el.getAttribute('data-url'),
            options = {
              hideTabs: true,
              width: width,
              height: 'auto',
              onFirstVizSizeKnown: function (e) {
                let height = e.getVizSize().sheetSize.minSize.height;
                divElement.querySelector('iframe').style.height = height + 'px';
              }
            };

        viz = new tableau.Viz(divElement, url, options);
      }
    });
  }
})();
