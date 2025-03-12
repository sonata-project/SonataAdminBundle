/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Config from './core/config';
import Translation from './core/translation';

const Admin = {
  /**
   * This function must be called when an ajax call is done, to ensure
   * the retrieved html is properly setup
   *
   * @param subject
   */
  shared_setup(subject) {
    Admin.log('[core|shared_setup] Register services on', subject);
    Admin.setup_select2(subject);
    Admin.setup_icheck(subject);
    Admin.setup_checkbox_range_selection(subject);
    Admin.setup_xeditable(subject);
    Admin.setup_inline_form_errors(subject);
    Admin.setup_tree_view(subject);
    Admin.setup_sticky_elements(subject);
    Admin.setup_readmore_elements(subject);
  },
  get_config(key) {
    return Config.param(key);
  },
  get_translations(key) {
    return Translation.trans(key);
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
        let maximumSelectionLength = null;
        let minimumResultsForSearch = 10;
        let allowTags = false;

        select.removeClass('form-control');

        if (
          select.find('option[value=""]').length ||
          (select.attr('data-placeholder') && select.attr('data-placeholder').length) ||
          select.attr('data-sonata-select2-allow-clear') === 'true'
        ) {
          allowClearEnabled = true;
        } else if (select.attr('data-sonata-select2-allow-clear') === 'false') {
          allowClearEnabled = false;
        }

        if (select.attr('data-sonata-select2-allow-tags') === 'true') {
          allowTags = true;
        }

        if (select.attr('data-sonata-select2-maximumSelectionLength')) {
          maximumSelectionLength = select.attr('data-sonata-select2-maximumSelectionLength');
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
          maximumSelectionLength,
          tags: allowTags,
        });

        if (undefined !== popover) {
          select.select2('container').popover(popover.options);
        }
      });
    }
  },
  setup_icheck(subject) {
    if (Admin.get_config('USE_ICHECK')) {
      Admin.log('[core|setup_icheck] configure iCheck on', subject);

      const inputs = jQuery(
        'input[type="checkbox"]:not(label.btn > input, [data-sonata-icheck="false"]), input[type="radio"]:not(label.btn > input, [data-sonata-icheck="false"])',
        subject
      );
      inputs.iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
      });

      // In case some checkboxes were already checked (for instance after moving
      // back in the browser's session history) update iCheck checkboxes.
      if (subject === window.document) {
        setTimeout(() => {
          inputs.iCheck('update');
        }, 0);
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
    Admin.log(
      '[core|setup_checkbox_range_selection] configure checkbox range selection on',
      subject
    );

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
          const isChecked = jQuery(
            `tbody input[type="checkbox"]:nth(${currentIndex})`,
            subject
          ).prop('checked');

          // Check all checkbox between previous and current one clicked
          jQuery('tbody input[type="checkbox"]', subject).each((index, element) => {
            if (
              (index > previousIndex && index < currentIndex) ||
              (indexedDB > currentIndex && index < previousIndex)
            ) {
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
        jQuery(this).closest('td').replaceWith(html);
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
      row.find('[required]').removeAttr('required').attr('data-required', 'required');
      errors.hide();
    } else {
      row.find('[data-required]').attr('required', 'required');
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
    const idLookupTable = [];
    const selectedItems = [];
    const unselectedItems = [];
    const selectedIds = subject.val() ? subject.val().split(',') : [];

    for (let i = 0; i < data.length; i += 1) {
      if (idLookupTable[data[i].label]) {
        Admin.log(
          '[setup_sortable_select2] error: sortable requires all option labels to be unique'
        );
      }
      idLookupTable[data[i].label] = data[i].data;

      const item = {
        id: data[i].data,
        text: data[i].label,
      };

      const selectedIndex = selectedIds.indexOf(data[i].data);
      if (selectedIndex !== -1) {
        selectedItems[selectedIndex] = item;
      } else {
        unselectedItems.push(item);
      }
    }

    const options = {
      theme: 'bootstrap',
      width: () => Admin.get_select2_width(subject),
      dropdownAutoWidth: true,
      data: [...selectedItems, ...unselectedItems],
      multiple: true,
      ...customOptions,
    };
    subject.select2(options);
    const list = subject.data('select2').$container.find('ul.select2-selection__rendered');
    list.sortable({
      containment: 'parent',
      items: '> li[data-select2-id]',
      update: () => {
        // Find all values in the new order and put them in the input.
        const sortedIds = list
          .children('li[data-select2-id]')
          .toArray()
          .map((li) => idLookupTable[li.title]);
        subject.val(sortedIds.join());
      },
    }); // On form submit, transform value to match what is expected by server

    subject.parents('form:first').submit(() => {
      let values = subject.val().trim();

      if (values !== '') {
        let baseName = subject.attr('name');
        values = values.split(',');
        baseName = baseName.substring(0, baseName.length - 1);

        for (let i = 0; i < values.length; i += 1) {
          jQuery('<input>')
            .attr('type', 'hidden')
            .attr('name', ''.concat(baseName + i, ']'))
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
            const position =
              jQuery('.sonata-ba-form form > .row').outerHeight() +
              jQuery(footer).outerHeight() -
              2;

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
    if (
      footer.length &&
      jQuery(window).scrollTop() + jQuery(window).height() !== jQuery(document).height()
    ) {
      jQuery(footer).addClass('stuck');
    }

    jQuery(window).on(
      'scroll',
      Admin.debounce(() => {
        if (
          footer.length &&
          Math.round(jQuery(window).scrollTop() + jQuery(window).height()) >=
            jQuery(document).height()
        ) {
          jQuery(footer).removeClass('stuck');
        }

        if (navbar.length && jQuery(window).scrollTop() === 0) {
          jQuery(navbar).removeClass('stuck');
        }
      }, 250)
    );

    jQuery('body').on('expanded.pushMenu collapsed.pushMenu', () => {
      // the animation takes 0.3s to execute, so we have to take the width,
      // just after the animation ended
      setTimeout(() => {
        Admin.handleResize(footer, navbar, wrapper);
      }, 350);
    });

    jQuery(window).on(
      'resize',
      Admin.debounce(() => {
        Admin.handleResize(footer, navbar, wrapper);
      }, 250)
    );
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

    jQuery(subject)
      .find('.sonata-readmore')
      .each((index, element) => {
        const $element = jQuery(element);

        $element.readmore({
          collapsedHeight: parseInt($element.data('readmore-height'), 10),
          moreLink: `<a href="#">${$element.data('readmore-more')}</a>`,
          lessLink: `<a href="#">${$element.data('readmore-less')}</a>`,
        });
      });
  },

  handle_top_navbar_height() {
    jQuery('body.fixed .content-wrapper').css(
      'padding-top',
      jQuery('.navbar-static-top').outerHeight()
    );
  },
};

window.Admin = Admin;

jQuery(() => {
  Admin.handle_top_navbar_height();

  jQuery('html').removeClass('no-js');

  Admin.shared_setup(document);
});

jQuery(window).on('resize', () => {
  Admin.handle_top_navbar_height();
});

jQuery(document).on('sonata-admin-append-form-element', (event) => {
  Admin.setup_select2(event.target);
  Admin.setup_icheck(event.target);
});

jQuery(() => {
  jQuery('select.per-page').one('change.select2', (event) => {
    event.target.dispatchEvent(new Event('change'));
  });
});
