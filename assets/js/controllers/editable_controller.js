/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus'
import parseHTML from '../parse_html'

export default class extends Controller {
  submit(form, controller) {
    const options = {
      body: new FormData(form),
      method: 'POST',
    };

    fetch(form.getAttribute('action') || '', options)
      .then((response) => {
        if (response.ok) {
          return response.text();
        }

        return Promise.reject(response.text());
      })
      .then((response) => {
        this.element.replaceWith(parseHTML(response));
        this.hide();
      }).catch((response) => {
        controller.replaceWith(parseHTML(response));
      });
  }

  show() {
    fetch(this.element.dataset.url)
    .then((response) => response.text())
    .then((response) => {
      $(this.element).popover({
        container: 'body',
        placement: 'top',
        html: true,
        content: parseHTML(response),
      });

      $(this.element).popover('show');
    });
  }

  hide() {
    $(this.element).popover('destroy');
  }
}
