import $ from 'jquery';

import containsSelector from 'sonata/util/containsSelector';
import './errors.css';


const {forEach, map} = Array.prototype;


//
// Form errors.
// ----------------------------------------------------------------------------------------------------------------

/**
 * @typedef {{
 *   pane: jQuery,
 *   trigger: jQuery,
 *   hasError: boolean
 * }} TabType
 */


/**
 * Returns true if we shouldn't set the aria-invalid attribute on an input.
 * (e.g. disabled, button, etc...)
 *
 * @param {HTMLInputElement} input
 * @returns {boolean}
 */
function cannotBeInvalid (input) {
    return !!(
        input.disabled
        || input.readonly
        || ['hidden', 'button', 'submit', 'reset', 'image'].indexOf(input.type) > -1
    );
}

const containsServerSideError = containsSelector('.sonata-ba-field-error');
// TODO: client-side validation
// const containsClientSideError = containsSelector(':invalid');

/**
 * Returns an array of TabType objects for the given form.
 *
 * @param {jQuery} $form
 * @param {Function} hasError
 * @returns {TabType[]}
 */
function getFormTabs ($form, hasError) {
    return map.call($form.find('.tab-pane'), tab => {
        const $trigger = $form.find(`[data-toggle="tab"][href="#${tab.id}"]`);
        return {
            pane: $(tab),
            trigger: $trigger,
            hasError: hasError(tab),
        };
    });
}

/**
 * Returns a TabType objects contains an error.
 *
 * @param {TabType} tab
 * @returns {boolean}
 */
function tabHasError (tab) {
    return !!tab.hasError;
}

/**
 * Returns whether a given input element is in error state.
 *
 * @param {jQuery|HTMLElement|string} input
 * @returns {boolean}
 */
function inputHasError (input) {
    return $(input).closest('.sonata-ba-field-error').length > 0;
}

/**
 * Filters out tabs that do not have errors.
 *
 * @param {TabType[]} tabs
 * @returns {TabType[]}
 */
function keepInvalidTabs (tabs) {
    return tabs.filter(tabHasError);
}

/**
 * Mark tabs containing errors.
 *
 * @param {TabType[]} tabs
 * @returns {TabType[]}
 */
function markInvalidTabs (tabs) {
    tabs.forEach(tab => {
        tab.trigger.parent().toggleClass('has-error', tabHasError(tab));
    });

    return tabs;
}

/**
 * For a group of tabs, find the first one having an error and set it as the active tab.
 *
 * @param {TabType[]} tabs
 *
 * @returns {Promise.<TabType|null>}
 */
function switchToFirstInvalidTab (tabs) {
    const tab = keepInvalidTabs(tabs)[0];
    if (tab) {
        return new Promise(resolve => {
            tab.trigger
                .one('shown.bs.tab', () => resolve(tab))
                .tab('show');
        });
    }

    return Promise.resolve();
}

/**
 * For a given element, find the first error container.
 *
 * @param {jQuery} $element
 * @returns {jQuery}
 */
function findFirstInvalidField ($element) {
    return $element.find('.sonata-ba-field-error').first();
}

/**
 * Sets the aria-invalid attribute on all inputs set as invalid by the server.
 *
 * @param {HTMLFormElement} form
 */
function setInvalidStatesFromServer (form) {
    forEach.call(form.elements, input => {
        if (cannotBeInvalid(input)) {
            return;
        }
        input.setAttribute('aria-invalid', inputHasError(input));
    });
}

function setupForms ($element) {
    const promises = map.call($element.find('form'), form => {
        setInvalidStatesFromServer(form);
        const tabs = getFormTabs($(form), containsServerSideError);
        if (tabs.length) {
            markInvalidTabs(tabs);
            return switchToFirstInvalidTab(tabs);
        }

        return Promise.resolve();
    });

    // Wait for possible tabs to be activated, so that scrollIntoView find our target element.
    Promise.all(promises).then(() => {
        const $field = findFirstInvalidField($element);
        if ($field.length) {
            const $formGroup = $field.closest('.form-group');
            // scrollIntoView behavior option is currently FF only :/
            ($formGroup.length ? $formGroup : $field)[0].scrollIntoView({behavior: 'smooth'});
            $field.find(':input').focus();
        }
    });
}

$(() => setupForms($(document)));
$(document).on('sonata:domready', ({target}) => setupForms($(target)));
// TODO: client-side validation
// $(subject)
//    .on('click', 'form [type="submit"]', function() {
//        Admin.show_form_first_tab_with_errors($(this).closest('form'), ':invalid');
//    })
//    .on('keypress', 'form [type="text"]', function(e) {
//        if (13 === e.which) {
//            Admin.show_form_first_tab_with_errors($(this), ':invalid');
//        }
//    })


//
// Inline form errors.
// ----------------------------------------------------------------------------------------------------------------

/**
 * @constant
 * @type {string}
 */
const DELETE_CHECKBOX_SELECTOR = '.sonata-ba-field-inline-table [id$="_delete"][type="checkbox"]';

/**
 * Disables inline form errors when the row is marked for deletion
 *
 * @param {jQuery} $subject
 */
function toggleInlineFormErrors ($subject) {
    const $row = $subject.closest('.sonata-ba-field-inline-table');
    const $errors = $row.find('.sonata-ba-field-error-messages');

    if ($subject.is(':checked')) {
        $row.find('[required]').removeAttr('required').attr('data-required', 'required');
        $errors.hide();
    } else {
        $row.find('[data-required]').attr('required', 'required');
        $errors.show();
    }
}

/**
 * @param {jQuery} $subject
 */
function setupInlineFormErrors ($subject) {
    $subject.find(DELETE_CHECKBOX_SELECTOR).each((i, checkbox) => toggleInlineFormErrors($(checkbox)));
    $subject.on('change', DELETE_CHECKBOX_SELECTOR, ({target}) => toggleInlineFormErrors($(target)));
}


$(() => setupInlineFormErrors($(document)));
$(document).on('sonata:domready', ({target}) => setupInlineFormErrors($(target)));
