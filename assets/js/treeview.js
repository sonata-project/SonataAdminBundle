/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TreeView {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            togglersAttribute: '[data-treeview-toggler]',
            toggledState: 'is-toggled',
            activeState: 'is-active',
            defaultToggled: '[data-treeview-toggled]',
            instanceAttribute: 'data-treeview-instance',
            ...options,
        };

        this.init();
    }

    init() {
        this.setElements();
        this.setEvents();
        this.setAttributes();
        this.showActiveElement();
        this.showToggledElements();
    }

    setElements() {
        this.togglers = this.element.querySelectorAll(this.options.togglersAttribute);
        this.defaultToggled = this.element.querySelectorAll(this.options.defaultToggled);
    }

    setAttributes() {
        this.element.setAttribute(this.options.instanceAttribute, 'true');
    }

    setEvents() {
        this.togglers.forEach((toggler) => {
            toggler.addEventListener('click', (event) => this.toggle(event));
        });
    }

    toggle(event) {
        event.preventDefault();
        const target = event.currentTarget;
        const parent = target.parentElement;
        const submenu = parent.querySelector('ul');

        parent.classList.toggle(this.options.toggledState);

        if (submenu) {
            if (submenu.classList.contains('hidden') || !submenu.style.display || submenu.style.display === 'none') {
                this.slideDown(submenu);
            } else {
                this.slideUp(submenu);
            }
        }
    }

    slideDown(element) {
        element.classList.remove('hidden');
        element.style.display = 'block';
        element.style.height = '0';
        element.style.overflow = 'hidden';
        element.style.transition = 'height 0.3s ease-out';

        requestAnimationFrame(() => {
            element.style.height = element.scrollHeight + 'px';
        });

        element.addEventListener('transitionend', function handler() {
            element.style.height = '';
            element.style.overflow = '';
            element.style.transition = '';
            element.removeEventListener('transitionend', handler);
        });
    }

    slideUp(element) {
        element.style.height = element.scrollHeight + 'px';
        element.style.overflow = 'hidden';
        element.style.transition = 'height 0.3s ease-out';

        requestAnimationFrame(() => {
            element.style.height = '0';
        });

        element.addEventListener('transitionend', function handler() {
            element.style.display = 'none';
            element.classList.add('hidden');
            element.style.height = '';
            element.style.overflow = '';
            element.style.transition = '';
            element.removeEventListener('transitionend', handler);
        });
    }

    showActiveElement() {
        const activeElement = this.element.querySelector(`.${this.options.activeState}`);
        if (!activeElement) return;

        let parent = activeElement.parentElement;
        while (parent && parent !== this.element) {
            if (parent.tagName === 'UL') {
                parent.classList.remove('hidden');
                parent.style.display = 'block';
                const prevSibling = parent.previousElementSibling;
                if (prevSibling) {
                    prevSibling.classList.add(this.options.toggledState);
                }
            }
            parent = parent.parentElement;
        }
    }

    showToggledElements() {
        this.defaultToggled.forEach((element) => {
            element.classList.add(this.options.toggledState);
            const submenu = element.querySelector('ul');
            if (submenu) {
                submenu.classList.remove('hidden');
                submenu.style.display = 'block';
            }
        });
    }
}

// Initialize treeviews on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('ul.js-treeview').forEach((element) => {
        if (!element.hasAttribute('data-treeview-instance')) {
            new TreeView(element);
        }
    });
});

// Also handle dynamically added treeviews
document.addEventListener('sensiolabs-admin-append-form-element', (event) => {
    const target = event.target || document;
    target.querySelectorAll('ul.js-treeview').forEach((element) => {
        if (!element.hasAttribute('data-treeview-instance')) {
            new TreeView(element);
        }
    });
});

export default TreeView;
