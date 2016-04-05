import $ from 'jquery';

import '../ui/treeview';

export default subject => $(subject).find('ul.js-treeview').treeView();
