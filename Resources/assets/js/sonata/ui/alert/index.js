import $ from 'jquery';

import SKELETON from './alert.html';


/**
 * Creates a dismissible alert box.
 *
 * @param {string} message The error message
 *
 * @returns {jQuery}
 */
export function createAlert (message) {
    return $(SKELETON)
        .find('.alert-body').html(message).end()
        .alert()
    ;
}

/**
 * Returns a dismissible overlay containing an error alert box.
 *
 * @param {string} message The error message
 *
 * @returns {jQuery}
 */
export function createAlertOverlay (message) {
    return $('<div class="overlay alert-overlay"/>')
        .append(createAlert(message))
        .one('click', ({target}) => $(target).remove())
    ;
}
