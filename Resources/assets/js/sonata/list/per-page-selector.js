import $ from 'jquery';

/**
 * Changes the pager's number of items per page according to the select box value.
 *
 * @param {jQuery} $perPageSelect
 */
function changeNumberOfListItemsPerPage ($perPageSelect) {
    $('input[type="submit"]').hide();
    window.top.location.href = $perPageSelect.val();
}

$(document).on('change', 'select.per-page', ({target}) => changeNumberOfListItemsPerPage($(target)));
