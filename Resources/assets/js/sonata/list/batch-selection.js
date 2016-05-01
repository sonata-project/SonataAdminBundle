import $ from 'jquery';


const {slice} = Array.prototype;

const BATCH_FIELD_SELECTOR = '.sonata-ba-list-field-batch';
const BATCH_CHECKBOX_SELECTOR = `${BATCH_FIELD_SELECTOR} input[type="checkbox"]`;

const listSelectionStates = new WeakMap();

/**
 * @typedef {object} ListSelectionState
 * @property {number} index Index of the last clicked checkbox in the list.
 * @property {bool} checked Checked state of the last clicked checkbox in the list.
 */

/**
 * Returns the selection state for the given admin list.
 *
 * @param {HTMLElement} list
 * @returns {ListSelectionState}
 */
function getListSelectionState (list) {
    if (!listSelectionStates.has(list)) {
        listSelectionStates.set(list, {
            index: 0,
            checked: true,
        });
    }
    return listSelectionStates.get(list);
}

/**
 * Sets the current selection state for the given admin list
 *
 * @param {HTMLElement} list
 * @param {ListSelectionState} state
 */
function setListSelectionState (list, state) {
    listSelectionStates.set(list, state);
}


/**
 * Returns all the batch checkboxes that are in the same list that the given element.
 *
 * @param {jQuery|HTMLElement} element
 * @returns {jQuery}
 */
function getAllCheckboxesInParentList (element) {
    return $(element).closest('.sonata-ba-list').find(BATCH_CHECKBOX_SELECTOR);
}

/**
 * Returns a slice of the given checkboxes list, containing all the checkboxes between
 * currentIndex and previousIndex.
 *
 * @param {jQuery} $checkboxes
 * @param {number} currentIndex
 * @param {number} previousIndex
 * @returns {Array.<jQuery>}
 */
function getCheckboxSelectionSlice ($checkboxes, currentIndex, previousIndex) {
    const [start, end] = currentIndex < previousIndex
        ? [currentIndex, previousIndex]
        : [previousIndex, currentIndex + 1]
    ;
    return slice.call($checkboxes, start, end);
}

function handleCheckboxClicked ({currentTarget, shiftKey}) {
    const $checkbox = $(currentTarget).find('input');
    const $list = $checkbox.closest('.sonata-ba-list');
    const lastCheckboxClicked = getListSelectionState($list[0]);
    const $checkboxes = getAllCheckboxesInParentList($checkbox);
    const currentIndex = $checkboxes.index($checkbox);

    if (shiftKey) {
        getCheckboxSelectionSlice($checkboxes, currentIndex, lastCheckboxClicked.index)
            .forEach(cb => $(cb).prop('checked', lastCheckboxClicked.checked).trigger('change'));
    }

    setListSelectionState($list[0], {
        index: currentIndex,
        checked: $checkbox.prop('checked'),
    });
}

function handleMasterCheckboxClicked ({target}) {
    getAllCheckboxesInParentList(target)
        .prop('checked', target.checked)
        .trigger('change');
}

function handleListCheckboxChanged ({target}) {
    $(target)
        .closest('.sonata-ba-list__item')
        .toggleClass('sonata-ba-list-row-selected', target.checked);
}

$(document)
    .on('change', '#list_batch_checkbox', handleMasterCheckboxClicked)
    .on('change', BATCH_CHECKBOX_SELECTOR, handleListCheckboxChanged)
    .on('click', `${BATCH_FIELD_SELECTOR} .checkbox`, handleCheckboxClicked)
;
