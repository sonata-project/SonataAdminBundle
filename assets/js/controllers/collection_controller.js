/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus';
import { activateScriptElement, createDocumentFragment } from '../core/utils';

export default class extends Controller {
  static targets = ['item'];
  static values = {
    numItems: Number,
  };

  connect() {
    this.numItemsValue = this.itemTargets.length;
  }

  add(event) {
    const button = event.currentTarget;
    const item = this.createItem();
    const scripts = item.querySelectorAll('script');

    scripts.forEach((script) => {
      script.replaceWith(activateScriptElement(script));
    });

    this.element.insertBefore(item, button);
    this.numItemsValue += 1;
    this.dispatch('sonata-admin-append-form-element', {
      prefix: '',
      detail: {
        item,
      },
    });

    this.dispatch('sonata-collection-item-added', {
      prefix: '',
      detail: {
        item,
      },
    });
  }

  delete(event) {
    const button = event.currentTarget;
    const item = this.itemTargets.find((el) => {
      return el.contains(button);
    });

    this.dispatch('sonata-collection-item-deleted', {
      prefix: '',
      detail: {
        item,
      },
    });

    item.remove();
    this.dispatch('sonata-collection-item-deleted-successful', {
      prefix: '',
    });
  }

  createItem() {
    const id = this.element.getAttribute('id');
    const parts = id.split('_');

    const idRegexp = new RegExp(`${id}_${this.prototypeName}`, 'g');
    const nameRegexp = new RegExp(`${parts[parts.length - 1]}\\]\\[${this.prototypeName}`, 'g');
    const prototype = this.prototype
      .replace(idRegexp, `${id}_${this.numItemsValue}`)
      .replace(nameRegexp, `${parts[parts.length - 1]}][${this.numItemsValue}`);

    const fragment = createDocumentFragment(prototype);
    return fragment.firstElementChild;
  }

  get prototype() {
    return this.element.getAttribute('data-prototype') || '';
  }

  get prototypeName() {
    return this.element.getAttribute('data-prototype-name') || '__name__';
  }
}
