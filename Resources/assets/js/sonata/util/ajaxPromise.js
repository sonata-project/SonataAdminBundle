import $ from 'jquery';

export default function ajaxPromise (...args) {
    return Promise.resolve($.ajax(...args));
}
