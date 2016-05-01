import $ from 'jquery';


/**
 * Returns the field container of the association field identified by the given id.
 *
 * Also has the class `.field-container`.
 * This container typically contains:
 *   * a `#field_widget_{id}` element containing the widget used to represent the association
 *   * a `#field_actions_{id}` element, hosting the action buttons (list, add, delete)
 *
 *
 * @param {string} fieldId
 * @param {(HTMLElement|jQuery)} [context]
 * @returns {jQuery}
 */
export default function getAssociationFieldContainer (fieldId, context) {
    return $(`#field_container_${fieldId}`, context || document);
}
