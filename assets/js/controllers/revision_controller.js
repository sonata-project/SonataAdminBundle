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
  static targets = ['preview'];

  showPreview(event) {
    const link = event.currentTarget;
    if (!(link instanceof HTMLAnchorElement)) {
      return;
    }

    const options = {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    };

    this.previewTarget.innerHTML = '';
    fetch(link.href, options)
      .then((response) => response.text())
      .then((response) => {
        this.previewTarget.innerHTML = response;
      });
  }
}
