import $ from 'jquery';

import ajaxSubmit from 'sonata/form/util/ajaxSubmit';

import getField from 'sonata/form/util/getField';
import getAssociationFieldContainer from 'sonata/form/util/getAssociationFieldContainer';
import getAssociationFieldWidget from 'sonata/form/util/getAssociationFieldWidget';
import {triggerAsyncEvent} from 'sonata/util/event';

import {createAlertOverlay} from 'sonata/ui/alert';


const isFormTypeModel = formType => formType === 'sonata_type_model';

/**
 * In sonata_type_model, when a new associated entity was created,
 * we re-fetch the whole association form and replace the old one.
 *
 * @param {string} objectId
 * @param {FieldDescriptionType} fieldDescription
 *
 * @returns {Promise}
 *
 * @fires sonata:association-update
 */
function retrieveAssociationField (objectId, fieldDescription) {
    const fieldId = fieldDescription.id;
    const $widget = getAssociationFieldWidget(fieldId);
    const $form = $widget.closest('form');

    return ajaxSubmit($form, {
        url: fieldDescription.routes.retrieveFormElement,
        method: 'post',
        dataType: 'html',
    })
        .then(html => {
            // TODO: describe what's going on here...
            getAssociationFieldContainer(fieldId, $form).replaceWith(html);
            const $field = getField(fieldId, $form);
            const $newElement = $field.find(`[value="${objectId}"]`);
            if ($newElement.is('input')) {
                $newElement.attr('checked', 'checked');
            } else {
                $newElement.attr('selected', 'selected');
            }
            getAssociationFieldContainer(fieldId, $form).trigger('sonata:domready');
            triggerAsyncEvent('sonata:association-update', $field, [objectId, fieldDescription]);
        })
        .catch(response => {
            getAssociationFieldContainer(fieldId).closest('.box').append(
                createAlertOverlay(response.statusText)
            );
        })
    ;
}


$(document)
    .on('sonata:association-create-response', (event, objectId, fieldDescription) => {
        if (isFormTypeModel(fieldDescription.formType)) {
            retrieveAssociationField(objectId, fieldDescription);
        }
    })
    .on('sonata:association-edit-response', (event, objectId, fieldDescription) => {
        if (isFormTypeModel(fieldDescription.formType)) {
            retrieveAssociationField(objectId, fieldDescription);
        }
    })
;
