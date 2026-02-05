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
    static targets = ['tab', 'tabStore', 'tabContent'];

    connect() {
        if (this.tabSelected) {
            this.showFirstTabWithErrors('.sonata-ba-field-error');
        }
    }

    checkValidity() {
        if (this.tabSelected) {
            this.showFirstTabWithErrors(':invalid');
        }
    }

    prepareSubmit() {
        setTimeout(() => {
            this.submitters.forEach((submitter) => {
                submitter.disabled = true;
            });
        }, 1);

        if (this.tabSelected && this.hasTabStoreTarget) {
            this.tabStoreTarget.value = this.tabSelected.getAttribute('aria-controls');
        }
    }

    showFirstTabWithErrors(errorSelector) {
        let firstTabWithErrors = null;

        this.tabTargets.forEach((tab) => {
            const paneId = tab.getAttribute('href') || tab.getAttribute('data-target');
            const pane = paneId ? this.element.querySelector(paneId) : null;
            const icon = tab.querySelector('.has-errors');

            if (pane && pane.querySelectorAll(errorSelector).length > 0) {
                if (!firstTabWithErrors) {
                    this.showTab(tab);
                    firstTabWithErrors = tab;
                }

                if (icon) {
                    icon.hidden = false;
                }
            } else if (icon) {
                icon.hidden = true;
            }
        });
    }

    showTab(tab) {
        // Deactivate all tabs
        this.tabTargets.forEach((t) => {
            t.classList.remove('tab-active', 'active');
            t.setAttribute('aria-selected', 'false');
            t.parentElement?.classList.remove('active');
        });

        // Activate the selected tab
        tab.classList.add('tab-active', 'active');
        tab.setAttribute('aria-selected', 'true');
        tab.parentElement?.classList.add('active');

        // Hide all panes
        if (this.hasTabContentTarget) {
            this.tabContentTargets.forEach((pane) => {
                pane.classList.remove('active', 'show');
                pane.classList.add('hidden');
            });
        } else {
            this.tabTargets.forEach((t) => {
                const paneId = t.getAttribute('href') || t.getAttribute('data-target');
                const pane = paneId ? this.element.querySelector(paneId) : null;
                if (pane) {
                    pane.classList.remove('active', 'show');
                    pane.classList.add('hidden');
                }
            });
        }

        // Show the selected pane
        const paneId = tab.getAttribute('href') || tab.getAttribute('data-target');
        const pane = paneId ? this.element.querySelector(paneId) : null;
        if (pane) {
            pane.classList.add('active', 'show');
            pane.classList.remove('hidden');
        }
    }

    changeTab(event) {
        event.preventDefault();
        const tab = event.currentTarget;

        this.showTab(tab);

        const { history, location } = window;
        const { search, href, origin } = location;

        const searchParams = new URLSearchParams(search);
        searchParams.set('_tab', tab.getAttribute('aria-controls') || tab.getAttribute('href')?.replace('#', ''));

        const url = new URL(href, origin);
        url.search = searchParams.toString();

        if (history) {
            history.pushState({ path: url.toString() }, '', url.toString());
        }
    }

    get tabSelected() {
        return this.tabTargets.find((tab) => {
            return tab.classList.contains('active') ||
                   tab.classList.contains('tab-active') ||
                   tab.parentElement?.classList.contains('active');
        });
    }

    get submitters() {
        return this.element.querySelectorAll('button');
    }
}
