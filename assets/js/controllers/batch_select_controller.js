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
  static targets = ['checkboxAll', 'checkbox', 'summary', 'allElements'];

  initialize() {
    this.toggle = this.toggle.bind(this);
    this.refresh = this.refresh.bind(this);
  }

  checkboxAllTargetConnected(checkbox) {
    checkbox.addEventListener('change', this.toggle);

    this.refresh();
  }

  checkboxTargetConnected(checkbox) {
    checkbox.addEventListener('change', this.refresh);

    this.refresh();
  }

  summaryTargetConnected() {
    this.refresh();
  }

  allElementsTargetConnected(checkbox) {
    checkbox.addEventListener('change', this.refresh);

    this.refresh();
  }

  checkboxAllTargetDisconnected(checkbox) {
    checkbox.removeEventListener('change', this.toggle);

    this.refresh();
  }

  checkboxTargetDisconnected(checkbox) {
    checkbox.removeEventListener('change', this.refresh);

    this.refresh();
  }

  allElementsTargetDisconnected(checkbox) {
    checkbox.removeEventListener('change', this.refresh);

    this.refresh();
  }

  toggle(e) {
    e.preventDefault();

    const { checked } = e.target;

    this.checkboxTargets.forEach((checkbox) => {
      checkbox.checked = checked;
      this.triggerInputEvent(checkbox);
    });

    // The checkbox targets do not emit a change event here, so the state of the
    // checkboxAll target must be computed again.
    this.refresh();
  }

  refresh() {
    if (!this.hasCheckboxAllTarget) return;

    const checkboxesCount = this.checkboxTargets.length;
    const checkboxesCheckedCount = this.checkboxTargets.filter(
      (checkbox) => checkbox.checked
    ).length;

    this.checkboxAllTarget.checked =
      checkboxesCheckedCount > 0 && checkboxesCheckedCount === checkboxesCount;
    this.checkboxAllTarget.indeterminate =
      checkboxesCheckedCount > 0 && checkboxesCheckedCount < checkboxesCount;
    this.checkboxAllTarget.classList.toggle('indeterminate', this.checkboxAllTarget.indeterminate);

    this.triggerInputEvent(this.checkboxAllTarget);

    if (this.hasSummaryTarget) {
      if (this.hasAllElementsTarget && this.allElementsTarget.checked) {
        this.summaryTarget.textContent = `${checkboxesCount} / ${checkboxesCount}`;
      } else {
        this.summaryTarget.textContent = `${checkboxesCheckedCount} / ${checkboxesCount}`;
      }
    }

    this.checkboxTargets.forEach((checkbox) => {
      const tr = checkbox.closest('tr');
      if (tr !== null) {
        tr.classList.toggle('sonata-ba-list-row-selected', checkbox.checked);
      }
    });
  }

  triggerInputEvent(checkbox) {
    const event = new Event('input', { bubbles: true, cancelable: false });

    checkbox.dispatchEvent(event);
  }

  get checked() {
    return this.checkboxTargets.filter((checkbox) => checkbox.checked);
  }

  get unchecked() {
    return this.checkboxTargets.filter((checkbox) => !checkbox.checked);
  }

  get enabled() {
    return this.checkboxTargets.filter((checkbox) => !checkbox.disabled);
  }

  get disabled() {
    return this.checkboxTargets.filter((checkbox) => checkbox.disabled);
  }
}
