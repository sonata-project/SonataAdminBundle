import $ from 'jquery';

import fetchHTML from 'sonata/util/fetchHTML';
import {createAlert} from 'sonata/ui/alert';
import {createSpinner, createSpinnerOverlay} from 'sonata/ui/spinner';


function showDetailsSpinner ($details) {
    if (!$details.children().length) {
        return $details.append(createSpinner());
    }
    return $details.find('.box').append(createSpinnerOverlay()).end();
}


$(document).on('click', 'a[data-action="view-revision"], a[data-action="compare-revision"]', event => {
    event.preventDefault();
    event.stopPropagation();

    const $revisionLink = $(event.currentTarget);
    const $container = $revisionLink.closest('.sonata-ba-revisions');
    const $details = $container.find('.sonata-ba-revisions__details');
    const action = $revisionLink.data('action');

    if (action === 'view-revision') {
        $container
            .find('sonata-ba-revisions__revision--is-current')
            .removeClass('sonata-ba-revisions__revision--is-current')
        ;
    }

    showDetailsSpinner($details);
    fetchHTML($revisionLink.attr('href'))
        .then(html => $details.html(html))
        .catch(({statusText}) => $details.empty().append(createAlert(statusText)));
});

