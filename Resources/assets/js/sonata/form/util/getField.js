import $ from 'jquery';


/**
 * Returns the form field with the given id.
 *
 * @param {string} fieldId
 * @param {(HTMLElement|jQuery)} [context]
 * @returns {jQuery}
 */
export default function getField (fieldId, context) {
    return $(`#${fieldId}`, context || document);
}
