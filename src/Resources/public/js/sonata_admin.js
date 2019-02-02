let jquery = require("jquery");
window.$ = window.jQuery = jquery
global.$ = global.jQuery = jquery;

// Styles
import "../scss/sonata_admin.scss"

//Vendors
import "admin-lte"
import "bootstrap"
import "jquery-form"
import "jquery-ui"
import "jquery.scrollto"
import "jquery-slimscroll"
import "x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min"
import "icheck"
import "waypoints/lib/jquery.waypoints"
import "waypoints/lib/shortcuts/sticky.min"
import "moment"
import "select2"
import "readmore-js"
import "masonry-layout"
import "bootstrap-datepicker"

// Custom
import "./Admin"
import "./sidebar"
import "./jquery.confirmExit"
import "./treeview"
