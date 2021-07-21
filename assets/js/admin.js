/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const Admin = {

  collectionCounters: [],
  config: null,
  translations: null,

  /**
   * This function must be called when an ajax call is done, to ensure
   * the retrieved html is properly setup
   *
   * @param subject
   */
  shared_setup(subject) {
    Admin.read_config();
    Admin.log('[core|shared_setup] Register services on', subject);
    Admin.set_object_field_value(subject);
    Admin.add_filters(subject);
    Admin.setup_select2(subject);
    Admin.setup_icheck(subject);
    Admin.setup_checkbox_range_selection(subject);
    Admin.setup_xeditable(subject);
    Admin.setup_form_tabs_for_errors(subject);
    Admin.setup_inline_form_errors(subject);
    Admin.setup_tree_view(subject);
    Admin.setup_collection_counter(subject);
    Admin.setup_sticky_elements(subject);
    Admin.setup_readmore_elements(subject);
    Admin.setup_form_submit(subject);
  },
  read_config() {
    const data = jQuery('[data-sonata-admin]').data('sonata-admin');

    this.config = data.config;
    this.translations = data.translations;
  },
  get_config(key) {
    if (this.config == null) {
      this.read_config();
    }

    return this.config[key];
  },
  get_translations(key) {
    if (this.translations == null) {
      this.read_config();
    }

    return this.translations[key];
  },
  setup_list_modal(modal) {
    Admin.log('[core|setup_list_modal] configure modal on', modal);
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
    if (Admin.get_config('USE_SELECT2')) {
      Admin.log('[core|setup_select2] configure Select2 on', subject);

      jQuery('select:not([data-sonata-select2="false"])', subject).each((index, element) => {
        const select = jQuery(element);
        let allowClearEnabled = false;
        const popover = select.data('popover');
        let maximumSelectionSize = null;
        let minimumResultsForSearch = 10;

        select.removeClass('form-control');

        if (select.find('option[value=""]').length || (select.attr('data-placeholder') && select.attr('data-placeholder').length) || select.attr('data-sonata-select2-allow-clear') === 'true') {
          allowClearEnabled = true;
        } else if (select.attr('data-sonata-select2-allow-clear') === 'false') {
          allowClearEnabled = false;
        }

        if (select.attr('data-sonata-select2-maximumSelectionSize')) {
          maximumSelectionSize = select.attr('data-sonata-select2-maximumSelectionSize');
        }

        if (select.attr('data-sonata-select2-minimumResultsForSearch')) {
          minimumResultsForSearch = select.attr('data-sonata-select2-minimumResultsForSearch');
        }

        select.select2({
          width: () => Admin.get_select2_width(select),
          theme: 'bootstrap',
          dropdownAutoWidth: true,
          minimumResultsForSearch,
          placeholder: allowClearEnabled ? ' ' : '', // allowClear needs placeholder to work properly
          allowClear: allowClearEnabled,
          maximumSelectionSize,
        });

        if (undefined !== popover) {
          select
            .select2('container')
            .popover(popover.options);
        }
      });
    }
  },
  setup_icheck(subject) {
    if (Admin.get_config('USE_ICHECK')) {
      Admin.log('[core|setup_icheck] configure iCheck on', subject);

      const inputs = jQuery('input[type="checkbox"]:not(label.btn > input, [data-sonata-icheck="false"]), input[type="radio"]:not(label.btn > input, [data-sonata-icheck="false"])', subject);
      inputs.iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
      });

      // In case some checkboxes were already checked (for instance after moving
      // back in the browser's session history) update iCheck checkboxes.
      if (subject === window.document) {
        setTimeout(() => { inputs.iCheck('update'); }, 0);
      }
    }
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
    Admin.log('[core|setup_checkbox_range_selection] configure checkbox range selection on', subject);

    let previousIndex;
    const useICheck = Admin.get_config('USE_ICHECK');

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
    Admin.log('[core|setup_xeditable] configure xeditable on', subject);
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
      error: (xhr) => {
        // On some error responses, we return JSON.
        if (xhr.getResponseHeader('Content-Type') === 'application/json') {
          return JSON.parse(xhr.responseText);
        }

        return xhr.responseText;
      },
    });
  },

  /**
   * render log message
   * @param mixed
   */
  log(...args) {
    if (!Admin.get_config('DEBUG')) {
      return;
    }

    const msg = `[Sonata.Admin] ${Array.prototype.join.call(args, ', ')}`;
    if (window.console && window.console.log) {
      window.console.log(msg);
    } else if (window.opera && window.opera.postError) {
      window.opera.postError(msg);
    }
  },

  stopEvent(event) {
    event.preventDefault();

    return event.target;
  },

  add_filters(subject) {
    Admin.log('[core|add_filters] configure filters on', subject);

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

      Admin.log('[core|add_filters] handle filter container: ', jQuery(event.target).attr('filter-container'));

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
        const defaultValue = element.multiple ? [] : '';
        const defaultElementValue = defaults[element.name] || defaultValue;
        const elementValue = jQuery(element).val() || defaultValue;

        if (JSON.stringify(defaultElementValue) === JSON.stringify(elementValue)) {
          element.removeAttribute('name');
        }
      });

      // Simulate a reset if no value is different from the default ones.
      if ($form.find('[name*=filter]').length === 0) {
        $form.append('<input name="filters" type="hidden" value="reset">');
      }
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
    Admin.log('[core|set_object_field_value] set value field on', subject);

    this.log(jQuery('a.sonata-ba-edit-inline', subject));
    jQuery('a.sonata-ba-edit-inline', subject).on('click', (event) => {
      Admin.stopEvent(event);
      const element = jQuery(event.target);
      jQuery.ajax({
        url: element.attr('href'),
        type: 'POST',
        success: (response) => {
          const elm = element.parent();
          elm.children().remove();
          // fix issue with html comment ...
          elm.html(jQuery(response.replace(/<!--[\s\S]*?-->/g, '')).html());
          elm.effect('highlight', { color: '#57A957' }, 2000);
          Admin.set_object_field_value(elm);
        },
        error: () => {
          element.parent().effect('highlight', { color: '#C43C35' }, 2000);
        },
      });
    });
  },

  setup_collection_counter(subject) {
    Admin.log('[core|setup_collection_counter] setup collection counter', subject);

    // Count and save element of each collection
    const highestCounterRegexp = new RegExp('_([0-9]+)[^0-9]*$');
    jQuery(subject).find('[data-prototype]').each((index, element) => {
      const collection = jQuery(element);
      let counter = -1;
      collection.children().each((collectionIndex, collectionElement) => {
        const matches = highestCounterRegexp.exec(jQuery('[id^="sonata-ba-field-container"]', collectionElement).attr('id'));
        if (matches && matches[1] && matches[1] > counter) {
          counter = parseInt(matches[1], 10);
        }
      });
      Admin.collectionCounters[collection.attr('id')] = counter;
    });
  },

  setup_collection_buttons(subject) {
    jQuery(subject).on('click', '.sonata-collection-add', (event) => {
      Admin.stopEvent(event);

      const container = jQuery(event.target).closest('[data-prototype]');

      Admin.collectionCounters[container.attr('id')] += 1;

      const counter = Admin.collectionCounters[container.attr('id')];
      let proto = container.attr('data-prototype');
      const protoName = container.attr('data-prototype-name') || '__name__';
      // Set field id
      const idRegexp = new RegExp(`${container.attr('id')}_${protoName}`, 'g');
      proto = proto.replace(idRegexp, `${container.attr('id')}_${counter}`);

      // Set field name
      const parts = container.attr('id').split('_');
      const nameRegexp = new RegExp(`${parts[parts.length - 1]}\\]\\[${protoName}`, 'g');
      proto = proto.replace(nameRegexp, `${parts[parts.length - 1]}][${counter}`);
      jQuery(proto)
        .insertBefore(jQuery(event.target).parent())
        .trigger('sonata-admin-append-form-element');
      jQuery(event.target).trigger('sonata-collection-item-added');
    });

    jQuery(subject).on('click', '.sonata-collection-delete', (event) => {
      Admin.stopEvent(event);

      jQuery(event.target).trigger('sonata-collection-item-deleted');

      jQuery(event.target).closest('.sonata-collection-row').remove();

      jQuery(document).trigger('sonata-collection-item-deleted-successful');
    });
  },

  setup_per_page_switcher(subject) {
    Admin.log('[core|setup_per_page_switcher] setup page switcher', subject);

    jQuery('select.per-page').on('change', (event) => {
      jQuery('input[type=submit]').hide();

      window.top.location.href = event.target.options[event.target.selectedIndex].value;
    });
  },

  setup_form_tabs_for_errors(subject) {
    Admin.log('[core|setup_form_tabs_for_errors] setup form tab\'s errors', subject);

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
    Admin.log('[core|show_form_first_tab_with_errors] show first tab with errors', form);

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
    Admin.log('[core|setup_inline_form_errors] show first tab with errors', subject);

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
    Admin.log('[core|switch_inline_form_errors] switch_inline_form_errors', subject);

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
    Admin.log('[core|setup_tree_view] setup tree view', subject);

    jQuery('ul.js-treeview', subject).treeView();
  },

  /** Return the width for simple and sortable select2 element * */
  get_select2_width(element) {
    const ereg = /width:(auto|(([-+]?([0-9]*\.)?[0-9]+)(px|em|ex|%|in|cm|mm|pt|pc)))/i;

    // this code is an adaptation of select2 code (initContainerWidth function)
    let style = element.attr('style');
    // console.log("main style", style);

    if (style !== undefined) {
      const attrs = style.split(';');

      for (let i = 0, l = attrs.length; i < l; i += 1) {
        const matches = attrs[i].replace(/\s/g, '').match(ereg);
        if (matches !== null && matches.length >= 1) return matches[1];
      }
    }

    style = element.css('width');
    if (style.indexOf('%') > 0) {
      return style;
    }

    return '100%';
  },

  setup_sortable_select2(subject, data, customOptions) {
    const transformedData = [];
    for (let i = 0; i < data.length; i += 1) {
      transformedData[i] = { id: data[i].data, text: data[i].label };
    }

    const options = {
      theme: 'bootstrap',
      width: () => Admin.get_select2_width(subject),
      dropdownAutoWidth: true,
      data: transformedData,
      multiple: true,
      ...customOptions,
    };

    subject.select2(options);

    subject.select2('container').find('ul.select2-choices').sortable({
      containment: 'parent',
      start: () => {
        subject.select2('onSortStart');
      },
      update: () => {
        subject.select2('onSortEnd');
      },
    });

    // On form submit, transform value to match what is expected by server
    subject.parents('form:first').submit(() => {
      let values = subject.val().trim();
      if (values !== '') {
        let baseName = subject.attr('name');
        values = values.split(',');
        baseName = baseName.substring(0, baseName.length - 1);
        for (let i = 0; i < values.length; i += 1) {
          jQuery('<input>')
            .attr('type', 'hidden')
            .attr('name', `${baseName + i}]`)
            .val(values[i])
            .appendTo(subject.parents('form:first'));
        }
      }
      subject.remove();
    });
  },

  setup_sticky_elements(subject) {
    if (Admin.get_config('USE_STICKYFORMS')) {
      Admin.log('[core|setup_sticky_elements] setup sticky elements on', subject);

      const topNavbar = jQuery(subject).find('.navbar-static-top');
      const wrapper = jQuery(subject).find('.content-wrapper');
      const navbar = jQuery(wrapper).find('nav.navbar');
      const footer = jQuery(wrapper).find('.sonata-ba-form-actions');

      if (navbar.length) {
        // eslint-disable-next-line no-new
        new window.Waypoint.Sticky({
          element: navbar[0],
          offset: () => {
            Admin.refreshNavbarStuckClass(topNavbar);

            return jQuery(topNavbar).outerHeight();
          },
          handler: (direction) => {
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
          handler: (direction) => {
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

    jQuery(window).on('scroll', Admin.debounce(() => {
      if (footer.length && Math.round(jQuery(window).scrollTop() + jQuery(window).height())
        >= jQuery(document).height()) {
        jQuery(footer).removeClass('stuck');
      }

      if (navbar.length && jQuery(window).scrollTop() === 0) {
        jQuery(navbar).removeClass('stuck');
      }
    }, 250));

    jQuery('body').on('expanded.pushMenu collapsed.pushMenu', () => {
      // the animation takes 0.3s to execute, so we have to take the width,
      // just after the animation ended
      setTimeout(() => {
        Admin.handleResize(footer, navbar, wrapper);
      }, 350);
    });

    jQuery(window).on('resize', Admin.debounce(() => {
      Admin.handleResize(footer, navbar, wrapper);
    }, 250));
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
    const topNavbarHeight = topNavbar.outerHeight();

    let stuck = document.getElementById('navbar-stuck');
    if (stuck === null) {
      stuck = document.createElement('style');
      stuck.id = 'navbar-stuck';
      stuck.type = 'text/css';
      stuck.dataset.lastOffset = topNavbarHeight;
      stuck.innerHTML = `body.fixed .content-header .navbar.stuck { top: ${topNavbarHeight}px; }`;
      document.head.appendChild(stuck);
    }

    if (stuck.dataset.lastOffset !== topNavbarHeight) {
      stuck.dataset.lastOffset = topNavbarHeight;
      stuck.innerHTML = `body.fixed .content-header .navbar.stuck { top: ${topNavbarHeight}px; }`;
    }
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
    Admin.log('[core|setup_readmore_elements] setup readmore elements on', subject);

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
    Admin.log('[core|setup_form_submit] setup form submit on', subject);

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
  Admin.setup_collection_buttons(document);
  Admin.setup_view_tabs_changer();
  Admin.shared_setup(document);
});

jQuery(window).on('resize', () => {
  Admin.handle_top_navbar_height();
});

jQuery(document).on('sonata-admin-append-form-element', (event) => {
  Admin.setup_select2(event.target);
  Admin.setup_icheck(event.target);
  Admin.setup_collection_counter(event.target);
});

jQuery(window).on('load', () => {
  if (Admin.get_config('CONFIRM_EXIT')) {
    jQuery('.sonata-ba-form form').each((index, element) => {
      jQuery(element).confirmExit();
    });
  }
});
