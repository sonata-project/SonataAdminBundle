/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const pluginName = 'treeView';
const defaults = {
  togglersAttribute: '[data-treeview-toggler]',
  toggledState: 'is-toggled',
  activeState: 'is-active',
  defaultToggled: '[data-treeview-toggled]',
  instanceAttribute: 'data-treeview-instance',
};

function TreeView(element, options) {
  this.element = element;
  this.options = jQuery.extend({}, defaults, options);
  this.defaults = defaults;
  this.name = pluginName;
  this.init();
}

TreeView.prototype = {

  /**
   * Constructor
   */
  init() {
    this.setElements();
    this.setEvents();
    this.setAttributes();
    this.showActiveElement();
    this.showToggledElements();
  },

  /**
   * Cache DOM elements to limit DOM parsing
   */
  setElements() {
    this.$element = jQuery(this.element);
    this.$togglers = this.$element.find(this.options.togglersAttribute);
    this.$defaultToggled = this.$element.find(this.options.defaultToggled);
  },

  /**
   * Set some attrs
   */
  setAttributes() {
    this.$element.attr(this.options.instanceAttribute, true);
  },

  /**
   * Set events and delegates
   */
  setEvents() {
    this.$togglers.on('click', this.toggle.bind(this));
  },

  /**
   * Toggle an item
   */
  toggle(event) {
    const $target = jQuery(event.currentTarget);
    const $parent = $target.parent();
    $parent.toggleClass(this.options.toggledState);
    $parent.next('ul').slideToggle();
  },

  /**
   * Show active element
   */
  showActiveElement() {
    const parents = `[${this.options.instanceAttribute}] ul, [${this.options.instanceAttribute}]`;
    const $activeElement = this.$element.find(`.${this.options.activeState}`);
    const $parents = $activeElement.parents(parents);
    $parents.show();
    $parents.prev().addClass(this.options.toggledState);
  },

  /**
   * Default visible elements
   */
  showToggledElements() {
    this.$defaultToggled.addClass(this.options.toggledState);
    this.$defaultToggled.next('ul').show();
  },
};

// A really lightweight plugin wrapper around the constructor,
// preventing against multiple instantiations
jQuery.fn[pluginName] = function plugin(options) {
  return this.each(function plugins() {
    if (!jQuery.data(this, `plugin_${pluginName}`)) {
      jQuery.data(this, `plugin_${pluginName}`, new TreeView(this, options));
    }
  });
};
