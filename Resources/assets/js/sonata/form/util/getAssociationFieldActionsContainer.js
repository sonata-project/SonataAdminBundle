import $ from 'jquery';

/**
 * Returns the element containing the association action buttons
 * of the association field identified by the given id.
 *
 * Also has the class `.field-actions`
 *
 * @param {string} fieldId
 * @param {(HTMLElement|jQuery)} [context]
 * @returns {jQuery}
 */
export default function getAssociationFieldActionsContainer (fieldId, context) {
    return $(`#field_actions_${fieldId}`, context || document);
}
