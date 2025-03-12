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
  reload() {
    this.submitters.forEach((submitter) => {
      submitter.disabled = true;
    });

    window.top.location.href = this.element.options[this.element.selectedIndex].value;
  }

  get submitters() {
    return document.querySelectorAll('input[type=submit], button[type=submit]');
  }
}
