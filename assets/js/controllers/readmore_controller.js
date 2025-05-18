/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus';

const controllers = new WeakMap();
const observer = new ResizeObserver((entries) => {
  entries.forEach((entry) => {
    const controller = controllers.get(entry.target);
    if (controller) {
      controller.resize();
    }
  });
});

export default class extends Controller {
  static targets = ['content', 'button'];
  static values = {
    collapsedHeight: Number,
    moreText: String,
    lessText: String,
  };

  connect() {
    controllers.set(this.contentTarget, this);
    observer.observe(this.contentTarget);

    this.contentTarget.style.maxHeight = `${this.collapsedHeightValue}px`;
    this.buttonTarget.innerText = this.moreTextValue;
  }

  disconnect() {
    controllers.delete(this.contentTarget);
    observer.unobserve(this.contentTarget);
  }

  resize() {
    if (this.contentTarget.scrollHeight > this.contentTarget.clientHeight) {
      this.contentTarget.classList.add('truncated');
    } else {
      this.contentTarget.classList.remove('truncated');
    }
  }

  toggle() {
    this.contentTarget.classList.toggle('expanded');
    if (this.contentTarget.classList.contains('expanded')) {
      this.buttonTarget.innerText = this.lessTextValue;
    } else {
      this.buttonTarget.innerText = this.moreTextValue;
    }
  }
}
