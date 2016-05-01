
export default function debounce (fn, delay = 32) {
    if (typeof fn !== 'function') {
        throw new TypeError('Only functions can be debounced.');
    }
    let timeoutId;

    return function debounced (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            timeoutId = null;
            fn.apply(this, args);
        }, delay);
    };
}
