import $ from 'jquery';

import config from 'sonata/config';
import i18n from 'sonata/i18n';
import * as formHelpers from 'sonata/form/util';
import * as listHelpers from 'sonata/list/util';
import Dialog from 'sonata/ui/dialog';

// plugins
import 'sonata/plugins';

// Components
import 'sonata/list';
import 'sonata/form';


/**
 * @namespace
 */
const {Sonata = {
    debug: true,
}} = window;


//
// Expose the public API through the global Sonata namespace
// --------------------------------------------------------------------------------------------------------------------

/**
 * @property {object} Sonata.config
 */
Sonata.config = config;


/**
 * @property {object} Sonata.i18n
 */
Sonata.i18n = i18n;


/**
 * @namespace
 */
Sonata.Admin = {
    ...formHelpers,
    ...listHelpers,
    createDialog: title => new Dialog(title),
};

window.Sonata = Sonata;


//
// Bootstrap
// --------------------------------------------------------------------------------------------------------------------


/**
 * Bootstrap on initial page load.
 */
$(() => {
    $('html').removeClass('no-js');
});

if (process.env.NODE_ENV !== 'production') {
    // eslint-disable-next-line no-console
    const logEvent = ({type}, ...args) => console.log(type, ...args);
    [
        'sonata:association-list-request',
        'sonata:association-show-request',
        'sonata:association-create-request',
        'sonata:association-edit-request',
        'sonata:association-delete-request',
        'sonata:association-list-response',
        'sonata:association-show-response',
        'sonata:association-create-response',
        'sonata:association-edit-response',
        'sonata:association-delete-response',
        //
        'sonata:association-update',
        'sonata:collection-item-add',
        'sonata:collection-item-added',
        'sonata:native-collection-item-add',
        'sonata:native-collection-item-added',
        'sonata:native-collection-item-delete',
        'sonata:native-collection-item-deleted',
        'sonata:domready',
    ].forEach(event => $(document).on(event, logEvent));
}
