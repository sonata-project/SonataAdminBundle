/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Import Admin module
import Admin from './js/admin.js';
import TreeView from './js/treeview.js';

// Export for users who want to use Admin utilities
export { Admin, TreeView };

// Export default for simple imports
export default Admin;

// Expose Admin globally for backwards compatibility
if (typeof window !== 'undefined') {
    window.Admin = Admin;
}

// Initialize Admin on DOM ready
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        document.documentElement.classList.remove('no-js');
        Admin.shared_setup(document);
    });

    // Handle dynamic content added via AJAX
    document.addEventListener('sensiolabs-admin-append-form-element', (event) => {
        Admin.shared_setup(event.target);
    });
}
