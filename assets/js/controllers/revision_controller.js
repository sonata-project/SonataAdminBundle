/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
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

        event.preventDefault();

        const options = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        this.previewTarget.innerHTML = '<div class="flex items-center justify-center p-4"><svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';

        fetch(link.href, options)
            .then((response) => response.text())
            .then((response) => {
                this.previewTarget.innerHTML = response;
            })
            .catch((error) => {
                this.previewTarget.innerHTML = `<div class="text-error-500 p-4">Error loading preview: ${error.message}</div>`;
            });
    }
}
