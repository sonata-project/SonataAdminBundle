/*!
* jQuery confirmExit plugin
* https://github.com/dunglas/jquery.confirmExit
*
* Copyright 2012 Kévin Dunglas <dunglas@gmail.com>
* Released under the MIT license
* http://www.opensource.org/licenses/mit-license.php
*/

jQuery.fn.confirmExit = function confirmExit() {
  jQuery(this).attr('data-original', jQuery(this).serialize());

  jQuery(this).on('submit', function onSubmit() {
    jQuery(this).removeAttr('data-original');
  });

  return jQuery(this);
};

// eslint-disable-next-line consistent-return
jQuery(window).on('beforeunload', (event) => {
  const e = event || window.event;
  const message = window.Admin.get_translations('CONFIRM_EXIT');
  let changes = false;

  jQuery('form[data-original]').each(function formDataOriginal() {
    if (jQuery(this).attr('data-original') !== jQuery(this).serialize()) {
      changes = true;
    }
  });

  if (changes) {
    // For old IE and Firefox
    if (e) {
      e.returnValue = message;
    }

    return message;
  }
});
