// Any SCSS/CSS you require will output into a single css file (app.css in this case)
import '../scss/app.scss';

// Require jQuery normally
import $ from 'jquery';

// Create global $ and jQuery variables to be used outside this script
// eslint-disable-next-line
global.$ = global.jQuery = $;

import 'jquery.scrollto';

// Only using sortable widget from jQuery UI library
import 'jquery-ui/ui/widget';
import 'jquery-ui/ui/widgets/sortable';
import 'bootstrap';
import 'moment';

// Eonasdan Bootstrap DateTimePicker in its version 3 does not
// provide the scss or plain css, it only provides the less version
// of its source files, that's why it is not included it via npm.
import '../vendor/bootstrap-datetimepicker.min';
import 'jquery-form';

// Boostrap 3 JavaScript for the X-editable library
import 'x-editable/dist/bootstrap3-editable/js/bootstrap-editable';

// Full version of Select2, needed because SonataAdmin needs
// compat dropdownCss and it only comes on the full version
import 'select2/dist/js/select2.full';
import 'admin-lte';
import 'icheck';
import 'jquery-slimscroll';

// jQuery Waypoints version and sticky shortcut
import 'waypoints/lib/jquery.waypoints';
import 'waypoints/lib/shortcuts/sticky';
import 'readmore-js';
import 'masonry-layout';
