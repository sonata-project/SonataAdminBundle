import $ from 'jquery';

import curry from 'sonata/util/curry';

import fetchHTML from 'sonata/util/fetchHTML';
import ajaxSubmit from 'sonata/form/util/ajaxSubmit';

import getField from 'sonata/form/util/getField';
import getParentListField from 'sonata/list/util/getParentListField';
import getFieldDescription from 'sonata/form/util/getFieldDescription';
import {
    triggerCancelableEvent,
    triggerAsyncEvent,
} from 'sonata/util/event';

import Dialog from 'sonata/ui/dialog';


/**
 * Dummy event handler.
 * @param {Event} event
 *
 * @returns {*}
 */
const preventDefault = event => event.preventDefault();

/**
 * @param {jQuery|HTMLElement} input
 *
 * @returns {string}
 */
const getInputValue = input => ($(input).val() || '').trim();

/**
 * Returns whether the given element is an anchor inside the same page.
 *
 * @param {jQuery} $el
 * @returns {boolean}
 */
function isAnchor ($el) {
    const href = $el.attr('href');
    return $el.is('a') && (!href || href[0] === '#');
}

/**
 * @param {string} title
 *
 * @returns {Promise.<Dialog>}
 */
const openDialog = title => new Dialog(title).appendTo(document.body).open();

/**
 * @param {Dialog} dialog
 * @param {string} html
 *
 * @returns {Promise.<Dialog>}
 */
const populateDialog = curry((dialog, html) => dialog.setContent(html));

/**
 * @param {Dialog} dialog
 * @param {jqXHR} xhr
 *
 * @returns {Promise.<Dialog>}
 */
const handleRequestError = curry((dialog, xhr) => dialog.setContent(xhr.responseText));


//
// List action handlers
// -------------------------------------------------------------------------------------------------------------------

/**
 * handle link click in a list :
 *  - if the parent has an objectId defined then the related input gets updated
 *  - if the parent has NO objectId then an ajax request is made to continue normal navigation.
 *
 * @param {Dialog} dialog
 * @param {FieldDescriptionType} fieldDescription
 * @param {Event} event
 *
 * @fires sonata:association-list-response
 */
const handleListDialogClick = curry((dialog, fieldDescription, event) => {
    const $link = $(event.currentTarget);

    if (isAnchor($link)) {
        return null;
    }

    event.preventDefault();
    event.stopPropagation();

    const $parentListField = getParentListField($link, dialog.body);
    if (!$parentListField.length) {
        // the user does not click on a row column, continue normal navigation (i.e. filters, etc...)
        dialog.showSpinner().then(() => {
            fetchHTML($link.attr('href'))
                .then(populateDialog(dialog))
                .catch(handleRequestError(dialog));
        });

        return;
    }

    triggerAsyncEvent('sonata:association-list-response', $parentListField, [
        $parentListField.data('objectId'),
        fieldDescription,
    ]).then(() => dialog.close());
});

/**
 * Handle form submissions in list dialog, and continue navigation.
 *
 * @param {Dialog} dialog
 * @param {FieldDescriptionType} fieldDescription
 * @param {Event} event
 */
const handleListDialogSubmit = curry((dialog, fieldDescription, event) => {
    event.preventDefault();
    const $form = $(event.target);

    dialog.showSpinner().then(dialog => {
        ajaxSubmit($form, {url: $form.attr('action'), method: $form.attr('method'), dataType: 'html'})
            .then(populateDialog(dialog))
            .catch(handleRequestError(dialog));
    });
});

/**
 * Shows the dialog to choose an association from a list view.
 *
 * @param {string} url
 * @param {FieldDescriptionType} fieldDescription
 */
function showListDialog ({url, fieldDescription}) {
    openDialog(fieldDescription.label).then(dialog => {
        fetchHTML(url)
            .then(html => {
                populateDialog(dialog, html);
                // setup event listeners on the modal, passing our action
                dialog.body
                    .on('click', 'a', handleListDialogClick(dialog, fieldDescription))
                    .on('submit', 'form', handleListDialogSubmit(dialog, fieldDescription));
            })
            .catch(handleRequestError(dialog));
    });
}

function defaultListRequestHandler ({target}, request) {
    request.url = target.href;
}


//
// Create action handlers
// -------------------------------------------------------------------------------------------------------------------

/**
 * Handle navigation in the create dialog.
 *
 * @param {Dialog} dialog
 * @param {FieldDescriptionType} fieldDescription
 * @param {Event} event
 */
const handleCreateDialogClick = curry((dialog, fieldDescription, event) => {
    const $target = $(event.currentTarget);

    if (isAnchor($target) || $target.hasClass('sonata-ba-action')) {
        // a click on a tab, a sonata action, etc...
        return;
    }
    event.preventDefault();

    dialog.showSpinner().then(dialog => {
        ajaxSubmit($target, {url: $target.attr('href'), method: 'get'})
            .then(populateDialog(dialog))
            .catch(handleRequestError(dialog));
    });
});

/**
 * Handle form submissions in the create dialog.
 *
 * @param {Dialog} dialog
 * @param {FieldDescriptionType} fieldDescription
 * @param {Event} event
 *
 * @fires sonata:submit
 * @fires sonata:association-create-response
 */
const handleCreateDialogSubmit = curry((dialog, fieldDescription, event) => {
    event.preventDefault();
    const $form = $(event.target);

    // let listeners cancel the event, e.g. for client-side validation
    triggerCancelableEvent('sonata:submit', $form)
        .then(() => dialog.showSpinner())
        .then(() => ajaxSubmit($form, {url: $form.attr('action'), method: $form.attr('method')}))
        .then(response => {
            // if the crud action return ok, then the element has been added
            // so the widget container must be refresh with the last option available
            if (response.result !== 'ok') {
                return populateDialog(dialog, response);
            }
            triggerAsyncEvent('sonata:association-create-response', $form, [
                response.objectId,
                fieldDescription,
            ]).then(() => dialog.close());
        })
        .catch(handleRequestError(dialog))
    ;
});

/**
 * Shows the dialog to create a new association.
 *
 * @param {string} url
 * @param {FieldDescriptionType} fieldDescription
 */
function showCreateDialog ({url, fieldDescription}) {
    openDialog(fieldDescription.label).then(dialog => {
        fetchHTML(url)
            .then(html => {
                populateDialog(dialog, html);
                // setup event listeners on the modal, passing our action
                dialog.body
                    .on('click', 'a', handleCreateDialogClick(dialog, fieldDescription))
                    .on('submit', 'form', handleCreateDialogSubmit(dialog, fieldDescription));
            })
            .catch(handleRequestError(dialog));
    });
}

function defaultCreateRequestHandler ({target}, request) {
    request.url = target.href;
}


//
// Edit action handlers
// -------------------------------------------------------------------------------------------------------------------

/**
 * Handle form submissions in the create dialog.
 *
 * @param {Dialog} dialog
 * @param {FieldDescriptionType} fieldDescription
 * @param {Event} event
 *
 * @fires sonata:submit
 * @fires sonata:association-create-response
 */
const handleEditDialogSubmit = curry((dialog, fieldDescription, event) => {
    event.preventDefault();
    const $form = $(event.target);

    // let listeners cancel the event, e.g. for client-side validation
    triggerCancelableEvent('sonata:submit', $form)
        .then(() => dialog.showSpinner())
        .then(() => ajaxSubmit($form, {url: $form.attr('action'), method: $form.attr('method')}))
        .then(response => {
            // The edit controller returns JSON only for 200 responses.
            if (response.result !== 'ok') {
                return populateDialog(dialog, response);
            }
            triggerAsyncEvent('sonata:association-edit-response', $form, [
                response.objectId,
                fieldDescription,
            ]).then(() => dialog.close());
        })
        .catch(handleRequestError(dialog))
    ;
});

function showEditDialog ({url, fieldDescription}) {
    openDialog(fieldDescription.label).then(dialog => {
        fetchHTML(url)
            .then(html => {
                populateDialog(dialog, html);
                // setup event listeners on the modal, passing our action
                dialog.body
                    // we use the same click handler as the create action
                    .on('click', 'a', handleCreateDialogClick(dialog, fieldDescription))
                    .on('submit', 'form', handleEditDialogSubmit(dialog, fieldDescription));
            })
            .catch(handleRequestError(dialog));
    });
}

function defaultEditRequestHandler ({target}, request) {
    const {fieldDescription: {id, routes}} = request;
    const $input = getField(id, $(target).closest('form'));
    const value = getInputValue($input);
    if (!value) {
        // Association is not set or was deleted, just
        return;
    }

    request.url = routes.edit.replace('__SONATA_OBJECT_ID__', value);
}


//
// Show action handlers
// -------------------------------------------------------------------------------------------------------------------

/**
 * Shows the dialog to display an existing association.
 *
 * @param {string} url
 * @param {FieldDescriptionType} fieldDescription
 */
function showShowDialog ({url, fieldDescription}) {
    openDialog(fieldDescription.label).then(dialog => {
        fetchHTML(url)
            .then(html => {
                populateDialog(dialog, html);
                // It would be nice to be able to navigate in here, submit forms and stuff as usual,
                // but the CRUDController::edit action returns JSON when requested with XHR,
                // instead of checking the requested format :/
                // So for now we just prevent further stuff from happening.
                dialog.body
                    .on('click', 'a', preventDefault)
                    .on('submit', 'form', preventDefault);
            })
            .catch(handleRequestError(dialog));
    });
}

function defaultShowRequestHandler ({target}, request) {
    const {fieldDescription: {id, routes}} = request;
    const $input = getField(id, $(target).closest('form'));
    const value = getInputValue($input);
    if (!value) {
        // Association is not set or was deleted, just
        return;
    }

    request.url = routes.show.replace('__SONATA_OBJECT_ID__', value);
}


//
// Delete action handlers
// -------------------------------------------------------------------------------------------------------------------

/**
 * Triggers the sonata:association-delete-response.
 *
 * @param {jQuery} inputField
 * @param {FieldDescriptionType} fieldDescription
 *
 * @fires sonata:association-delete-response
 */
function unlinkAssociation ({inputField, fieldDescription}) {
    triggerAsyncEvent('sonata:association-delete-response', inputField, [
        getInputValue(inputField),
        fieldDescription,
    ]);
}

function defaultDeleteRequestHandler ({target}, request) {
    const {fieldDescription: {id}} = request;
    const $input = getField(id, $(target).closest('form'));
    const value = getInputValue($input);
    if (!value) {
        // Association is not set or was deleted, just
        return;
    }

    request.inputField = $input;
}


//
// Main CRUD event handlers
// -------------------------------------------------------------------------------------------------------------------


/**
 * Handles the original DOM Event, retrieves the FieldDescriptionType object, and dispatches the given request event.
 * Widgets listening to this event can then perform their own logic
 * to populate the request fields required by the matcher.
 * If a listener has provided the requested fields, we call the handler with the request.
 * The handler is then responsible for calling the corresponding response event when appropriate.
 *
 * @param {string} requestEvent The request event name to dispatch.
 * @param {function} matcher (request -> boolean) Returns whether the handler should be called for the request.
 * @param {function} handler (request -> any) Handles the request
 * @param {Event} domEvent The originating DOM Event.
 */
const actionDispatcher = curry((requestEvent, matcher, handler, domEvent) => {
    domEvent.preventDefault();
    const $link = $(domEvent.currentTarget);
    const fieldDescription = getFieldDescription($link);
    if (!fieldDescription) {
        throw new Error('No field description found on association action button.');
    }

    const request = {fieldDescription};

    triggerCancelableEvent(requestEvent, $link, [request]).then(() => {
        if (matcher(request)) {
            handler(request);
        }
    });
});

const prop = curry((prop, obj) => obj[prop]);
const hasUrl = prop('url');
const hasInputField = prop('inputField');


$(document)
    // ---------- Show button
    .on(
        'click.sonata-admin',
        '.sonata-ba-action[data-field-action="show-association"]',
        actionDispatcher('sonata:association-show-request', hasUrl, showShowDialog)
    ).on('sonata:association-show-request', defaultShowRequestHandler)
    // ---------- List button
    .on(
        'click.sonata-admin',
        '.sonata-ba-action[data-field-action="list-association"]',
        actionDispatcher('sonata:association-list-request', hasUrl, showListDialog)
    ).on('sonata:association-list-request', defaultListRequestHandler)
    // ---------- Create button
    .on(
        'click.sonata-admin',
        '.sonata-ba-action[data-field-action="create-association"]',
        actionDispatcher('sonata:association-create-request', hasUrl, showCreateDialog)
    ).on('sonata:association-create-request', defaultCreateRequestHandler)
    .on(
        'click.sonata-admin',
        '.sonata-ba-action[data-field-action="edit-association"]',
        actionDispatcher('sonata:association-edit-request', hasUrl, showEditDialog)
    ).on('sonata:association-edit-request', defaultEditRequestHandler)
    // ---------- Delete button
    .on(
        'click.sonata-admin',
        '.sonata-ba-action[data-field-action="remove-association"]',
        actionDispatcher('sonata:association-delete-request', hasInputField, unlinkAssociation)
    ).on('sonata:association-delete-request', defaultDeleteRequestHandler)
;
