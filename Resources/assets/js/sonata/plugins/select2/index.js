import $ from 'jquery';

import curry from 'sonata/util/curry';
import containsSelector from 'sonata/util/containsSelector';

import './select2.css';


const {map} = Array.prototype;

const containsPlaceholderOption = containsSelector('option[value=""]');

/**
 * @constant
 * @type {RegExp}
 */
const CSS_WIDTH_RX = /width:(auto|(([-+]?([0-9]*\.)?[0-9]+)(px|em|ex|%|in|cm|mm|pt|pc)))/i;

/**
 * Return the width for simple and sortable select2 element
 *
 * @param {jQuery} $element The select2 container.
 * @returns {string}
 */
function getSelect2Width ($element) {
    // this code is an adaptation of select2 code (initContainerWidth function)
    let style = $element.attr('style');
    if (style) {
        const rules = style.split(';');
        for (let i = 0, l = rules.length; i < l; i++) {
            const matches = rules[i].replace(/\s/g, '').match(CSS_WIDTH_RX);
            if (matches && matches.length) {
                return matches[1];
            }
        }
    }

    style = $element.css('width');
    if (style.indexOf('%') > 0) {
        return style;
    }

    return '100%';
}

/**
 * Initializes a select2 widget.
 *
 * @param {jQuery} $subject The input/select element.
 */
function createSelect2 ($subject) {
    let allowClearEnabled = false;
    const popover = $subject.data('popover');

    $subject.removeClass('form-control');

    if (containsPlaceholderOption($subject) || $subject.data('sonataSelect2AllowClear')) {
        allowClearEnabled = true;
    }

    $subject.select2({
        width () {
            return getSelect2Width(this.element);
        },
        dropdownAutoWidth: true,
        minimumResultsForSearch: 10,
        allowClear: allowClearEnabled,
    });

    if (popover) {
        $subject.select2('container').popover(popover.options);
    }
}

/**
 * Convert a choice from Sonata to select2 format
 *
 * @returns {{id: string, label: string}}
 */
const choiceToSelect2 = ({data, label}) => ({id: data, text: label});

const valueToInput = curry(
    (name, index, value) => $('<input type="hidden"/>').attr('name', `${name}${index}]`).val(value)
);

/**
 * Creates a sortable select2 widget.
 * Choices are passed to the hidden input via data-select2-choices attribute.
 *
 * @param {jQuery} $subject The input/select element.
 */
function createSortableSelect2 ($subject) {
    const choices = map.call($subject.data('select2Choices'), choiceToSelect2);

    $subject.select2({
        width () {
            return getSelect2Width(this.element);
        },
        dropdownAutoWidth: true,
        data: choices,
        multiple: true,
    });

    $subject.select2('container').find('ul.select2-choices').sortable({
        containment: 'parent',
        start: () => $subject.select2('onSortStart'),
        update: () => $subject.select2('onSortEnd'),
    });

    // On form submit, transform value to match what is expected by server
    const $form = $subject.closest('form');
    $form.on('submit', () => {
        const value = $subject.val().trim();
        if (value) {
            const baseName = $subject.attr('name').slice(0, -1);
            const inputs = value.split(',').map(valueToInput(baseName));
            $form.append(inputs);
        }
        $subject.remove();
    });
}


export function setupSelect2 ($subject) {
    $subject
        .find('select:not([data-sonata-select2="false"])')
        .each((i, select) => createSelect2($(select)))
    ;
}

export function setupSortableSelect2 ($subject) {
    $subject
        .find('.sonata-select2-sortable')
        .each((i, sortable) => createSortableSelect2($(sortable)))
    ;
}

/**
 * Initializes all select2 elements in $subject.
 *
 * Exported for BC.
 *
 * @private
 * @param {jQuery} $subject
 */
export default $subject => {
    setupSelect2($subject);
    setupSortableSelect2($subject);
};
