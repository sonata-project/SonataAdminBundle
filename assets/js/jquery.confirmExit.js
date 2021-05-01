/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

  jQuery('form[data-original]').each((index, element) => {
    if (jQuery(element).attr('data-original') !== jQuery(element).serialize()) {
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
