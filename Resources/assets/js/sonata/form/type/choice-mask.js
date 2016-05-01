import $ from 'jquery';

import getField from 'sonata/form/util/getField';
import getFormGroup from 'sonata/form/util/getFormGroup';


/**
 * @constant
 * @type {string}
 */
const WIDGET_SELECTOR = '.sonata-choice-mask-widget';

/**
 * @private
 * @typedef {object} ChoiceMaskDescriptionType
 * @property {string} id
 * @property {string} parentId
 * @property {string[]} fields
 * @property {Object.<string, string>} fieldMap
 */

/**
 *
 * @param {string} parentId
 * @param {string} fieldName
 * @returns {jQuery}
 */
function getControlGroup (parentId, fieldName) {
    const controlGroupId = parentId + fieldName;
    return getFormGroup(controlGroupId);
}

/**
 *
 * @param {ChoiceMaskDescriptionType} fieldDescription
 * @param {string} fieldName
 */
function showFieldMask (fieldDescription, fieldName) {
    const {fields, fieldMap, parentId} = fieldDescription;

    if (!fieldMap[fieldName]) {
        fields.forEach(fieldName => getControlGroup(parentId, fieldName).hide());

        return;
    }
    fields.forEach(fieldName => getControlGroup(parentId, fieldName).hide());
    fieldMap[fieldName].forEach(fieldName => getControlGroup(parentId, fieldName).show());
}

function createChoiceMaskField ($subject) {
    const fieldDescription = $subject.data('fieldDescription');
    const $choiceField = getField(fieldDescription.id);

    $choiceField.on('change', () => showFieldMask(fieldDescription, $choiceField.val()));
    showFieldMask(fieldDescription, $choiceField.val());
}

function setupWidgets ($subject) {
    $subject.find(WIDGET_SELECTOR).each((i, element) => createChoiceMaskField($(element)));
}


$(() => setupWidgets($(document)));
$(document).on('sonata:domready', ({target}) => setupWidgets($(target)));

