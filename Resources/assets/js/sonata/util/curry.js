
export default function curry (fn) {
    if (typeof fn !== 'function') {
        throw new TypeError('Only functions can be curried.');
    }
    const arity = fn.length;

    return function curried (...args) {
        if (args.length >= arity) {
            return fn.apply(this, args);
        }
        return (...args2) => curried.apply(this, args.concat(args2));
    };
}
