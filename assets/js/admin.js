/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import log from './admin-log';
import setupICheck from './admin-setup-icheck';
import addFlashMessageListener from './admin-flash-message';
import { getConfig, getTranslation, read } from './admin-config';
import { select2Width, setupSelect2, setupSortableSelect2 } from './admin-setup-select2';
import { setupCollectionButtons, setupCollectionCounter } from './admin-setup-collection';

const Admin = {
  /**
   * This function must be called when an ajax call is done, to ensure
   * the retrieved html is properly setup
   *
   * @param subject
   */
  shared_setup(subject) {
    read();
    log('[core|shared_setup] Register services on', subject);
    Admin.setup_ie10_polyfill();
    Admin.set_object_field_value(subject);
    Admin.add_filters(subject);
    setupSelect2(subject);
    setupICheck(subject);
    Admin.setup_checkbox_range_selection(subject);
    Admin.setup_xeditable(subject);
    Admin.setup_form_tabs_for_errors(subject);
    Admin.setup_inline_form_errors(subject);
    Admin.setup_tree_view(subject);
    setupCollectionCounter(subject);
    Admin.setup_sticky_elements(subject);
    Admin.setup_readmore_elements(subject);
    Admin.setup_form_submit(subject);

    // Admin.setup_list_modal(subject);
  },
  setup_ie10_polyfill() {
    // http://getbootstrap.com/getting-started/#support-ie10-width
    if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
      const msViewportStyle = document.createElement('style');
      msViewportStyle.appendChild(document.createTextNode('@-ms-viewport{width:auto!important}'));
      document.querySelector('head').appendChild(msViewportStyle);
    }
  },
  read_config() {
    read();
  },
  get_config(key) {
    return getConfig(key);
  },
  get_translations(key) {
    return getTranslation(key);
  },
  setup_list_modal(modal) {
    log('[core|setup_list_modal] configure modal on', modal);
    // this will force relation modal to open list of entity in a wider modal
    // to improve readability
    jQuery('div.modal-dialog', modal).css({
      width: '90%', // choose your width
      height: '85%',
      padding: 0,
    });
    jQuery('div.modal-content', modal).css({
      'border-radius': '0',
      height: '100%',
      padding: 0,
    });
    jQuery('.modal-body', modal).css({
      width: 'auto',
      height: '90%',
      padding: 15,
      overflow: 'auto',
    });

    jQuery(modal).trigger('sonata-admin-setup-list-modal');
  },
  setup_select2(subject) {
    setupSelect2(subject);
  },
  setup_icheck(subject) {
    setupICheck(subject);
  },
  /**
   * Setup checkbox range selection
   *
   * Clicking on a first checkbox then another with shift + click
   * will check / uncheck all checkboxes between them
   *
   * @param {string|Object} subject The html selector or object on which function should be applied
   */
  setup_checkbox_range_selection(subject) {
    log('[core|setup_checkbox_range_selection] configure checkbox range selection on', subject);

    let previousIndex;
    const useICheck = getConfig('USE_ICHECK');

    // When a checkbox or an iCheck helper is clicked
    jQuery('tbody input[type="checkbox"], tbody .iCheck-helper', subject).on('click', (event) => {
      let input;

      if (useICheck) {
        input = jQuery(event.target).prev('input[type="checkbox"]');
      } else {
        input = jQuery(event.target);
      }

      if (input.length) {
        const currentIndex = input.closest('tr').index();

        if (event.shiftKey && previousIndex >= 0) {
          const isChecked = jQuery(`tbody input[type="checkbox"]:nth(${currentIndex})`, subject).prop('checked');

          // Check all checkbox between previous and current one clicked
          jQuery('tbody input[type="checkbox"]', subject).each((index, element) => {
            if ((index > previousIndex && index < currentIndex)
              || (indexedDB > currentIndex && index < previousIndex)) {
              if (useICheck) {
                jQuery(element).iCheck(isChecked ? 'check' : 'uncheck');

                return;
              }

              jQuery(element).prop('checked', isChecked);
            }
          });
        }

        previousIndex = currentIndex;
      }
    });
  },

  setup_xeditable(subject) {
    log('[core|setup_xeditable] configure xeditable on', subject);
    jQuery('.x-editable', subject).editable({
      emptyclass: 'editable-empty btn btn-sm btn-default',
      emptytext: '<i class="fas fa-pencil-alt"></i>',
      container: 'body',
      placement: 'auto',
      success(response) {
        const html = jQuery(response);
        Admin.setup_xeditable(html);
        jQuery(this)
          .closest('td')
          .replaceWith(html);
      },
      error(xhr) {
        // On some error responses, we return JSON.
        if (xhr.getResponseHeader('Content-Type') === 'application/json') {
          return JSON.parse(xhr.responseText);
        }

        return xhr.responseText;
      },
    });
  },

  log(...args) {
    log(...args);
  },

  stopEvent(event) {
    event.preventDefault();

    return event.target;
  },

  add_filters(subject) {
    log('[core|add_filters] configure filters on', subject);

    function updateCounter() {
      const count = jQuery('a.sonata-toggle-filter .fa-check-square', subject).length;

      jQuery('.sonata-filter-count', subject).text(count);
    }

    jQuery('a.sonata-toggle-filter', subject).on('click', (event) => {
      event.preventDefault();
      event.stopPropagation();

      if (jQuery(event.target).attr('sonata-filter') === 'false') {
        return;
      }

      log('[core|add_filters] handle filter container: ', jQuery(event.target).attr('filter-container'));

      const filtersContainer = jQuery(`#${jQuery(event.currentTarget).attr('filter-container')}`);

      if (jQuery('div[sonata-filter="true"]:visible', filtersContainer).length === 0) {
        jQuery(filtersContainer).slideDown();
      }

      const targetSelector = jQuery(event.currentTarget).attr('filter-target');
      const target = jQuery(`div[id="${targetSelector}"]`, filtersContainer);
      const filterToggler = jQuery('i', `.sonata-toggle-filter[filter-target="${targetSelector}"]`);
      if (jQuery(target).is(':visible')) {
        filterToggler
          .filter(':not(.fa-minus-circle)')
          .removeClass('fa-check-square')
          .addClass('fa-square');
        target.hide();
      } else {
        filterToggler
          .filter(':not(.fa-minus-circle)')
          .removeClass('fa-square')
          .addClass('fa-check-square');
        target.show();
      }

      if (jQuery('div[sonata-filter="true"]:visible', filtersContainer).length > 0) {
        jQuery(filtersContainer).slideDown();
      } else {
        jQuery(filtersContainer).slideUp();
      }

      updateCounter();
    });

    jQuery('.sonata-filter-form', subject).on('submit', (event) => {
      const $form = jQuery(event.target);
      $form.find('[sonata-filter="true"]:hidden :input').val('');

      if (!event.target.dataset.defaultValues) {
        return;
      }

      const defaults = Admin.convert_query_string_to_object(
        jQuery.param({ filter: JSON.parse(event.target.dataset.defaultValues) }),
      );

      // Keep only changed values
      $form.find('[name*=filter]').each((index, element) => {
        if (JSON.stringify(defaults[element.name] || '') === JSON.stringify(jQuery(element).val())) {
          element.removeAttribute('name');
        }
      });
    });

    /* Advanced filters */
    if (jQuery('.advanced-filter :input:visible', subject).filter(function filterWithoutValue() { return jQuery(this).val(); }).length === 0) {
      jQuery('.advanced-filter').hide();
    }

    jQuery('[data-toggle="advanced-filter"]', subject).on('click', () => {
      jQuery('.advanced-filter').toggle();
    });

    updateCounter();
  },

  /**
   * Change object field value
   * @param subject
   */
  set_object_field_value(subject) {
    log('[core|set_object_field_value] set value field on', subject);
    log(jQuery('a.sonata-ba-edit-inline', subject));

    jQuery('a.sonata-ba-edit-inline', subject).on('click', (event) => {
      event.preventDefault();
      const element = jQuery(event.target);
      jQuery.ajax({
        url: element.attr('href'),
        type: 'POST',
        success(response) {
          const elm = element.parent();
          elm.children().remove();
          // fix issue with html comment ...
          elm.html(jQuery(response.replace(/<!--[\s\S]*?-->/g, '')).html());
          elm.effect('highlight', { color: '#57A957' }, 2000);
          Admin.set_object_field_value(elm);
        },
        error() {
          element.parent().effect('highlight', { color: '#C43C35' }, 2000);
        },
      });
    });
  },

  setup_collection_counter(subject) {
    setupCollectionCounter(subject);
  },

  setup_collection_buttons(subject) {
    setupCollectionButtons(subject);
  },

  setup_per_page_switcher(subject) {
    log('[core|setup_per_page_switcher] setup page switcher', subject);

    jQuery('select.per-page').on('change', () => {
      jQuery('input[type=submit]').hide();

      window.top.location.href = this.options[this.selectedIndex].value;
    });
  },

  setup_form_tabs_for_errors(subject) {
    log('[core|setup_form_tabs_for_errors] setup form tab\'s errors', subject);

    // Switch to first tab with server side validation errors on page load
    jQuery('form', subject).each((index, element) => {
      Admin.show_form_first_tab_with_errors(jQuery(element), '.sonata-ba-field-error');
    });

    // Switch to first tab with HTML5 errors on form submit
    jQuery(subject)
      .on('click', 'form [type="submit"]', (event) => {
        Admin.show_form_first_tab_with_errors(jQuery(event.target).closest('form'), ':invalid');
      })
      .on('keypress', 'form [type="text"]', (event) => {
        if (event.which === 13) {
          Admin.show_form_first_tab_with_errors(jQuery(event.target), ':invalid');
        }
      });
  },

  show_form_first_tab_with_errors(form, errorSelector) {
    log('[core|show_form_first_tab_with_errors] show first tab with errors', form);

    const tabs = form.find('.nav-tabs a'); let
      firstTabWithErrors;

    tabs.each((index, element) => {
      const id = jQuery(element).attr('href');
      const tab = jQuery(element);
      const icon = tab.find('.has-errors');

      if (jQuery(id).find(errorSelector).length > 0) {
        // Only show first tab with errors
        if (!firstTabWithErrors) {
          tab.tab('show');
          firstTabWithErrors = tab;
        }

        icon.removeClass('hide');
      } else {
        icon.addClass('hide');
      }
    });
  },

  setup_inline_form_errors(subject) {
    log('[core|setup_inline_form_errors] show first tab with errors', subject);

    const deleteCheckboxSelector = '.sonata-ba-field-inline-table [id$="_delete"][type="checkbox"]';

    jQuery(deleteCheckboxSelector, subject).each((index, element) => {
      Admin.switch_inline_form_errors(jQuery(element));
    });

    jQuery(subject).on('change', deleteCheckboxSelector, (event) => {
      Admin.switch_inline_form_errors(jQuery(event.target));
    });
  },

  /**
   * Disable inline form errors when the row is marked for deletion
   */
  switch_inline_form_errors(subject) {
    log('[core|switch_inline_form_errors] switch_inline_form_errors', subject);

    const row = subject.closest('.sonata-ba-field-inline-table');
    const errors = row.find('.sonata-ba-field-error-messages');
    if (subject.is(':checked')) {
      row
        .find('[required]')
        .removeAttr('required')
        .attr('data-required', 'required');
      errors.hide();
    } else {
      row
        .find('[data-required]')
        .attr('required', 'required');
      errors.show();
    }
  },

  setup_tree_view(subject) {
    log('[core|setup_tree_view] setup tree view', subject);

    jQuery('ul.js-treeview', subject).treeView();
  },

  get_select2_width(element) {
    select2Width(element);
  },

  setup_sortable_select2(subject, data, customOptions) {
    setupSortableSelect2(subject[0], data, customOptions);
  },

  setup_sticky_elements(subject) {
    if (getConfig('USE_STICKYFORMS')) {
      log('[core|setup_sticky_elements] setup sticky elements on', subject);

      const topNavbar = jQuery(subject).find('.navbar-static-top');
      const wrapper = jQuery(subject).find('.content-wrapper');
      const navbar = jQuery(wrapper).find('nav.navbar');
      const footer = jQuery(wrapper).find('.sonata-ba-form-actions');

      if (navbar.length) {
        // eslint-disable-next-line no-new
        new window.Waypoint.Sticky({
          element: navbar[0],
          offset() {
            Admin.refreshNavbarStuckClass(topNavbar);

            return jQuery(topNavbar).outerHeight();
          },
          handler(direction) {
            if (direction === 'up') {
              jQuery(navbar).width('auto');
            } else {
              jQuery(navbar).width(jQuery(wrapper).outerWidth());
            }

            Admin.refreshNavbarStuckClass(topNavbar);
          },
        });
      }

      if (footer.length) {
        // eslint-disable-next-line no-new
        new window.Waypoint({
          element: wrapper[0],
          offset: 'bottom-in-view',
          handler(direction) {
            const position = jQuery('.sonata-ba-form form > .row').outerHeight() + jQuery(footer).outerHeight() - 2;

            if (position < jQuery(footer).offset().top) {
              jQuery(footer).removeClass('stuck');
            }

            if (direction === 'up') {
              jQuery(footer).addClass('stuck');
            }
          },
        });
      }

      Admin.handleScroll(footer, navbar, wrapper);
    }
  },

  handleScroll(footer, navbar, wrapper) {
    if (footer.length && jQuery(window).scrollTop() + jQuery(window).height()
      !== jQuery(document).height()) {
      jQuery(footer).addClass('stuck');
    }

    jQuery(window).on('scroll', () => {
      Admin.debounce(() => {
        if (footer.length && Math.round(jQuery(window).scrollTop() + jQuery(window).height())
          >= jQuery(document).height()) {
          jQuery(footer).removeClass('stuck');
        }

        if (navbar.length && jQuery(window).scrollTop() === 0) {
          jQuery(navbar).removeClass('stuck');
        }
      }, 250);
    });

    jQuery('body').on('expanded.pushMenu collapsed.pushMenu', () => {
      // the animation takes 0.3s to execute, so we have to take the width,
      // just after the animation ended
      setTimeout(() => {
        Admin.handleResize(footer, navbar, wrapper);
      }, 350);
    });

    jQuery(window).on('resize', () => {
      Admin.debounce(() => {
        Admin.handleResize(footer, navbar, wrapper);
      }, 250);
    });
  },

  handleResize(footer, navbar, wrapper) {
    if (navbar.length && jQuery(navbar).hasClass('stuck')) {
      jQuery(navbar).width(jQuery(wrapper).outerWidth());
    }

    if (footer.length && jQuery(footer).hasClass('stuck')) {
      jQuery(footer).width(jQuery(wrapper).outerWidth());
    }
  },

  refreshNavbarStuckClass(topNavbar) {
    let stuck = jQuery('#navbar-stuck');

    if (!stuck.length) {
      stuck = jQuery('<style id="navbar-stuck">')
        .prop('type', 'text/css')
        .appendTo('head');
    }

    stuck.html(`body.fixed .content-header .navbar.stuck { top: ${jQuery(topNavbar).outerHeight()}px; }`);
  },

  // http://davidwalsh.name/javascript-debounce-function
  debounce(func, wait, immediate) {
    let timeout;

    return function debounceFunction(...args) {
      const context = this;

      const later = () => {
        timeout = null;

        if (!immediate) {
          func.apply(context, args);
        }
      };

      const callNow = immediate && !timeout;

      clearTimeout(timeout);
      timeout = setTimeout(later, wait);

      if (callNow) {
        func.apply(context, args);
      }
    };
  },

  setup_readmore_elements(subject) {
    log('[core|setup_readmore_elements] setup readmore elements on', subject);

    jQuery(subject).find('.sonata-readmore').each((index, element) => {
      const $element = jQuery(element);

      $element.readmore({
        collapsedHeight: parseInt($element.data('readmore-height'), 10),
        moreLink: `<a href="#">${$element.data('readmore-more')}</a>`,
        lessLink: `<a href="#">${$element.data('readmore-less')}</a>`,
      });
    });
  },

  handle_top_navbar_height() {
    jQuery('body.fixed .content-wrapper').css('padding-top', jQuery('.navbar-static-top').outerHeight());
  },

  setup_form_submit(subject) {
    log('[core|setup_form_submit] setup form submit on', subject);

    jQuery(subject).find('form').on('submit', (event) => {
      const form = jQuery(event.target);

      // this allows to submit forms and know which button was clicked
      setTimeout(() => {
        form.find('button').prop('disabled', true);
      }, 1);

      const tabSelected = form.find('.nav-tabs li.active .changer-tab');

      if (tabSelected.length > 0) {
        form.find('input[name="_tab"]').val(tabSelected.attr('aria-controls'));
      }
    });
  },

  convert_query_string_to_object(str) {
    return str.split('&').reduce((accumulator, keyValue) => {
      const key = decodeURIComponent(keyValue.split('=')[0]);
      const val = keyValue.split('=')[1];

      if (key.endsWith('[]')) {
        if (!Object.prototype.hasOwnProperty.call(accumulator, key)) {
          accumulator[key] = [];
        }
        accumulator[key].push(val);
      } else {
        accumulator[key] = val;
      }

      return accumulator;
    }, {});
  },

  /**
   * Remember open tab after refreshing page.
   */
  setup_view_tabs_changer() {
    jQuery('.changer-tab').on('click', (event) => {
      const tab = jQuery(event.target).attr('aria-controls');
      const search = window.location.search.substring(1);

      /* Get query string parameters from URL */
      const parameters = decodeURIComponent(search).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"');
      let jsonURL = '{}';

      /* If the parameters exist and their length is greater than 0, we put them in json */
      if (parameters.length) {
        jsonURL = `{"${parameters}"}`;
      }

      const hashes = JSON.parse(jsonURL);

      /* Replace tab parameter */
      // eslint-disable-next-line no-underscore-dangle
      hashes._tab = tab;

      /* Setting new URL */
      const newurl = `${window.location.origin + window.location.pathname}?${jQuery.param(hashes, true)}`;
      window.history.pushState({
        path: newurl,
      }, '', newurl);
    });
  },
};

window.Admin = Admin;

jQuery(() => {
  Admin.handle_top_navbar_height();

  jQuery('html').removeClass('no-js');

  Admin.setup_per_page_switcher(document);
  setupCollectionButtons(document);
  Admin.setup_view_tabs_changer();
  Admin.shared_setup(document);
  addFlashMessageListener();
});

jQuery(window).on('resize', () => {
  Admin.handle_top_navbar_height();
});

jQuery(document).on('sonata-admin-append-form-element', (event) => {
  setupSelect2(event.target);
  setupICheck(event.target);
  setupCollectionCounter(event.target);
});

jQuery(window).on('load', () => {
  if (getConfig('CONFIRM_EXIT')) {
    jQuery('.sonata-ba-form form').each((index, element) => {
      jQuery(element).confirmExit();
    });
  }
});
