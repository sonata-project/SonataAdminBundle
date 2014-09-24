/**
 *
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

;(function ( $, window, document, undefined ) {

    var pluginName = 'treeView',
        defaultRegistry = '.js-treeview',
        defaults = {
            togglersAttribute: '[data-treeview-toggler]',
            toggledState: 'is-toggled',
            activeState: 'is-active',
            defaultToggled: '[data-treeview-toggled]',
            instanceAttribute: 'data-treeview-instance'
        };

    function TreeView( element, options ) {
        this.element = element;
        this.options = $.extend({}, defaults, options) ;
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    TreeView.prototype = {

        /**
         * Constructor
         */
        init: function() {
            this.setElements();
            this.setEvents();
            this.setAttributes();
            this.showActiveElement();
            this.showToggledElements();
        },

        /**
         * Cache DOM elements to limit DOM parsing
         */
        setElements: function() {
            this.$element = $(this.element);
            this.$togglers = this.$element.find(this.options.togglersAttribute);
            this.$defaultToggled = this.$element.find(this.options.defaultToggled);
        },

        /**
         * Set some attrs
         */
        setAttributes: function() {
            this.$element.attr(this.options.instanceAttribute, true);
        },

        /**
         * Set events and delegates
         */
        setEvents: function() {
            this.$togglers.on('click', $.proxy(this.toggle, this));
        },

        /**
         * Toggle an item
         */
        toggle: function(ev) {
            var $target = $(ev.currentTarget),
                $parent = $target.parent();
            $parent.toggleClass(this.options.toggledState);
            $parent.next('ul').slideToggle();
        },

        /**
         * Show active element
         */
        showActiveElement: function() {
            var parents = '[' + this.options.instanceAttribute + '] ul, [' + this.options.instanceAttribute + ']';
            var $activeElement = this.$element.find('.' + this.options.activeState);
            var $parents = $activeElement.parents(parents);
            $parents.show();
            $parents.prev().addClass(this.options.toggledState);
        },

        /**
         * Default visible elements
         */
        showToggledElements: function() {
            this.$defaultToggled.addClass(this.options.toggledState);
            this.$defaultToggled.next('ul').show();
        }

    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new TreeView(this, options));
            }
        });
    };
})( jQuery, window, document );