/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import jQuery from 'jquery';
import { getConfig } from './admin-config';
import log from './admin-log';

/**
 * Return the width for simple and sortable select2 element
 * This code is an adaptation of select2 code (initContainerWidth function)
 *
 * @param {HTMLElement} element
 * @returns {string}
 */
const select2Width = (element) => {
  const ereg = /width:(auto|(([-+]?([0-9]*\.)?[0-9]+)(px|em|ex|%|in|cm|mm|pt|pc)))/i;

  const { style, width } = getComputedStyle(element);

  if (style !== undefined) {
    const attrs = style.split(';');

    for (let index = 0, { length } = attrs; index < length; index += 1) {
      const matches = attrs[index].replace(/\s/g, '').match(ereg);

      if (matches !== null && matches.length >= 1) {
        return matches[1];
      }
    }
  }

  if (width.indexOf('%') > 0) {
    return width;
  }

  return '100%';
};

/**
 * @param {HTMLElement} subject
 * @returns {void}
 */
const setupSelect2 = (subject) => {
  if (getConfig('USE_SELECT2')) {
    log('[core|setup_select2] configure Select2 on', subject);

    jQuery('select:not([data-sonata-select2="false"])', subject).each((index, element) => {
      const select = jQuery(element);
      const {
        popover,
        dataSonataSelect2AllowClear,
        dataSonataSelect2MaximumSelectionSize,
        dataSonataSelect2MinimumResultsForSearch,
      } = element.dataset;

      let allowClearEnabled = false;
      let maximumSelectionSize = null;
      let minimumResultsForSearch = 10;

      element.classList.remove('form-control');

      if (!!element.querySelector('option[value=""]') || dataSonataSelect2AllowClear === 'true') {
        allowClearEnabled = true;
      }

      if (dataSonataSelect2MaximumSelectionSize) {
        maximumSelectionSize = dataSonataSelect2MaximumSelectionSize;
      }

      if (dataSonataSelect2MinimumResultsForSearch) {
        minimumResultsForSearch = dataSonataSelect2MinimumResultsForSearch;
      }

      select.select2({
        width: () => select2Width(element),
        theme: 'bootstrap',
        dropdownAutoWidth: true,
        minimumResultsForSearch,
        placeholder: allowClearEnabled ? ' ' : '', // allowClear needs placeholder to work properly
        allowClear: allowClearEnabled,
        maximumSelectionSize,
      });

      if (undefined !== popover) {
        select
          .select2('container')
          .popover(popover.options);
      }
    });
  }
};

/**
 * @param {HTMLElement} subject
 * @param {array} data
 * @param {array} customOptions
 */
const setupSortableSelect2 = (subject, data, customOptions) => {
  const select = jQuery(subject);
  const transformedData = [];

  for (let i = 0; i < data.length; i += 1) {
    transformedData[i] = { id: data[i].data, text: data[i].label };
  }

  select.select2({
    theme: 'bootstrap',
    width() {
      return select2Width(subject);
    },
    dropdownAutoWidth: true,
    data: transformedData,
    multiple: true,
    ...customOptions,
  });

  select.select2('container').find('ul.select2-choices').sortable({
    containment: 'parent',
    start: () => {
      select.select2('onSortStart');
    },
    update: () => {
      select.select2('onSortEnd');
    },
  });

  // On form submit, transform value to match what is expected by server
  select.parents('form:first').on('submit', () => {
    const values = subject.value.trim();

    if (values !== '') {
      const name = subject.getAttribute('name');
      const baseName = name.substring(0, name.length - 1);
      const splitValues = values.split(',');

      for (let index = 0; index < values.length; index += 1) {
        jQuery('<input>')
          .attr('type', 'hidden')
          .attr('name', `${baseName + index}]`)
          .val(splitValues[index])
          .appendTo(select.parents('form:first'));
      }
    }
    select.remove();
  });
};

export { select2Width, setupSelect2, setupSortableSelect2 };
