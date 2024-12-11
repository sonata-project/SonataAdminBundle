/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export function controlValue(el) {
  if (el.options && el.multiple) {
    // prettier-ignore
    return el.options
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
