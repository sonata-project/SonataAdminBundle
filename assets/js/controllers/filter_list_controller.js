/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['counter', 'field'];
    static outlets = ['sensiolabs-de--admin-bundle--filter'];
    static classes = ['active'];

    connect() {
        this.updateCounter();
    }

    updateCounter() {
        const count = this.enabledFields.length;
        this.counterTarget.innerHTML = count;
        this.counterTarget.classList.toggle('hidden', count === 0);
    }

    disable(id) {
        const field = this.fieldTargets.find((el) => id === el.dataset.filter);
        if (field) {
            field.classList.remove(this.activeClass);
            this.updateCounter();
        }
    }

    toggle(event) {
        const field = event.currentTarget;
        const state = field.classList.contains(this.activeClass);
        field.classList.toggle(this.activeClass, !state);

        this.sensiolabsDeAdminBundleFilterOutlet.toggleFilter(field.dataset.filter, !state);
        this.updateCounter();
    }

    get enabledFields() {
        return this.fieldTargets.filter((field) => {
            return field.classList.contains(this.activeClass);
        });
    }
}
