import curry from './curry';

// a -> [a]
const getExternalScripts = el => Array.from(el.querySelectorAll('script[src]'));

const getScriptsSources = el => getExternalScripts(el).map(s => s.src).filter(Boolean);

const scriptExists = curry((sources, script) => sources.has(script.src));

const removeElement = el => el.parentNode.removeChild(el);


/**
 * Removes external scripts from an html string if they already exist in the document.
 *
 * @param {string} html
 * @returns {string}
 */
export default function dedupeScripts (html) {
    const sources = new Set(getScriptsSources(document));
    const el = document.createElement('div');
    el.innerHTML = html;
    getExternalScripts(el)
        .filter(scriptExists(sources))
        .forEach(removeElement)
    ;

    return el.innerHTML;
}
