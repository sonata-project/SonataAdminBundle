/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Any SCSS/CSS you require will output into a single css file (app.css in this case)
import '../scss/app.scss';

// Require jQuery normally
import $ from 'jquery';

// jQuery scrollTo is not directly used in SonataAdmin
// but it is used on SonataPage, SonataArticle and SonataDashboard
import 'jquery.scrollto';

// Only using sortable widget from jQuery UI library
import 'jquery-ui/ui/widget.js';
import 'jquery-ui/ui/widgets/sortable.js';
import 'bootstrap';

import 'jquery-form';

// Boostrap 3 JavaScript for the X-editable library
import 'x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js';

// Full version of Select2, needed because SonataAdmin needs
// compat dropdownCss and it only comes on the full version
import 'select2/dist/js/select2.full.js';
import 'admin-lte';
import 'icheck';

// jQuery SlimScroll is used in AdminLTE v2
import 'jquery-slimscroll';
import 'masonry-layout';

// SonataAdmin custom scripts
import './admin.js';
import './treeview.js';
import './sidebar.js';
import './base.js';

import * as stimulus from '@hotwired/stimulus';

import sonataApplication from './stimulus.js';

// Create global variables to be used outside this script
window.$ = $;
window.jQuery = $;
window.stimulus = stimulus;
window.sonataApplication = sonataApplication;
