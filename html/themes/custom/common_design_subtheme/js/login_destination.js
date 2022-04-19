/**
 * @file
 * Attach destination to login link.
 */

(function () {
  var loginLink = document.querySelector('a[href="/user/login/hid"]');
  if (loginLink) {
    loginLink.href += '?destination=' + location.pathname + location.search + location.hash;
  }
}());

