import $ from 'jquery';

import {triggerCancelableEvent, triggerAsyncEvent} from 'sonata/util/event';
import escapeRegexp from 'sonata/util/escapeRegexp';


const {reduce} = Array.prototype;


/**
 * @constant
 * @type {RegExp}
 */
const HIGHEST_COUNTER_REGEXP = /_(\d+)\D*$/;

/**
 * @constant
 * @type {Map<string, number>}
 */
const COLLECTION_COUNTERS = new Map();

/**
 * @param {jQuery} $trigger
 *
 * @returns {Promise}
 *
 * @fires sonata:native-collection-item-add
 * @fires sonata:native-collection-item-added
 */
function addCollectionRow ($trigger) {
    const $container = $trigger.closest('[data-prototype]');
    const id = $container.attr('id');
    const counter = COLLECTION_COUNTERS.get(id) + 1;

    const prototypeName = $container.data('prototypeName') || '__name__';
    let prototype = $container.data('prototype');

    // Set field id
    const idRegexp = new RegExp(escapeRegexp(`${id}_${prototypeName}`), 'g');
    prototype = prototype.replace(idRegexp, `${id}_${counter}`);

    // Set field name
    const fieldName = id.split('_').slice(-1)[0];
    const nameRegexp = new RegExp(escapeRegexp(`[${fieldName}][${prototypeName}]`), 'g');
    prototype = prototype.replace(nameRegexp, `[${fieldName}][${counter}]`);

    const $newRow = $(prototype);

    return triggerCancelableEvent('sonata:native-collection-item-add', $container, [$newRow])
        .then(() => {
            COLLECTION_COUNTERS.set(id, counter);
            $newRow.insertBefore($trigger.parent());
            $newRow.trigger('sonata:domready');
            triggerAsyncEvent('sonata:native-collection-item-added', $container, [$newRow]);
        })
    ;
}

/**
 * @param {jQuery} $trigger
 *
 * @returns {Promise}
 *
 * @fires sonata:native-collection-item-delete
 * @fires sonata:native-collection-item-deleted
 */
function removeCollectionRow ($trigger) {
    const $container = $trigger.closest('[data-prototype]');
    const $row = $trigger.closest('.sonata-collection-row');

    return triggerCancelableEvent('sonata:native-collection-item-delete', $container, [$row]).then(() => {
        $row.remove();
        triggerAsyncEvent('sonata:native-collection-item-deleted', $container, [$row]);
    });
}

/**
 * Initializes the counters for the collection forms.
 *
 * @param {jQuery} $subject
 */
function setupCollectionCounters ($subject) {
    // Count and save element of each collection
    $subject.find('[data-prototype]').each((i, element) => {
        const $collection = $(element);
        const counter = reduce.call($collection.children(), (counter, item) => {
            const $fieldContainer = $(item).find('[id^="sonata-ba-field-container"]');
            const matches = HIGHEST_COUNTER_REGEXP.exec($fieldContainer.attr('id'));
            if (matches && matches[1] && matches[1] > counter) {
                counter = parseInt(matches[1], 10);
            }

            return counter;
        }, 0);
        COLLECTION_COUNTERS.set($collection.attr('id'), counter);
    });
}


$(() => setupCollectionCounters($(document)));
$(document)
    .on('sonata:domready', ({target}) => setupCollectionCounters($(target)))
    .on('click.sonata-admin', '.sonata-collection-add', event => {
        event.preventDefault();
        addCollectionRow($(event.currentTarget));
    })
    .on('click.sonata-admin', '.sonata-collection-delete', event => {
        event.preventDefault();
        removeCollectionRow($(event.currentTarget));
    })
;
