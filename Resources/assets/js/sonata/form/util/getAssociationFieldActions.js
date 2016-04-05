import getAssociationFieldActionsContainer from './getAssociationFieldActionsContainer';


/**
 * Returns the association action buttons of the association field identified by the given id.
 *
 * The action buttons should have the `.sonata-ba-action` class, along with a `data-field-action` attribute
 * identifying the action type (`list-association`, `add-association`, `remove-association`).
 *
 * @param {string} fieldId
 * @param {(HTMLElement|jQuery)} [context]
 * @returns {jQuery}
 */
export default function getAssociationFieldActions (fieldId, context) {
    return getAssociationFieldActionsContainer(fieldId, context).find('.sonata-ba-action');
}
