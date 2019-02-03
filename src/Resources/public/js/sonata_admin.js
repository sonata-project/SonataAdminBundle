// Globalize jquery
import jquery from "jquery"

window.$ = jquery;
window.jQuery = jquery;
global.$ = jquery;
global.jQuery = jquery;

// Styles
import "../scss/sonata_admin.scss"

// Vendors
import "bootstrap"
import "bootstrap-datepicker"
import "eonasdan-bootstrap-datetimepicker"
import "admin-lte"

import "jquery-form"
import "jquery-ui"
import "jquery.scrollto"
import "jquery-slimscroll"
import "x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min"
import "icheck"
import "waypoints/lib/jquery.waypoints"
import "waypoints/lib/shortcuts/sticky.min"
import "select2"

// Loading langugage files for select2
let language = window.navigator.userLanguage || window.navigator.language;
language = language.split("-")[0];
import('select2/select2_locale_' + language + '.js')
    .catch('failed to import locale component for select2')

// Configure momentJS locale
import("moment").then(moment => {
    moment.locale(language)
}).catch('failed to configure momentJS locale')

// Load momentJS locale component
import("moment/locale/" + language + '.js')
    .catch('failed to load language component for momentJS')

import "readmore-js"
import "masonry-layout"

// Custom
import "./Admin"
import "./sidebar"
import "./jquery.confirmExit"
import "./treeview"
