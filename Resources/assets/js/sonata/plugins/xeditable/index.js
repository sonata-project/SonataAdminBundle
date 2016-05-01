import $ from 'jquery';

import './xeditable.css';

export default function setupXEditable ($subject) {
    $subject.find('.x-editable').editable({
        emptyclass: 'editable-empty btn btn-sm btn-default',
        emptytext: '<i class="fa fa-pencil"></i>',
        container: 'body',
        placement: 'auto',
        success (response) {
            if (response.status === 'KO') {
                return response.message;
            }
            const $editable = $(this);
            const $parent = $editable.parent();
            // we make sure to include the root node here, because templates might decide to return no wrapper,
            // e.g. for flat list fields. This ensures we update only the editable widget.
            const $newField = $(response.content).find('.x-editable').addBack().filter('.x-editable');

            $editable.replaceWith($newField);
            setupXEditable($parent);
        },
    });
}
