import $ from 'jquery';

import './list.css';

import {triggerAsyncEvent} from 'sonata/util/event';
import fetchHTML from 'sonata/util/fetchHTML';

import containsSelector from 'sonata/util/containsSelector';
import getField from 'sonata/form/util/getField';
import getAssociationFieldWidget from 'sonata/form/util/getAssociationFieldWidget';

import {createSpinner} from 'sonata/ui/spinner';


const isFormTypeModelList = formType => formType === 'sonata_type_model_list';

const containsOptionElement = containsSelector('option');


/**
 * In sonata_type_model_list, when the associated entity is set,
 * we update the hidden input field and fetch the entity short description.
 *
 * @param {string} objectId
 * @param {FieldDescriptionType} fieldDescription
 *
 * @returns {Promise}
 *
 * @fires sonata:association-update
 */
function updateShortObjectDescription (objectId, fieldDescription) {
    const fieldId = fieldDescription.id;
    const url = fieldDescription.routes.shortObjectDescription.replace('__SONATA_OBJECT_ID__', objectId);
    const $field = getField(fieldId);

    $field.val(objectId);
    getAssociationFieldWidget(fieldId)
        .addClass('loading')
        .empty()
        .append(createSpinner(24))
    ;

    return fetchHTML(url)
        .then(html => {
            const $widget = getAssociationFieldWidget(fieldId);
            $widget.removeClass('loading').html(html);
            triggerAsyncEvent('sonata:association-update', $widget, [objectId, fieldDescription]);
        }).catch(({statusText}) => {
            getAssociationFieldWidget(fieldId).removeClass('loading').empty().append(
                $('<span class="inner-field-short-description text-danger"/>').text(statusText)
            );
        })
    ;
}


$(document)
    // This widget use the default request handlers,
    // see the sonata/form/association-crud module
    //
    .on('sonata:association-list-response', (event, objectId, fieldDescription) => {
        if (isFormTypeModelList(fieldDescription.formType)) {
            updateShortObjectDescription(objectId, fieldDescription);
        }
    })
    .on('sonata:association-create-response', (event, objectId, fieldDescription) => {
        if (isFormTypeModelList(fieldDescription.formType)) {
            updateShortObjectDescription(objectId, fieldDescription);
        }
    })
    .on('sonata:association-edit-response', (event, objectId, fieldDescription) => {
        if (isFormTypeModelList(fieldDescription.formType)) {
            updateShortObjectDescription(objectId, fieldDescription);
        }
    })
    .on('sonata:association-delete-response', (event, objectId, fieldDescription) => {
        if (isFormTypeModelList(fieldDescription.formType)) {
            const $input = $(event.target);
            // if field is a select input, unselect all
            if (containsOptionElement($input)) {
                $input.attr('selectedIndex', '-1')
                    .children('option:selected')
                    .attr('selected', false);
            }
            updateShortObjectDescription('', fieldDescription);
        }
    })
;
