import $ from 'jquery';

import curry from 'sonata/util/curry';


const DEFAULTS = {
    togglersSelector: '[data-treeview-toggler]',
    toggledClass: 'is-toggled',
    activeClass: 'is-active',
    defaultToggledSelector: '[data-treeview-toggled]',
    instanceAttribute: 'data-treeview-instance',
};


const toggleNode = curry(({toggledClass}, $item) => {
    $item.parent()
        .toggleClass(toggledClass)
        .next('ul')
            .slideToggle()
    ;
});

function showActiveNode ($container, {instanceAttribute, activeClass, toggledClass}) {
    const $activeElement = $container.find(`.${activeClass}`);
    const $parents = $activeElement.parents(`[${instanceAttribute}] ul, [${instanceAttribute}]`);
    $parents.show();
    $parents.prev().addClass(toggledClass);
}

function showToggledNodes ($container, {toggledClass, defaultToggledSelector}) {
    $container
        .find(defaultToggledSelector)
        .addClass(toggledClass)
        .next('ul')
            .show()
    ;
}


function createTreeView (element, options = {}) {
    const $element = $(element);
    options = {...DEFAULTS, ...options};
    $element.attr(options.instanceAttribute, true);

    const toggle = toggleNode(options);
    $element.on('click.sonata.treeview', options.togglersSelector, ({currentTarget}) => toggle($(currentTarget)));

    const showActive = showActiveNode.bind(null, $element, options);
    const showToggled = showToggledNodes.bind(null, $element, options);

    return {
        toggle,
        showActive,
        showToggled,
    };
}


$.fn.treeView = function (options) {
    return this.each((i, element) => {
        if (!$.data(element, 'plugin_treeView')) {
            $.data(element, 'plugin_treeView', createTreeView(element, options));
        }
    });
};
