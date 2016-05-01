import $ from 'jquery';


/**
 * Returns the field widget of the association field identified by the given id.
 *
 * Also has a class of:
 *   `.field-short-description` for `sonata_type_model_list`
 *
 * @param {string} fieldId
 * @param {(HTMLElement|jQuery)} [context]
 * @returns {jQuery}
 */
export default function getAssociationFieldWidget (fieldId, context) {
    return $(`#field_widget_${fieldId}`, context || document);
}
