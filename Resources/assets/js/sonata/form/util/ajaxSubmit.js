import $ from 'jquery';

import merge from 'sonata/util/merge';


/**
 * Wrapper around the jQuery.ajaxSubmit plugin to return a Promise.
 *
 * @param {jQuery|HTMLFormElement} form
 * @param {Object} options
 * @returns {Promise}
 */
export default function ajaxSubmit (form, options) {
    return new Promise((resolve, reject) => {
        $(form).ajaxSubmit(merge({}, options, {
            data: {_xml_http_request: true},
            success: resolve,
            error: reject,
        }));
    });
}
