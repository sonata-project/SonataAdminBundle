/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Config from './core/config.js';
import Translation from './core/translation.js';

const Admin = {
    /**
     * This function must be called when an ajax call is done, to ensure
     * the retrieved html is properly setup
     *
     * @param subject
     */
    shared_setup(subject) {
        Admin.log('[core|shared_setup] Register services on', subject);
        Admin.setup_select2(subject);
        Admin.setup_checkbox_range_selection(subject);
        Admin.setup_xeditable(subject);
        Admin.setup_inline_form_errors(subject);
        Admin.setup_tree_view(subject);
    },

    get_config(key) {
        return Config.param(key);
    },

    get_translations(key) {
        return Translation.trans(key);
    },

    setup_list_modal(modal) {
        Admin.log('[core|setup_list_modal] configure modal on', modal);

        const modalDialog = modal.querySelector('.modal-content');
        if (modalDialog) {
            modalDialog.style.maxWidth = '90%';
            modalDialog.style.width = '90%';
        }

        const modalBody = modal.querySelector('.modal-body');
        if (modalBody) {
            modalBody.style.maxHeight = '80vh';
            modalBody.style.overflowY = 'auto';
        }

        modal.dispatchEvent(new CustomEvent('sensiolabs-admin-setup-list-modal'));
    },

    /**
     * Setup Tom Select on select elements
     */
    setup_select2(subject) {
        if (!Admin.get_config('USE_SELECT2')) {
            return;
        }

        Admin.log('[core|setup_select2] configure Tom Select on', subject);

        // Check if TomSelect is available
        if (typeof TomSelect === 'undefined') {
            Admin.log('[core|setup_select2] TomSelect not available, skipping');
            return;
        }

        const container = subject === document ? document : subject;
        const selects = container.querySelectorAll('select:not([data-sensiolabs-select2="false"]):not(.tomselected)');

        selects.forEach((select) => {
            // Skip if already initialized
            if (select.tomselect) {
                return;
            }

            const options = {
                plugins: [],
                allowEmptyOption: true,
                create: select.dataset.sensiolabsSelect2AllowTags === 'true',
                maxItems: select.multiple ? (select.dataset.sensiolabsSelect2MaximumSelectionLength || null) : 1,
            };

            // Add clear button plugin if allowed
            if (
                select.querySelector('option[value=""]') ||
                (select.dataset.placeholder && select.dataset.placeholder.length) ||
                select.dataset.sensiolabsSelect2AllowClear === 'true'
            ) {
                options.plugins.push('clear_button');
            }

            // Add remove button for multiple selects
            if (select.multiple) {
                options.plugins.push('remove_button');
            }

            // Add dropdown input for searching
            if (!select.dataset.sensiolabsSelect2MinimumResultsForSearch ||
                parseInt(select.dataset.sensiolabsSelect2MinimumResultsForSearch, 10) <= select.options.length) {
                options.plugins.push('dropdown_input');
            }

            new TomSelect(select, options);
        });
    },

    /**
     * Setup checkbox range selection
     *
     * Clicking on a first checkbox then another with shift + click
     * will check / uncheck all checkboxes between them
     *
     * @param {string|Object} subject The html selector or object on which function should be applied
     */
    setup_checkbox_range_selection(subject) {
        Admin.log('[core|setup_checkbox_range_selection] configure checkbox range selection on', subject);

        let previousIndex;

        const container = subject === document ? document : subject;
        const checkboxes = container.querySelectorAll('tbody input[type="checkbox"]');

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('click', (event) => {
                const input = event.target;
                const row = input.closest('tr');
                if (!row) return;

                const currentIndex = Array.from(row.parentElement.children).indexOf(row);

                if (event.shiftKey && previousIndex !== undefined && previousIndex >= 0) {
                    const isChecked = input.checked;
                    const allCheckboxes = container.querySelectorAll('tbody input[type="checkbox"]');

                    allCheckboxes.forEach((cb, index) => {
                        if (
                            (index > previousIndex && index < currentIndex) ||
                            (index > currentIndex && index < previousIndex)
                        ) {
                            cb.checked = isChecked;
                        }
                    });
                }

                previousIndex = currentIndex;
            });
        });
    },

    /**
     * Setup x-editable (inline editing)
     * Note: x-editable requires jQuery, so this is now a no-op
     * Consider using a vanilla JS alternative or Stimulus controller
     */
    setup_xeditable(subject) {
        Admin.log('[core|setup_xeditable] x-editable requires jQuery, skipping. Consider using inline editing via Stimulus.');
    },

    /**
     * Render log message
     * @param mixed
     */
    log(...args) {
        if (!Admin.get_config('DEBUG')) {
            return;
        }

        const msg = `[SensioLabs.Admin] ${Array.prototype.join.call(args, ', ')}`;
        if (window.console && window.console.log) {
            window.console.log(msg);
        }
    },

    setup_inline_form_errors(subject) {
        Admin.log('[core|setup_inline_form_errors] show first tab with errors', subject);

        const deleteCheckboxSelector = '.sonata-ba-field-inline-table [id$="_delete"][type="checkbox"]';

        const container = subject === document ? document : subject;
        const checkboxes = container.querySelectorAll(deleteCheckboxSelector);

        checkboxes.forEach((checkbox) => {
            Admin.switch_inline_form_errors(checkbox);
        });

        container.addEventListener('change', (event) => {
            if (event.target.matches(deleteCheckboxSelector)) {
                Admin.switch_inline_form_errors(event.target);
            }
        });
    },

    /**
     * Disable inline form errors when the row is marked for deletion
     */
    switch_inline_form_errors(checkbox) {
        Admin.log('[core|switch_inline_form_errors] switch_inline_form_errors', checkbox);

        const row = checkbox.closest('.sonata-ba-field-inline-table');
        if (!row) return;

        const errors = row.querySelectorAll('.sonata-ba-field-error-messages');

        if (checkbox.checked) {
            row.querySelectorAll('[required]').forEach((el) => {
                el.removeAttribute('required');
                el.dataset.required = 'required';
            });
            errors.forEach((el) => el.classList.add('hidden'));
        } else {
            row.querySelectorAll('[data-required]').forEach((el) => {
                el.setAttribute('required', 'required');
            });
            errors.forEach((el) => el.classList.remove('hidden'));
        }
    },

    setup_tree_view(subject) {
        Admin.log('[core|setup_tree_view] setup tree view', subject);
        // TreeView is now vanilla JS and auto-initializes
    },

    /** Return the width for select elements */
    get_select2_width(element) {
        const style = element.getAttribute('style');

        if (style) {
            const widthMatch = style.match(/width:\s*(auto|[\d.]+(?:px|em|ex|%|in|cm|mm|pt|pc))/i);
            if (widthMatch) {
                return widthMatch[1];
            }
        }

        const computedWidth = window.getComputedStyle(element).width;
        if (computedWidth && computedWidth.indexOf('%') > 0) {
            return computedWidth;
        }

        return '100%';
    },

    /**
     * Setup sortable select with Tom Select
     */
    setup_sortable_select2(subject, data, customOptions = {}) {
        if (typeof TomSelect === 'undefined') {
            Admin.log('[core|setup_sortable_select2] TomSelect not available');
            return;
        }

        const element = typeof subject === 'string' ? document.querySelector(subject) : subject;
        if (!element) return;

        const selectedIds = element.value ? element.value.split(',') : [];
        const options = [];

        data.forEach((item) => {
            options.push({
                value: item.data,
                text: item.label,
            });
        });

        const tomSelectOptions = {
            plugins: ['remove_button', 'drag_drop'],
            options: options,
            items: selectedIds,
            maxItems: null,
            ...customOptions,
        };

        const ts = new TomSelect(element, tomSelectOptions);

        // Handle form submission
        const form = element.closest('form');
        if (form) {
            form.addEventListener('submit', () => {
                const values = ts.getValue();
                if (values && values.length > 0) {
                    const baseName = element.name.substring(0, element.name.length - 1);
                    const valuesArray = Array.isArray(values) ? values : [values];

                    valuesArray.forEach((value, i) => {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = `${baseName}${i}]`;
                        hidden.value = value;
                        form.appendChild(hidden);
                    });
                }

                element.remove();
            });
        }
    },

    /**
     * Make an AJAX request (replaces jQuery.ajax)
     */
    async ajax(options) {
        const {
            url,
            method = 'GET',
            data = null,
            headers = {},
            dataType = 'json',
        } = options;

        const fetchOptions = {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...headers,
            },
        };

        if (data && method !== 'GET') {
            if (data instanceof FormData) {
                fetchOptions.body = data;
            } else if (typeof data === 'object') {
                fetchOptions.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                fetchOptions.body = new URLSearchParams(data).toString();
            } else {
                fetchOptions.body = data;
            }
        }

        let finalUrl = url;
        if (data && method === 'GET') {
            const params = new URLSearchParams(data).toString();
            finalUrl = url + (url.includes('?') ? '&' : '?') + params;
        }

        try {
            const response = await fetch(finalUrl, fetchOptions);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            if (dataType === 'json') {
                return await response.json();
            } else if (dataType === 'html' || dataType === 'text') {
                return await response.text();
            }

            return response;
        } catch (error) {
            Admin.log('[core|ajax] Error:', error);
            throw error;
        }
    },

    /**
     * Submit a form via AJAX (replaces jQuery.ajaxSubmit)
     */
    async ajaxSubmit(form, options = {}) {
        const formData = new FormData(form);

        // Add extra data
        if (options.data) {
            Object.entries(options.data).forEach(([key, value]) => {
                formData.append(key, value);
            });
        }

        const url = options.url || form.action;
        const method = options.type || options.method || form.method || 'POST';

        const fetchOptions = {
            method: method.toUpperCase(),
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
        };

        // Don't set Content-Type for FormData - browser will set it with boundary
        if (options.headers?.Accept) {
            fetchOptions.headers.Accept = options.headers.Accept;
        }

        try {
            const response = await fetch(url, fetchOptions);

            let responseData;
            const contentType = response.headers.get('Content-Type') || '';

            if (options.dataType === 'json' || contentType.includes('application/json')) {
                responseData = await response.json();
            } else {
                responseData = await response.text();
            }

            if (response.ok) {
                if (options.success) {
                    options.success(responseData, response.statusText, response);
                }
            } else {
                if (options.error) {
                    options.error(response, response.statusText, responseData);
                }
            }

            return responseData;
        } catch (error) {
            Admin.log('[core|ajaxSubmit] Error:', error);
            if (options.error) {
                options.error(null, 'error', error);
            }
            throw error;
        }
    },

    /**
     * Show a modal dialog
     */
    showModal(modalElement) {
        if (!modalElement) return;

        modalElement.classList.remove('hidden');
        modalElement.classList.add('flex');
        modalElement.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        // Focus first focusable element
        const focusable = modalElement.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) {
            focusable.focus();
        }
    },

    /**
     * Hide a modal dialog
     */
    hideModal(modalElement) {
        if (!modalElement) return;

        modalElement.classList.add('hidden');
        modalElement.classList.remove('flex');
        modalElement.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    },

    /**
     * Create HTML element from string
     */
    createElementFromHTML(htmlString) {
        const template = document.createElement('template');
        template.innerHTML = htmlString.trim();
        return template.content.firstChild;
    },
};

export default Admin;
