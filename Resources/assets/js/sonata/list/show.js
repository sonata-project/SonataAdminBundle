import $ from 'jquery';

import ajaxPromise from 'sonata/util/ajaxPromise';
import {triggerCancelableEvent} from 'sonata/util/event';
import Dialog from 'sonata/ui/dialog';
import getParentListField from './util/getParentListField';


// Actions are disabled in AJAX mode, no need to listen to sonata:domready
$(document).on('click', '.sonata-ba-list__item .view_link', event => {
    const $link = $(event.currentTarget);
    const url = $link.attr('href');
    const dialog = new Dialog($link.attr('title'));
    const $field = getParentListField($link);

    triggerCancelableEvent('sonata:list-item-show', $field, [$field.data('objectId'), url])
        .then(() => event.preventDefault())
        .then(() => dialog.open())
        .then(() => ajaxPromise({url, dataType: 'html'}))
        .then(html => dialog.setContent(html))
        .catch(xhr => dialog.setContent(xhr.responseText))
    ;
});
