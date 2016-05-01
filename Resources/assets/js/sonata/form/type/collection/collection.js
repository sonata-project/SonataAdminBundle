import $ from 'jquery';

import merge from 'sonata/util/merge';
import containsSelector from 'sonata/util/containsSelector';

import getAssociationFieldContainer from 'sonata/form/util/getAssociationFieldContainer';
import ajaxSubmit from 'sonata/form/util/ajaxSubmit';
import getFieldDescription from 'sonata/form/util/getFieldDescription';
import {triggerCancelableEvent, triggerAsyncEvent} from 'sonata/util/event';
import {createSpinnerOverlay} from 'sonata/ui/spinner';
import {createAlert} from 'sonata/ui/alert';


const containsFileInput = containsSelector('input[type="file"]');

/**
 * Appends a new association field to a sonata_type_collection
 *
 * @param {FieldDescriptionType} fieldDescription
 *
 * @fires sonata:collection-item-add
 * @fires sonata:collection-item-added
 */
function appendCollectionField (fieldDescription) {
    const $fieldContainer = getAssociationFieldContainer(fieldDescription.id);
    const $form = $fieldContainer.closest('form');
    const eventArgs = merge({}, fieldDescription);

    triggerCancelableEvent('sonata:collection-item-add', $fieldContainer, eventArgs)
        .then(() => {
            const $spinner = createSpinnerOverlay(32).appendTo($fieldContainer.closest('.box'));
            return ajaxSubmit($form, {
                url: fieldDescription.routes.appendFormElement,
                method: 'post',
                dataType: 'html',
            })
            .then(html => {
                $fieldContainer.replaceWith(html);
                $spinner.remove();
                const $newContainer = getAssociationFieldContainer(fieldDescription.id);
                $newContainer.trigger('sonata:domready');
                if (containsFileInput($form)) {
                    $form.attr('enctype', 'multipart/form-data');
                    $form.attr('encoding', 'multipart/form-data');
                }
                triggerAsyncEvent('sonata:collection-item-added', $newContainer, eventArgs);
            })
            .catch(({statusText}) => {
                $spinner
                    .empty()
                    .css({cursor: 'pointer'})
                    .one('click', () => $spinner.remove())
                    .append(createAlert(statusText));
            });
        })
    ;
}


$(document)
    .on('click.sonata-admin', '.sonata-ba-action[data-field-action="append-form-element"]', event => {
        event.preventDefault();
        const fieldDescription = getFieldDescription($(event.currentTarget));
        appendCollectionField(fieldDescription);
    })
;
