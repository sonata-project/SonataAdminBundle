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
    static targets = ['sidebar', 'overlay'];

    connect() {
        // Load saved state from localStorage
        const savedState = localStorage.getItem('admin-sidebar-collapsed');
        if (savedState === 'true' && window.innerWidth >= 1024) {
            this.element.classList.add('sidebar-collapsed');
        }

        // Handle escape key to close sidebar on mobile
        document.addEventListener('keydown', this.handleEscape.bind(this));
    }

    disconnect() {
        document.removeEventListener('keydown', this.handleEscape.bind(this));
    }

    toggle() {
        if (window.innerWidth < 1024) {
            // Mobile: slide in/out
            this.sidebarTarget.classList.toggle('open');
            document.body.classList.toggle('sidebar-open');
        } else {
            // Desktop: collapse/expand
            this.element.classList.toggle('sidebar-collapsed');
            localStorage.setItem('admin-sidebar-collapsed', this.element.classList.contains('sidebar-collapsed'));
        }
    }

    close() {
        this.sidebarTarget.classList.remove('open');
        document.body.classList.remove('sidebar-open');
    }

    handleEscape(event) {
        if (event.key === 'Escape' && this.sidebarTarget.classList.contains('open')) {
            this.close();
        }
    }
}
