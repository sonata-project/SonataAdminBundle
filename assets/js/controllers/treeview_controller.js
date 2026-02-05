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
    static targets = ['item', 'submenu'];
    static classes = ['toggled'];

    connect() {
        // Show active items on connect
        this.showActiveItems();
    }

    toggle(event) {
        event.preventDefault();
        const item = event.currentTarget.closest('[data-sensiolabs-de--admin-bundle--treeview-target="item"]');

        if (!item) return;

        const submenu = item.querySelector('[data-sensiolabs-de--admin-bundle--treeview-target="submenu"]');
        const isOpen = item.classList.contains('is-toggled');

        if (isOpen) {
            this.closeSubmenu(item, submenu);
        } else {
            this.openSubmenu(item, submenu);
        }
    }

    openSubmenu(item, submenu) {
        if (!submenu) return;

        item.classList.add('is-toggled');
        submenu.classList.remove('hidden');
        submenu.style.height = '0';
        submenu.style.overflow = 'hidden';
        submenu.style.transition = 'height 0.2s ease-out';

        requestAnimationFrame(() => {
            submenu.style.height = submenu.scrollHeight + 'px';
        });

        const onEnd = () => {
            submenu.style.height = '';
            submenu.style.overflow = '';
            submenu.style.transition = '';
            submenu.removeEventListener('transitionend', onEnd);
        };
        submenu.addEventListener('transitionend', onEnd);
    }

    closeSubmenu(item, submenu) {
        if (!submenu) return;

        submenu.style.height = submenu.scrollHeight + 'px';
        submenu.style.overflow = 'hidden';
        submenu.style.transition = 'height 0.2s ease-out';

        requestAnimationFrame(() => {
            submenu.style.height = '0';
        });

        const onEnd = () => {
            item.classList.remove('is-toggled');
            submenu.classList.add('hidden');
            submenu.style.height = '';
            submenu.style.overflow = '';
            submenu.style.transition = '';
            submenu.removeEventListener('transitionend', onEnd);
        };
        submenu.addEventListener('transitionend', onEnd);
    }

    showActiveItems() {
        const activeLinks = this.element.querySelectorAll('.active, [aria-current="page"]');

        activeLinks.forEach((link) => {
            let parent = link.parentElement;
            while (parent && parent !== this.element) {
                if (parent.hasAttribute('data-sensiolabs-de--admin-bundle--treeview-target') &&
                    parent.getAttribute('data-sensiolabs-de--admin-bundle--treeview-target') === 'item') {
                    parent.classList.add('is-toggled');
                    const submenu = parent.querySelector('[data-sensiolabs-de--admin-bundle--treeview-target="submenu"]');
                    if (submenu) {
                        submenu.classList.remove('hidden');
                    }
                }
                parent = parent.parentElement;
            }
        });
    }
}
