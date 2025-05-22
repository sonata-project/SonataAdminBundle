/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus';
import Config from '../core/config';

export default class extends Controller {
  static targets = ['topNavbar', 'navbar', 'action'];

  static get shouldLoad() {
    return Config.param('USE_STICKYFORMS');
  }

  actionTargetConnected() {
    this.actionWrapper = this.wrap(this.actionTarget, 'action-sentinel');
    this.actionWrapper.style.height = `${this.actionTarget.offsetHeight}px`;

    let hasIntersected = false;
    this.actionObserver = new IntersectionObserver(([entry]) => {
      if (!hasIntersected) {
        hasIntersected = true;
        return;
      }

      if (entry.isIntersecting) {
        this.actionTarget.classList.remove('stuck');
      } else {
        this.actionTarget.classList.add('stuck');
      }
    }, {
      rootMargin: `0px 0px -${this.actionWrapper.offsetHeight}px 0px`,
      threshold: [0, 1],
    });

    this.actionObserver.observe(this.actionWrapper);
  }

  actionTargetDisconnected() {
    this.actionObserver.disconnect();
    this.unwrap(this.actionWrapper);
  }

  navbarTargetConnected() {
    this.navbarWrapper = this.wrap(this.navbarTarget, 'navbar-sentinel');
    this.navbarWrapper.style.height = `${this.navbarTarget.offsetHeight}px`;

    this.navbarObserver = new IntersectionObserver(([entry]) => {
      if (!entry.isIntersecting) {
        this.navbarTarget.classList.add('stuck');
      } else {
        this.navbarTarget.classList.remove('stuck');
      }
    }, {
      rootMargin: `-${this.topNavbarTarget.offsetHeight + this.navbarWrapper.offsetHeight}px 0px 0px 0px`,
      threshold: [0, 1],
    });

    this.navbarObserver.observe(this.navbarWrapper);
  }

  navbarTargetDisconnected() {
    this.navbarObserver.disconnect();
    this.unwrap(this.navbarWrapper);
  }

  wrap(el, className) {
    const wrapper = document.createElement('div');
    wrapper.classList.add(className);

    el.parentNode.insertBefore(wrapper, el);
    wrapper.appendChild(el);

    return wrapper;
  }

  unwrap(el) {
    return el.replaceWith(...el.childNodes);
  }
}
