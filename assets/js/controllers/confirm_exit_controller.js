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
  };

  static get shouldLoad() {
    return Config.param('CONFIRM_EXIT');
  }

  connect() {
    this.snapshotValue = this.snapshot;
  }

  // eslint-disable-next-line consistent-return
  confirm(event) {
    if (this.snapshotValue !== this.snapshot) {
      const message = Translation.trans('CONFIRM_EXIT');
      event.returnValue = message;
      return message;
    }
  }

  get snapshot() {
    const params = new URLSearchParams(new FormData(this.element));
    return params.toString();
  }
}
