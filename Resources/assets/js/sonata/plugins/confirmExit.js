import $ from 'jquery';

import curry from 'sonata/util/curry';


const {some} = Array.prototype;


// FIXME: jQuery.serialize() doesn't handle file inputs.
const formIsDirty = form => form.getAttribute('data-original') !== $(form).serialize();


const handleWindowUnload = curry((message, event) => {
    const e = event || window.event;
    const shouldWarnUser = some.call($('form[data-original]'), formIsDirty);

    if (shouldWarnUser) {
        if (e) {
            // For old IE and Firefox
            e.returnValue = message;
        }
        return message;
    }
});


let attached = false;

$.fn.confirmExit = function (message) {
    return this.each((i, element) => {
        const $form = $(element);
        $form.attr('data-original', $form.serialize());

        if (!attached) {
            // attach the global submit & unload handlers once
            $(document).on('submit', '.sonata-ba-form form', ({target}) => $(target).removeAttr('data-original'));
            $(window).on('beforeunload', handleWindowUnload(message));
            attached = true;
        }
    });
};

/**
 * @param {jQuery} $target
 * @param {string} message
 *
 * @returns {jQuery} The form
 */
export default ($target, message) => $target.find('.sonata-ba-form form').confirmExit(message);
