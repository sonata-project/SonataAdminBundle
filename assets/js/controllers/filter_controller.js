/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import qs from 'qs';
import { Controller } from '@hotwired/stimulus';
import { controlValue, convertQueryStringToObject } from '../utils';

export default class extends Controller {
  static targets = ['form', 'group', 'advanced', 'submitter'];
  static outlets = ['sonata-filter-list'];
  static values = {
    defaultValues: Object,
  };

  connect() {
    const withAdvanced = this.advancedTargets.find((advanced) => !advanced.hidden) !== undefined;
    this.advancedTargets.forEach((advanced) => {
      advanced.hidden = !withAdvanced;
    });
  }

  prepareSubmit() {
    const defaults = convertQueryStringToObject(
      qs.stringify({
        filter: this.defaultValuesValue,
      })
    );

    const changed = [];
    this.formElements.forEach((element) => {
      const defaultValue = element.multiple ? [] : '';
      const defaultElementValue = defaults[element.name] || defaultValue;
      const elementValue = controlValue(element) || defaultValue;

      if (element.closest('[hidden]')) {
        element.removeAttribute('name');
      } else if (JSON.stringify(defaultElementValue) === JSON.stringify(elementValue)) {
        element.removeAttribute('name');
      } else if (element.multiple && JSON.stringify(elementValue) === '[]') {
        // Empty array values will not be submitted, but we need to override
        // the default value provided by `AdminInterface::getDefaultFilterParameters()`.
        // So we change the empty select to an empty input in order to have a submitted value.
        // @see https://github.com/sonata-project/SonataAdminBundle/issues/7547

        // We remove the `[]` from the select name in order to generate the input name
        const name = element.name.substring(0, element.name.length - 2);
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = '';

        this.formTarget.appendChild(input);
        element.removeAttribute('name');
      } else {
        changed.push(element);
      }
    });

    if (changed.length === 0) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'filters';
      input.value = 'reset';

      this.formTarget.appendChild(input);
    }

    this.submitterTarget.disabled = true;
  }

  toggleAdvanced() {
    this.advancedTargets.forEach((advanced) => {
      advanced.hidden = !advanced.hidden;
    });
  }

  toggleFilter(id, state) {
    const group = this.groupTargets.find((el) => id === el.id);
    if (group) {
      group.hidden = !state;
      this.element.hidden = !this.visibleGroups.length;
    }
  }

  hideFilter({ params }) {
    this.toggleFilter(params.id, false);
    this.sonataFilterListOutlet.disable(params.id);
  }

  get visibleGroups() {
    return this.groupTargets.filter((group) => !group.hidden);
  }

  get formElements() {
    return Array.from(this.formTarget.elements)
      .filter((tag) => ['select', 'textarea', 'input'].includes(tag.tagName.toLowerCase()))
      .filter((element) => element.name.includes('filter'));
  }
}
