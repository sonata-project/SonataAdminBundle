import getAssociationFieldActions from './getAssociationFieldActions';


/**
 * Returns the the association action button with the given action type,
 * for the association field identified by the given id.
 *
 * @param {string} actionType
 * @param {string} fieldId
 * @param {(HTMLElement|jQuery)} [context]
 * @returns {jQuery}
 */
export default function getAssociationFieldAction (actionType, fieldId, context) {
    return getAssociationFieldActions(fieldId, context).filter(`[data-field-action="${actionType}"]`);
}
