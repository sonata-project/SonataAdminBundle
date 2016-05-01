import $ from 'jquery';

import './sortable.css';


/**
 * The element containing the items to sort.
 * @constant
 * @type {string}
 * @default
 */
const SORTABLE_CSS_CLASS = 'sonata-ba-sortable';

/**
 * The drag handle.
 * @constant
 * @type {string}
 * @default
 */
const DRAG_HANDLE_CSS_CLASS = 'sonata-ba-sortable-handler';

/**
 * The element containing the position hidden input.
 * @constant
 * @type {string}
 * @default
 */
const POSITION_INPUT_CONTAINER_CSS_CLASS = 'sonata-ba-sortable-position';

/**
 * The icon class for the drag handle.
 * @constant
 * @type {string}
 * @default
 */
const DRAG_HANDLE_ICON_CLASS = 'fa fa-arrows';

/** @constant */
const SORTABLE_SELECTOR = `.${SORTABLE_CSS_CLASS}`;
/** @constant */
const DRAG_HANDLE_SELECTOR = `.${DRAG_HANDLE_CSS_CLASS}`;
/** @constant */
const POSITION_INPUT_CONTAINER_SELECTOR = `.${POSITION_INPUT_CONTAINER_CSS_CLASS}`;

/**
 * Adds the drag handle icons to each item of the sortable.
 *
 * @param {jQuery} $sortable The sortable container.
 */
function addDragHandles ($sortable) {
    $sortable
        .find(DRAG_HANDLE_SELECTOR)
        .append($(`<i class="${DRAG_HANDLE_ICON_CLASS}"/>`));
}

/**
 * Finds the position hidden inputs and update their value based on the field's DOM position.
 *
 * @param {jQuery} $sortable The sortable container.
 */
function applyPositions ($sortable) {
    $sortable
        .find(POSITION_INPUT_CONTAINER_SELECTOR)
        .each((i, el) => $(el).find('input').val(i + 1));
}

/**
 * Instanciate the jQueryUI.sortable plugin on the given elements.
 *
 * @param {string|jQuery} elements
 * @returns {jQuery}
 */
function setupSortables (elements) {
    return $(elements).sortable({
        axis: 'y',
        opacity: 0.6,
        cursor: 'grabbing',
        handle: DRAG_HANDLE_SELECTOR,
        create: ({target}) => {
            const $sortable = $(target);
            addDragHandles($sortable);
            applyPositions($sortable);
        },
        stop: ({target}) => applyPositions($(target)),
    });
}

$(() => setupSortables(SORTABLE_SELECTOR));
// The whole form is reloaded when a new association is added,
// so we need to re-initialize everything
$(document)
    .on('sonata:collection-item-added', ({target}) => setupSortables($(target).find(SORTABLE_SELECTOR)))
;
