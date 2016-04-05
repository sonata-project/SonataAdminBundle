import $ from 'jquery';

import i18n from 'sonata/i18n';
import SPINNER_SYMBOL from './spinner.html';
import './spinner.css';


/**
 * Defaults to `Sonata.i18n.loadingInformation` if set.
 * @constant
 * @type {string}
 */
const DEFAULT_STATUS = i18n.loadingInformation || 'Loading...';
const DEFAULT_SIZE = 64;


/**
 * Returns a new SVG spinner symbol.
 *
 * @returns {jQuery}
 */
export function createSpinnerIcon () {
    return $(SPINNER_SYMBOL);
}

/**
 * Returns a spinner element with given size and given status text.
 *
 * @param {number} size Pixel size (width & height) of the spinner.
 * @param {string} [status] The status message.
 *
 * @returns {jQuery}
 */
export function createSpinner (size = DEFAULT_SIZE, status = DEFAULT_STATUS) {
    const $icon = $(SPINNER_SYMBOL).addClass('sonata-spinner__icon').css({
        width: size,
        height: size,
    });
    const $status = $('<span class="sonata-spinner__status sr-only" role="status" />').text(status);

    return $('<span class="sonata-spinner" />').append($icon).append($status);
}

/**
 * Returns an overlay element containing a spinner.
 *
 * @param {number} size Pixel size (width & height) of the spinner.
 * @param {string} [status] The status message.
 *
 * @returns {jQuery}
 */
export function createSpinnerOverlay (size = DEFAULT_SIZE, status = DEFAULT_STATUS) {
    return $('<div class="overlay spinner-overlay text-primary" />')
        .append(createSpinner(size, status))
    ;
}

