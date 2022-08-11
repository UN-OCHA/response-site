/**
 * HR.info Table of Contents
 */
(function () {
  'use strict';

  Drupal.behaviors.tableOfContents = {
    attach: function (context, settings) {
      // Initialize with a few variables.
      const toc = [];
      const paragraphs = document.querySelectorAll('.field--name-field-paragraphs > .field__item > *');
      const target = document.querySelector('.hri-toc__list');
      let output = '';

      // Loop through Paragraphs in the main content column and collect essential
      // info about them.
      paragraphs.forEach(function (el) {
        var paragraphTitle = el.querySelector('.field--name-field-title') && el.querySelector('.field--name-field-title').innerText;

        if (paragraphTitle !== null) {
          toc.push({
            id: el.id,
            title: paragraphTitle
          });
        }
      });

      // For all items found, construct some HTML.
      toc.forEach(function (item) {
        output += '<li><a href="#' + item.id + '">' + item.title + '</a></li>';
      });

      // Append to DOM
      target.innerHTML = output;

      // Set up smooth-scrolling for ToC.
      //
      // First, check for prefers-reduced-motion and only continue if the media
      // query resolves to false.
      const tocLinks = document.querySelectorAll('.hri-toc__list a');
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches === false) {
        tocLinks.forEach(link => {
          link.addEventListener('click', function (ev) {
            ev.preventDefault();
            var target = '#' + link.getAttribute('href').split('#')[1];
            document.querySelector(target).scrollIntoView({behavior: 'smooth'});
          });
        });
      }
    }
  };
})();
