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
    static targets = ['icon'];
    static values = {
        storageKey: { type: String, default: 'sensiolabs-admin-sidebar-collapsed' }
    };

    connect() {
        const isCollapsed = this.getStoredState();
        if (isCollapsed) {
            this.collapse(false);
        }
        this.updateIcon();
    }

    toggle() {
        const sidebar = document.getElementById('admin-sidebar');
        const isCollapsed = sidebar?.classList.contains('sidebar-collapsed');

        if (isCollapsed) {
            this.expand();
        } else {
            this.collapse(true);
        }
    }

    collapse(animate = true) {
        const sidebar = document.getElementById('admin-sidebar');
        const main = document.querySelector('.admin-main');

        if (sidebar) {
            if (animate) {
                sidebar.style.transition = 'width 0.2s ease-out';
                main?.style && (main.style.transition = 'margin-left 0.2s ease-out');
            }
            sidebar.classList.add('sidebar-collapsed');
            document.documentElement.classList.add('sidebar-collapsed');
        }

        this.storeState(true);
        this.updateIcon();
    }

    expand() {
        const sidebar = document.getElementById('admin-sidebar');
        const main = document.querySelector('.admin-main');

        if (sidebar) {
            sidebar.style.transition = 'width 0.2s ease-out';
            main?.style && (main.style.transition = 'margin-left 0.2s ease-out');
            sidebar.classList.remove('sidebar-collapsed');
            document.documentElement.classList.remove('sidebar-collapsed');
        }

        this.storeState(false);
        this.updateIcon();
    }

    updateIcon() {
        if (!this.hasIconTarget) return;

        const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');

        // Rotate icon based on state
        this.iconTarget.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    getStoredState() {
        return localStorage.getItem(this.storageKeyValue) === 'true';
    }

    storeState(collapsed) {
        localStorage.setItem(this.storageKeyValue, collapsed ? 'true' : 'false');
    }
}
