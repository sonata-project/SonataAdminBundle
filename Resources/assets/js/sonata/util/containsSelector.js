import $ from 'jquery';

import curry from './curry';


/**
 * Returns whether element contains descendants latching selector.
 *
 * @function containsSelector
 * @param {string} selector
 * @param {jQuery|HTMLElement} element
 * @returns {boolean}
 */
export default curry((selector, element) => $(element).find(selector).length > 0);
