import $ from 'jquery';

import config from 'sonata/config';
import i18n from 'sonata/i18n';

import setupSelect2 from './select2';
import setupTreeViews from './treeview';
import setupXEditable from './xeditable';
import setupConfirmExit from './confirmExit';
import setupStickyElements from './sticky';


const $doc = $(document);


$(() => {
    if (config.useStickyForms) {
        setupStickyElements($doc);
    }
    if (config.confirmExit) {
        setupConfirmExit($doc, i18n.confirmExit);
    }
    if (config.useSelect2) {
        setupSelect2($doc);
    }
    setupTreeViews($doc);
    setupXEditable($doc);
});

$doc.on('sonata:domready', event => {
    const $target = $(event.target);

    if (config.confirmExit) {
        setupConfirmExit($target, i18n.confirmExit);
    }
    if (config.useSelect2) {
        setupSelect2($target);
    }
    setupTreeViews($target);
    setupXEditable($target);
});
