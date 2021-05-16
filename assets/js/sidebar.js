/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery(() => {
  jQuery('.sidebar-toggle').on('click', () => {
    if (document.cookie.includes('sonata_sidebar_hide=1')) {
      document.cookie = 'sonata_sidebar_hide=0;path=/';

      return;
    }

    document.cookie = 'sonata_sidebar_hide=1;path=/';
  });
});
