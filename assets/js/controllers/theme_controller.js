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
    static targets = ['lightIcon', 'darkIcon'];
    static values = {
        storageKey: { type: String, default: 'sensiolabs-admin-theme' }
    };

    connect() {
        this.applyTheme(this.getStoredTheme() || this.getSystemTheme());
    }

    toggle() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.applyTheme(newTheme);
        this.storeTheme(newTheme);
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        this.updateIcons(theme);
    }

    updateIcons(theme) {
        if (this.hasLightIconTarget && this.hasDarkIconTarget) {
            if (theme === 'dark') {
                this.lightIconTarget.classList.remove('hidden');
                this.darkIconTarget.classList.add('hidden');
            } else {
                this.lightIconTarget.classList.add('hidden');
                this.darkIconTarget.classList.remove('hidden');
            }
        }
    }

    getStoredTheme() {
        return localStorage.getItem(this.storageKeyValue);
    }

    storeTheme(theme) {
        localStorage.setItem(this.storageKeyValue, theme);
    }

    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    }
}
