import $ from 'jquery';

/**
 * On filter form submit, find all filters not internally hidden nor currently visible,
 * and clear their input value.
 *
 * @param {jQuery} $filterForm
 */
function clearHiddenFilters ($filterForm) {
    $filterForm.find('.sonata-filters__filter:not(.hidden):not(.in) :input').val('');
}

/**
 * Toggles the visibility of a filter field.
 *
 * @param {string} filterId
 */
function toggleFilterVisibility (filterId) {
    const $filter = $(`#${filterId}`);
    // Be careful, there are TWO togglers for each filter.
    const $toggler = $(`[aria-controls="${filterId}"]`);
    const numVisible = $toggler.closest('.dropdown-menu').find('[aria-expanded="true"]').length;

    if (!$filter.hasClass('in')) {
        $toggler.find('i.fa-square-o').attr('class', 'fa fa-check-square-o');
        if (numVisible === 0) {
            // filter box was empty, now show it
            $filter.closest('.sonata-filters-box').collapse('show');
        }
    } else {
        $toggler.find('i.fa-check-square-o').attr('class', 'fa fa-square-o');
        if (numVisible === 1) {
            // filter box will be empty, now hide it
            $filter.closest('.sonata-filters-box').collapse('hide');
        }
    }
}

function toggleAdvancedFilters () {
    $('.advanced-filter').toggleClass('hidden');
}

function hideEmptyAdvancedFilters () {
    const $visibleEmptyFilters = $('.advanced-filter:not(.hidden) :input').filter((i, input) => $(input).val());
    if (!$visibleEmptyFilters.length) {
        $('.advanced-filter').addClass('hidden');
    }
}

$(hideEmptyAdvancedFilters);
$(document)
    .on('sonata:domready', hideEmptyAdvancedFilters)
    .on('show.bs.collapse', '.sonata-filters__filter', ({target: {id}}) => toggleFilterVisibility(id))
    .on('hide.bs.collapse', '.sonata-filters__filter', ({target: {id}}) => toggleFilterVisibility(id))
    // prevent the filter selection dropdown from closing when clicked
    .on('click', '.sonata-toggle-filter', e => e.stopPropagation())
    .on('submit', '.sonata-filter-form', ({target}) => clearHiddenFilters($(target)))
    .on('click', '[data-toggle="advanced-filter"]', event => {
        event.preventDefault();
        event.stopPropagation();
        toggleAdvancedFilters();
    })
;
