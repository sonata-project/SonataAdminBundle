import $ from 'jquery';

import raf from 'sonata/util/raf';

//
// Event helpers
// ------------------------------------------------------------------------------------------------------------

/**
 * Triggers an event on the specified target and returns a promise of that event.
 * The promise will resolve only if preventDefault() is not called by a handler.
 *
 * @param {string} name The event name
 * @param {jQuery|HTMLElement|HTMLDocument} [target]
 * @param {array|object} [args] Custom arguments to pass to the handlers.
 *
 * @returns {Promise.<jQuery.Event>}
 */
export function triggerCancelableEvent (name, target = document, args = []) {
    const event = $.Event(name);
    return new Promise(resolve => {
        $(target).trigger(event, args);
        if (!event.isDefaultPrevented()) {
            resolve(event);
        }
    });
}

/**
 * Returns a promise of a jQuery event, to be fired on the next animation frame.
 *
 * @param {string} name The event name
 * @param {jQuery|HTMLElement|HTMLDocument} [target]
 * @param {array|object} [args] Custom arguments to pass to the handlers.
 *
 * @returns {Promise.<jQuery.Event>}
 */
export function triggerAsyncEvent (name, target = document, args = []) {
    const event = $.Event(name);
    return new Promise(resolve => {
        raf(() => {
            $(target).trigger(event, args);
            resolve(event);
        });
    });
}
