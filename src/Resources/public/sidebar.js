/*

 This file is part of the Sonata package.

 (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

 */

jQuery(document).ready(function(){
    $('.sidebar-toggle').click(function(){
        if (~document.cookie.indexOf('sonata_sidebar_hide=1')) {
            return document.cookie = 'sonata_sidebar_hide=0;path=/';
        }

        document.cookie = 'sonata_sidebar_hide=1;path=/';
    });
});
