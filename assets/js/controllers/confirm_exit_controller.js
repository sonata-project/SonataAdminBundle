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
import Translation from '../core/translation';

export default class extends Controller {
  static values = {
    snapshot: String,
    skip: Boolean,
  };

  static get shouldLoad() {
    return Config.param('CONFIRM_EXIT');
  }

  connect() {
    this.snapshotValue = this.snapshot;

    // Until the user interacts with the page, any change of the form data is made by
    // scripts (late initialized widgets, browser autofill, extensions, ...), not by the
    // user. Refreshing the snapshot right before the first user interaction prevents
    // these changes from triggering the confirmation.
    window.addEventListener('pointerdown', this.refreshSnapshot, true);
    window.addEventListener('keydown', this.refreshSnapshot, true);
    // Interactions inside an iframe (e.g. a WYSIWYG editor) are not dispatched to this
    // window, but moving the focus into the iframe blurs it.
    window.addEventListener('blur', this.refreshSnapshot);
  }

  disconnect() {
    this.stopRefreshingSnapshot();
  }

  skip() {
    this.skipValue = true;
  }

  // eslint-disable-next-line consistent-return
  confirm(event) {
    if (this.shouldConfirm) {
      const message = Translation.trans('CONFIRM_EXIT');
      event.returnValue = message;
      return message;
    }
  }

  refreshSnapshot = (event) => {
    // Ignore events dispatched by scripts, only real user interactions count.
    if (!event.isTrusted) {
      return;
    }

    this.snapshotValue = this.snapshot;
    this.stopRefreshingSnapshot();
  };

  stopRefreshingSnapshot() {
    window.removeEventListener('pointerdown', this.refreshSnapshot, true);
    window.removeEventListener('keydown', this.refreshSnapshot, true);
    window.removeEventListener('blur', this.refreshSnapshot);
  }

  get shouldConfirm() {
    if (this.skipValue) {
      return false;
    }

    return this.snapshotValue !== this.snapshot;
  }

  get snapshot() {
    const params = new URLSearchParams(new FormData(this.element));
    return params.toString();
  }
}
