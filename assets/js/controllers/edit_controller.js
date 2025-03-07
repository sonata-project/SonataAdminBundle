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
  static targets = ['tab', 'tabStore'];

  connect() {
    if (this.tabSelected) {
      this.showFirstTabWithErrors('.sonata-ba-field-error');
    }
  }

  checkValidity() {
    if (this.tabSelected) {
      this.showFirstTabWithErrors(':invalid');
    }
  }

  prepareSubmit() {
    setTimeout(() => {
      this.submitters.forEach((submitter) => {
        submitter.disabled = true;
      });
    }, 1);

    if (this.tabSelected) {
      this.tabStoreTarget.value = this.tabSelected.getAttribute('aria-controls');
    }
  }

  showFirstTabWithErrors(errorSelector) {
    let firstTabWithErrors = null;

    this.tabTargets.forEach((tab) => {
      const pane = this.element.querySelector(tab.getAttribute('href'));
      const icon = tab.querySelector('.has-errors');

      if (pane.querySelectorAll(errorSelector).length > 0) {
        // Only show first tab with errors
        if (!firstTabWithErrors) {
          jQuery(tab).tab('show');
          firstTabWithErrors = tab;
        }

        icon.hidden = false;
      } else {
        icon.hidden = true;
      }
    });
  }

  changeTab(event) {
    const { history, location } = window;
    const { search, href, origin } = location;
    const tab = event.currentTarget;

    const searchParams = new URLSearchParams(search);
    searchParams.set('_tab', tab.getAttribute('aria-controls'));

    const url = new URL(href, origin);
    url.search = searchParams.toString();

    if (history) {
      history.pushState({ path: url.toString() }, '', url.toString());
    }
  }

  get tabSelected() {
    return this.tabTargets.find((tab) => {
      return tab.parentElement.classList.contains('active');
    });
  }

  get submitters() {
    return this.element.querySelectorAll('button');
  }
}
