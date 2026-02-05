/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus';
import { controlReset, controlValue, convertQueryStringToObject, stringifyNestedObject } from '../core/utils.js';

export default class extends Controller {
    static targets = ['form', 'group', 'advanced', 'submitter'];
    static outlets = ['sensiolabs-de--admin-bundle--filter-list'];
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
        this.formElements
            .filter((element) => element.closest('[hidden]'))
            .forEach((element) => controlReset(element));

        const defaults = convertQueryStringToObject(
            stringifyNestedObject({
                filter: this.defaultValuesValue,
            })
        );

        const changed = [];
        this.formElements.forEach((element) => {
            const defaultValue = element.multiple ? [] : '';
            const defaultElementValue = defaults[element.name] || defaultValue;
            const elementValue = controlValue(element) || defaultValue;

            if (JSON.stringify(defaultElementValue) === JSON.stringify(elementValue)) {
                element.removeAttribute('name');
            } else if (element.multiple && JSON.stringify(elementValue) === '[]') {
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
        this.sensiolabsDeAdminBundleFilterListOutlet.disable(params.id);
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
