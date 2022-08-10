/**
 * HR.info Table of Contents
 */
(function () {
  'use strict';

  const toc = [];
  const paragraphs = document.querySelectorAll('.field--name-field-paragraphs > .field__item > *');
  const target = document.querySelector('.hri-toc__list');
  let output = '';

  // Loop through Paragraphs in the main content column and collect essential
  // info about them.
  paragraphs.forEach(function (el) {
    if (el.dataset.type !== '') {
      toc.push({
        id: el.id,
        title: el.querySelector('h2').innerText
      });
    }
  });

  // For all items found, construct some HTML.
  toc.forEach(function (item) {
    output += '<li><a href="#' + item.id + '">' + item.title + '</a></li>';
  });

  // Append to DOM
  target.innerHTML = output;
})();
