/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

document.addEventListener('DOMContentLoaded', () => {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const body = document.body;

    if (!sidebarToggle) return;

    sidebarToggle.addEventListener('click', (event) => {
        event.preventDefault();

        // Toggle sidebar visibility
        sidebar?.classList.toggle('open');
        sidebar?.classList.toggle('-translate-x-full');
        body.classList.toggle('sidebar-collapsed');

        // Store preference in cookie
        if (document.cookie.includes('sensiolabs_sidebar_hide=1')) {
            document.cookie = 'sensiolabs_sidebar_hide=0;path=/;SameSite=Lax';
        } else {
            document.cookie = 'sensiolabs_sidebar_hide=1;path=/;SameSite=Lax';
        }
    });

    // Handle responsive sidebar
    const handleResize = () => {
        if (window.innerWidth < 1024) {
            sidebar?.classList.add('-translate-x-full');
            sidebar?.classList.remove('open');
        } else if (!document.cookie.includes('sensiolabs_sidebar_hide=1')) {
            sidebar?.classList.remove('-translate-x-full');
        }
    };

    window.addEventListener('resize', handleResize);
    handleResize();
});
