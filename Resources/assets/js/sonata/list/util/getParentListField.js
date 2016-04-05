import $ from 'jquery';

/**
 * Returns the parent list field (cell) of the given element,
 * possibly limited to the given context element.
 *
 * @param {string|jQuery|HTMLElement} element
 * @param {(jQuery|HTMLElement)} [context]
 * @returns {jQuery}
 */
export default function getParentListField (element, context) {
    return $(element).closest('.sonata-ba-list-field', context || document);
}
