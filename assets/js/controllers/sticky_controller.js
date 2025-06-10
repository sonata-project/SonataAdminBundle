/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus';
import { wrap } from '../core/utils';
import Config from '../core/config';

export default class extends Controller {
  static targets = ['topNavbar', 'navbar', 'action'];

  static get shouldLoad() {
    return Config.param('USE_STICKYFORMS');
  }

  connect() {
    this.resizeObserver = new ResizeObserver(this.resize.bind(this));
    if (this.hasActionTarget) {
      this.resizeObserver.observe(this.actionTarget);
    }

    if (this.hasNavbarTarget) {
      this.resizeObserver.observe(this.navbarTarget);
    }
  }

  disconnect() {
    this.resizeObserver.disconnect();
  }

  resize(entries) {
    entries.forEach((entry) => {
      if (this.hasActionTarget && entry.target === this.actionTarget) {
        this.actionIntersect();
      } else if (this.hasNavbarTarget && entry.target === this.navbarTarget) {
        this.navbarIntersect();
      }
    });
  }

  actionIntersect() {
    const wrapper = this.actionTarget.closest('.action-sentinel') || wrap(this.actionTarget);
    wrapper.classList.add('action-sentinel');
    wrapper.style.height = `${this.actionTarget.offsetHeight}px`;

    let hasIntersected = false;
    const callback = ([entry]) => {
      if (!hasIntersected) {
        hasIntersected = true;
        return;
      }

      if (entry.isIntersecting) {
        this.actionTarget.classList.remove('stuck');
      } else {
        this.actionTarget.classList.add('stuck');
      }
    };

    const observer = new IntersectionObserver(callback, {
      rootMargin: `0px 0px -${wrapper.offsetHeight}px 0px`,
      threshold: [0, 1],
    });

    observer.observe(wrapper);
  }

  navbarIntersect() {
    const wrapper = this.navbarTarget.closest('.navbar-sentinel') || wrap(this.navbarTarget);
    wrapper.classList.add('navbar-sentinel');
    wrapper.style.height = `${this.navbarTarget.offsetHeight}px`;

    const callback = ([entry]) => {
      if (!entry.isIntersecting) {
        this.navbarTarget.classList.add('stuck');
      } else {
        this.navbarTarget.classList.remove('stuck');
      }
    };

    const observer = new IntersectionObserver(callback, {
      rootMargin: `-${this.topNavbarTarget.offsetHeight + wrapper.offsetHeight}px 0px 0px 0px`,
      threshold: [0, 1],
    });

    observer.observe(wrapper);
  }
}
