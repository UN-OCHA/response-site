/**
 * RW River - Create the Letter Navigation
 *
 * We don't have the navigation in the HTML response so we build it client-side.
 */
(function () {
  'use strict';

  var output = '';
  var navId = 'letter-navigation';
  var navIdTitle = navId + '-title';
  var navWrapper = document.querySelector('#rw-river-letter-navigation-wrapper');
  var navLetters = document.querySelectorAll('.rw-river-country-list__group__title');

  output += '<nav class="rw-river-letter-navigation" id="' + navId + '" aria-labelledby="' + navIdTitle + '">';
  output += '<h3 class="rw-river-letter-navigation__title visually-hidden" id="' + navIdTitle + '">Jump to Letter</h3>';
  output += '<ul class="rw-river-letter-navigation__list">';

  navLetters.forEach(function (letter) {
    output += '<li class="rw-river-letter-navigation__list__item">';
    output += '<a class="rw-river-letter-navigation__link" href="#group-' + letter.innerText + '">';
    output += letter.innerText;
    output += '</a></li>';
  });

  output += '<li class="rw-river-letter-navigation__list__item">';
  output += '<a class="rw-river-letter-navigation__link" href="#">All</a>';
  output += '</li>';
  output += '</ul>';
  output += '</nav>';

  navWrapper.innerHTML = output;
})();
