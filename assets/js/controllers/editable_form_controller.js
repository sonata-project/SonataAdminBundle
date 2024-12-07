/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['form', 'submitter'];
  static outlets = ['editable'];

  submit(event) {
    this.editableOutlet.submit(this.formTarget, this.element);
    this.submitterTarget.disabled = true;
    event.preventDefault();
  }

  cancel() {
    this.editableOutlet.hide();
  }
}
