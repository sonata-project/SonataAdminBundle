
const META_CHARS = /[|\\{}()[\]^$+*?.]/g;

export default function (str) {
    if (typeof str !== 'string') {
        throw new TypeError('Expected a string');
    }

    return str.replace(META_CHARS, '\\$&');
}
