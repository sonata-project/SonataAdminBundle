/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['counter', 'field'];
  static outlets = ['sonata-filter'];
  static classes = ['active'];

  connect() {
    this.updateCounter();
  }

  updateCounter() {
    this.counterTarget.innerHTML = this.enabledFields.length;
  }

  disable(id) {
    const field = this.fieldTargets.find((el) => id === el.dataset.filter);
    if (field) {
      field.classList.remove(this.activeClass);
      this.updateCounter();
    }
  }

  toggle(event) {
    const field = event.target;
    const state = field.classList.contains(this.activeClass);
    field.classList.toggle(this.activeClass, !state);

    this.sonataFilterOutlet.toggleFilter(field.dataset.filter, !state);
    this.updateCounter();
  }

  get enabledFields() {
    return this.fieldTargets.filter((field) => {
      return field.classList.contains(this.activeClass);
    });
  }
}
