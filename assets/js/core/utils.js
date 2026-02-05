/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export function wrap(el, wrapper = document.createElement('div')) {
  el.parentNode.insertBefore(wrapper, el);
  wrapper.appendChild(el);

  return wrapper;
}

export function activateScriptElement(el) {
  const script = document.createElement('script');
  script.textContent = el.textContent;
  script.async = false;

  [...el.attributes].forEach(({ name, value }) => {
    script.setAttribute(name, value);
  });

  return script;
}

export function createDocumentFragment(html) {
  const template = document.createElement('template');
  template.innerHTML = html;
  return template.content;
}

export function getMetaContent(name) {
  const element = document.querySelector(`meta[name="${name}"]`);
  return element?.content;
}

export function controlReset(el) {
  switch (el.tagName.toLowerCase()) {
    case 'select':
      el.selectedIndex = -1;
      break;

    case 'textarea':
    case 'input':
      if (['radio', 'checkbox'].includes(el.type.toLowerCase())) {
        if (el.checked) {
          el.checked = false;
        }
      } else {
        el.value = '';
      }
      break;

    default:
      break;
  }
}

export function controlValue(el) {
  if (el.options && el.multiple) {
    return Array.from(el.options)
      .filter((option) => option.selected)
      .map((option) => option.value);
  }

  return el.value;
}

export function convertQueryStringToObject(str) {
  return str.split('&').reduce((accumulator, keyValue) => {
    const key = decodeURIComponent(keyValue.split('=')[0]);
    const val = keyValue.split('=')[1];

    if (key.endsWith('[]')) {
      if (!Object.prototype.hasOwnProperty.call(accumulator, key)) {
        accumulator[key] = [];
      }
      accumulator[key].push(val);
    } else {
      accumulator[key] = val;
    }

    return accumulator;
  }, {});
}

/**
 * Converts a nested object to a query string format.
 * Replaces the 'qs' library stringify function.
 *
 * @param {Object} obj - The object to stringify
 * @param {string} prefix - The prefix for nested keys
 * @returns {string} - Query string representation
 *
 * @example
 * stringifyNestedObject({ filter: { field: 'value' } })
 * // Returns: 'filter[field]=value'
 */
export function stringifyNestedObject(obj, prefix = '') {
  const pairs = [];

  for (const key in obj) {
    if (!Object.prototype.hasOwnProperty.call(obj, key)) {
      continue;
    }

    const value = obj[key];
    const encodedKey = prefix ? `${prefix}[${encodeURIComponent(key)}]` : encodeURIComponent(key);

    if (value === null || value === undefined) {
      pairs.push(`${encodedKey}=`);
    } else if (typeof value === 'object' && !Array.isArray(value)) {
      const nested = stringifyNestedObject(value, encodedKey);
      if (nested) {
        pairs.push(nested);
      }
    } else if (Array.isArray(value)) {
      value.forEach((item) => {
        pairs.push(`${encodedKey}[]=${encodeURIComponent(item)}`);
      });
    } else {
      pairs.push(`${encodedKey}=${encodeURIComponent(value)}`);
    }
  }

  return pairs.join('&');
}
