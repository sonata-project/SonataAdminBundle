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
    static targets = ['button', 'menu'];

    connect() {
        this.closeOnClickOutside = this.closeOnClickOutside.bind(this);
        this.closeOnEscape = this.closeOnEscape.bind(this);
    }

    toggle(event) {
        event.preventDefault();
        event.stopPropagation();

        if (this.isOpen()) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.menuTarget.classList.remove('hidden');
        this.menuTarget.classList.add('admin-animate-scale-in');
        document.addEventListener('click', this.closeOnClickOutside);
        document.addEventListener('keydown', this.closeOnEscape);
    }

    close() {
        this.menuTarget.classList.add('hidden');
        this.menuTarget.classList.remove('admin-animate-scale-in');
        document.removeEventListener('click', this.closeOnClickOutside);
        document.removeEventListener('keydown', this.closeOnEscape);
    }

    closeOnClickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }

    closeOnEscape(event) {
        if (event.key === 'Escape') {
            this.close();
        }
    }

    isOpen() {
        return !this.menuTarget.classList.contains('hidden');
    }

    disconnect() {
        document.removeEventListener('click', this.closeOnClickOutside);
        document.removeEventListener('keydown', this.closeOnEscape);
    }
}
