import $ from 'jquery';

import ajaxPromise from 'sonata/util/ajaxPromise';
import {triggerCancelableEvent} from 'sonata/util/event';
import Dialog from 'sonata/ui/dialog';
import getParentListField from './util/getParentListField';
import ajaxSubmit from 'sonata/form/util/ajaxSubmit';


function handleDialogSubmit (dialog, $item, event) {
    event.preventDefault();
    ajaxSubmit(event.target)
        .then(response => {
            if (response.result !== 'ok') {
                return dialog.setContent(response);
            }

            return dialog.close()
                // trigger a cancelable event so devs can override the default animation.
                .then(() => triggerCancelableEvent('sonata:list-item-deleted', $item, [$item.data('objectId')]))
                .then(() => $item.fadeOut(1000))
            ;
        })
        .catch(xhr => dialog.setContent(xhr.responseText))
    ;
}


$(document).on('click', '.sonata-ba-list__item .delete_link', event => {
    event.preventDefault();
    const $link = $(event.currentTarget);
    const url = $link.attr('href');
    const dialog = new Dialog($link.attr('title'));
    const $item = $link.closest('.sonata-ba-list__item');

    triggerCancelableEvent('sonata:list-item-delete', $item, [$item.data('objectId')])
        .then(() => dialog.open())
        .then(() => ajaxPromise({url, dataType: 'html'}))
        .then(html => {
            dialog.setContent(html);
            dialog.body.on('submit', 'form', handleDialogSubmit.bind(null, dialog, $item));
        })
        .catch(xhr => dialog.setContent(xhr.responseText))
    ;
});
