/*

 This file is part of the Sonata package.

 (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

 */

var Admin = {

    collectionCounters: [],

    /**
     * This function must be called when an ajax call is done, to ensure
     * the retrieved html is properly setup
     *
     * @param subject
     */
    shared_setup: function(subject) {
        Admin.log("[core|shared_setup] Register services on", subject);
        Admin.set_object_field_value(subject);
        Admin.add_filters(subject);
        Admin.setup_select2(subject);
        Admin.setup_icheck(subject);
        Admin.setup_xeditable(subject);
        Admin.setup_form_tabs_for_errors(subject);
        Admin.setup_inline_form_errors(subject);
        Admin.setup_tree_view(subject);
        Admin.setup_collection_counter(subject);
        Admin.setup_sticky_elements(subject);
        Admin.setup_readmore_elements(subject);

//        Admin.setup_list_modal(subject);
    },
    setup_list_modal: function(modal) {
        Admin.log('[core|setup_list_modal] configure modal on', modal);
        // this will force relation modal to open list of entity in a wider modal
        // to improve readability
        jQuery('div.modal-dialog', modal).css({
            width:  '90%', //choose your width
            height: '85%',
            padding: 0
        });
        jQuery('div.modal-content', modal).css({
            'border-radius':'0',
            height:   '100%',
            padding: 0
        });
        jQuery('.modal-body', modal).css({
            width:    'auto',
            height:   '90%',
            padding: 15,
            overflow: 'auto'
        });

        jQuery(modal).trigger('sonata-admin-setup-list-modal');
    },
    setup_select2: function(subject) {
        if (window.SONATA_CONFIG && window.SONATA_CONFIG.USE_SELECT2) {
            Admin.log('[core|setup_select2] configure Select2 on', subject);

            jQuery('select:not([data-sonata-select2="false"])', subject).each(function() {
                var select            = jQuery(this);
                var allowClearEnabled = false;
                var popover           = select.data('popover');

                select.removeClass('form-control');

                if (select.find('option[value=""]').length || select.attr('data-sonata-select2-allow-clear')==='true') {
                    allowClearEnabled = true;
                } else if (select.attr('data-sonata-select2-allow-clear')==='false') {
                    allowClearEnabled = false;
                }

                select.select2({
                    width: function(){
                        // Select2 v3 and v4 BC. If window.Select2 is defined, then the v3 is installed.
                        // NEXT_MAJOR: Remove Select2 v3 support.
                        return Admin.get_select2_width(window.Select2 ? this.element : jQuery(this));
                    },
                    dropdownAutoWidth: true,
                    minimumResultsForSearch: 10,
                    allowClear: allowClearEnabled
                });

                if (undefined !== popover) {
                    select
                        .select2('container')
                        .popover(popover.options)
                    ;
                }
            });
        }
    },
    setup_icheck: function(subject) {
        if (window.SONATA_CONFIG && window.SONATA_CONFIG.USE_ICHECK) {
            Admin.log('[core|setup_icheck] configure iCheck on', subject);

            jQuery("input[type='checkbox']:not('label.btn>input'), input[type='radio']:not('label.btn>input')", subject).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue'
            });
        }
    },

    setup_xeditable: function(subject) {
        Admin.log('[core|setup_xeditable] configure xeditable on', subject);
        jQuery('.x-editable', subject).editable({
            emptyclass: 'editable-empty btn btn-sm btn-default',
            emptytext: '<i class="fa fa-pencil"></i>',
            container: 'body',
            placement: 'auto',
            success: function(response) {
                if('KO' === response.status) {
                    return response.message;
                }

                var html = jQuery(response.content);
                Admin.setup_xeditable(html);

                jQuery(this)
                    .closest('td')
                    .replaceWith(html)
                ;
            }
        });
    },

    /**
     * render log message
     * @param mixed
     */
    log: function() {
        var msg = '[Sonata.Admin] ' + Array.prototype.join.call(arguments,', ');
        if (window.console && window.console.log) {
            window.console.log(msg);
        } else if (window.opera && window.opera.postError) {
            window.opera.postError(msg);
        }
    },

    /**
     * NEXT_MAJOR: remove this function.
     *
     * @deprecated in version 3.0
     */
    add_pretty_errors: function() {
        console.warn('Admin.add_pretty_errors() was deprecated in version 3.0');
    },

    stopEvent: function(event) {
        // https://github.com/sonata-project/SonataAdminBundle/issues/151
        //if it is a standard browser use preventDefault otherwise it is IE then return false
        if(event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false;
        }

        //if it is a standard browser get target otherwise it is IE then adapt syntax and get target
        if (typeof event.target != 'undefined') {
            targetElement = event.target;
        } else {
            targetElement = event.srcElement;
        }

        return targetElement;
    },

    add_filters: function(subject) {
        Admin.log('[core|add_filters] configure filters on', subject);
        jQuery('a.sonata-toggle-filter', subject).on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (jQuery(e.target).attr('sonata-filter') == 'false') {
                return;
            }

            Admin.log('[core|add_filters] handle filter container: ', jQuery(e.target).attr('filter-container'))

            var filters_container = jQuery('#' + jQuery(e.currentTarget).attr('filter-container'));

            if (jQuery('div[sonata-filter="true"]:visible', filters_container).length == 0) {
                jQuery(filters_container).slideDown();
            }

            var targetSelector = jQuery(e.currentTarget).attr('filter-target'),
                target = jQuery('div[id="' + targetSelector + '"]', filters_container),
                filterToggler = jQuery('i', '.sonata-toggle-filter[filter-target="' + targetSelector + '"]')
            ;

            if (jQuery(target).is(":visible")) {
                filterToggler
                    .removeClass('fa-check-square-o')
                    .addClass('fa-square-o')
                ;

                target.hide();

            } else {
                filterToggler
                    .removeClass('fa-square-o')
                    .addClass('fa-check-square-o')
                ;

                target.show();
            }

            if (jQuery('div[sonata-filter="true"]:visible', filters_container).length > 0) {
                jQuery(filters_container).slideDown();
            } else {
                jQuery(filters_container).slideUp();
            }
        });

        jQuery('.sonata-filter-form', subject).on('submit', function () {
            jQuery(this).find('[sonata-filter="true"]:hidden :input').val('');
        });

        /* Advanced filters */
        if (jQuery('.advanced-filter :input:visible', subject).filter(function () { return jQuery(this).val() }).length === 0) {
            jQuery('.advanced-filter').hide();
        };

        jQuery('[data-toggle="advanced-filter"]', subject).click(function() {
            jQuery('.advanced-filter').toggle();
        });
    },

    /**
     * Change object field value
     * @param subject
     */
    set_object_field_value: function(subject) {
        Admin.log('[core|set_object_field_value] set value field on', subject);

        this.log(jQuery('a.sonata-ba-edit-inline', subject));
        jQuery('a.sonata-ba-edit-inline', subject).click(function(event) {
            Admin.stopEvent(event);

            var subject = jQuery(this);
            jQuery.ajax({
                url: subject.attr('href'),
                type: 'POST',
                success: function(json) {
                    if(json.status === "OK") {
                        var elm = jQuery(subject).parent();
                        elm.children().remove();
                        // fix issue with html comment ...
                        elm.html(jQuery(json.content.replace(/<!--[\s\S]*?-->/g, "")).html());
                        elm.effect("highlight", {'color' : '#57A957'}, 2000);
                        Admin.set_object_field_value(elm);
                    } else {
                        jQuery(subject).parent().effect("highlight", {'color' : '#C43C35'}, 2000);
                    }
                }
            });
        });
    },

    setup_collection_counter: function(subject) {
        Admin.log('[core|setup_collection_counter] setup collection counter', subject);

        // Count and save element of each collection
        var highestCounterRegexp = new RegExp('_([0-9]+)[^0-9]*$');
        jQuery(subject).find('[data-prototype]').each(function() {
            var collection = jQuery(this);
            var counter = 0;
            collection.children().each(function() {
                var matches = highestCounterRegexp.exec(jQuery('[id^="sonata-ba-field-container"]', this).attr('id'));
                if (matches && matches[1] && matches[1] > counter) {
                    counter = parseInt(matches[1], 10);
                }
            });
            Admin.collectionCounters[collection.attr('id')] = counter;
        });
    },

    setup_collection_buttons: function(subject) {

        jQuery(subject).on('click', '.sonata-collection-add', function(event) {
            Admin.stopEvent(event);

            var container = jQuery(this).closest('[data-prototype]');
            var counter = ++Admin.collectionCounters[container.attr('id')];
            var proto = container.attr('data-prototype');
            var protoName = container.attr('data-prototype-name') || '__name__';
            // Set field id
            var idRegexp = new RegExp(container.attr('id')+'_'+protoName,'g');
            proto = proto.replace(idRegexp, container.attr('id')+'_'+counter);

            // Set field name
            var parts = container.attr('id').split('_');
            var nameRegexp = new RegExp(parts[parts.length-1]+'\\]\\['+protoName,'g');
            proto = proto.replace(nameRegexp, parts[parts.length-1]+']['+counter);
            jQuery(proto)
                .insertBefore(jQuery(this).parent())
                .trigger('sonata-admin-append-form-element')
            ;

            jQuery(this).trigger('sonata-collection-item-added');
        });

        jQuery(subject).on('click', '.sonata-collection-delete', function(event) {
            Admin.stopEvent(event);

            jQuery(this).trigger('sonata-collection-item-deleted');

            jQuery(this).closest('.sonata-collection-row').remove();

            jQuery(document).trigger('sonata-collection-item-deleted-successful');
        });
    },

    setup_per_page_switcher: function(subject) {
        Admin.log('[core|setup_per_page_switcher] setup page switcher', subject);

        jQuery('select.per-page').change(function(event) {
            jQuery('input[type=submit]').hide();

            window.top.location.href=this.options[this.selectedIndex].value;
        });
    },

    setup_form_tabs_for_errors: function(subject) {
        Admin.log('[core|setup_form_tabs_for_errors] setup form tab\'s errors', subject);

        // Switch to first tab with server side validation errors on page load
        jQuery('form', subject).each(function() {
            Admin.show_form_first_tab_with_errors(jQuery(this), '.sonata-ba-field-error');
        });

        // Switch to first tab with HTML5 errors on form submit
        jQuery(subject)
            .on('click', 'form [type="submit"]', function() {
                Admin.show_form_first_tab_with_errors(jQuery(this).closest('form'), ':invalid');
            })
            .on('keypress', 'form [type="text"]', function(e) {
                if (13 === e.which) {
                    Admin.show_form_first_tab_with_errors(jQuery(this), ':invalid');
                }
            })
        ;
    },

    show_form_first_tab_with_errors: function(form, errorSelector) {
        Admin.log('[core|show_form_first_tab_with_errors] show first tab with errors', form);

        var tabs = form.find('.nav-tabs a'), firstTabWithErrors;

        tabs.each(function() {
            var id = jQuery(this).attr('href'),
                tab = jQuery(this),
                icon = tab.find('.has-errors');

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

    setup_inline_form_errors: function(subject) {
        Admin.log('[core|setup_inline_form_errors] show first tab with errors', subject);

        var deleteCheckboxSelector = '.sonata-ba-field-inline-table [id$="_delete"][type="checkbox"]';

        jQuery(deleteCheckboxSelector, subject).each(function() {
            Admin.switch_inline_form_errors(jQuery(this));
        });

        jQuery(subject).on('change', deleteCheckboxSelector, function() {
            Admin.switch_inline_form_errors(jQuery(this));
        });
    },

    /**
     * Disable inline form errors when the row is marked for deletion
     */
    switch_inline_form_errors: function(subject) {
        Admin.log('[core|switch_inline_form_errors] switch_inline_form_errors', subject);

        var row = subject.closest('.sonata-ba-field-inline-table'),
            errors = row.find('.sonata-ba-field-error-messages')
        ;

        if (subject.is(':checked')) {
            row
                .find('[required]')
                .removeAttr('required')
                .attr('data-required', 'required')
            ;

            errors.hide();
        } else {
            row
                .find('[data-required]')
                .attr('required', 'required')
            ;

            errors.show();
        }
    },

    setup_tree_view: function(subject) {
        Admin.log('[core|setup_tree_view] setup tree view', subject);

        jQuery('ul.js-treeview', subject).treeView();
    },

    /** Return the width for simple and sortable select2 element **/
    get_select2_width: function(element){
        var ereg = /width:(auto|(([-+]?([0-9]*\.)?[0-9]+)(px|em|ex|%|in|cm|mm|pt|pc)))/i;

        // this code is an adaptation of select2 code (initContainerWidth function)
        var style = element.attr('style');
        //console.log("main style", style);

        if (style !== undefined) {
            var attrs = style.split(';');

            for (i = 0, l = attrs.length; i < l; i = i + 1) {
                var matches = attrs[i].replace(/\s/g, '').match(ereg);
                if (matches !== null && matches.length >= 1)
                    return matches[1];
            }
        }

        style = element.css('width');
        if (style.indexOf("%") > 0) {
            return style;
        }

        return '100%';
    },

    setup_sortable_select2: function(subject, data) {
        var transformedData = [];
        for (var i = 0 ; i < data.length ; i++) {
            transformedData[i] = {id: data[i].data, text: data[i].label};
        }

        subject.select2({
            width: function(){
                // Select2 v3 and v4 BC. If window.Select2 is defined, then the v3 is installed.
                // NEXT_MAJOR: Remove Select2 v3 support.
                return Admin.get_select2_width(window.Select2 ? this.element : jQuery(this));
            },
            dropdownAutoWidth: true,
            data: transformedData,
            multiple: true
        });

        subject.select2("container").find("ul.select2-choices").sortable({
            containment: 'parent',
            start: function () {
                subject.select2("onSortStart");
            },
            update: function () {
                subject.select2("onSortEnd");
            }
        });

        // On form submit, transform value to match what is expected by server
        subject.parents('form:first').submit(function (event) {
            var values = subject.val().trim();
            if (values !== '') {
                var baseName = subject.attr('name');
                values   = values.split(',');
                baseName = baseName.substring(0, baseName.length-1);
                for (var i=0; i<values.length; i++) {
                    jQuery('<input>')
                        .attr('type', 'hidden')
                        .attr('name', baseName+i+']')
                        .val(values[i])
                        .appendTo(subject.parents('form:first'));
                }
            }
            subject.remove();
        });
    },

    setup_sticky_elements: function(subject) {
        if (window.SONATA_CONFIG && window.SONATA_CONFIG.USE_STICKYFORMS) {
            Admin.log('[core|setup_sticky_elements] setup sticky elements on', subject);

            var wrapper = jQuery(subject).find('.content-wrapper');
            var navbar  = jQuery(wrapper).find('nav.navbar');
            var footer  = jQuery(wrapper).find('.sonata-ba-form-actions');

            if (navbar.length) {
                new Waypoint.Sticky({
                    element: navbar[0],
                    offset:  50,
                    handler: function( direction ) {
                        if (direction == 'up') {
                            jQuery(navbar).width('auto');
                        } else {
                            jQuery(navbar).width(jQuery(wrapper).outerWidth());
                        }
                    }
                });
            }

            if (footer.length) {
                new Waypoint({
                    element: wrapper[0],
                    offset: 'bottom-in-view',
                    handler: function(direction) {
                        var position = jQuery('.sonata-ba-form form > .row').outerHeight() + jQuery(footer).outerHeight() - 2;

                        if (position < jQuery(footer).offset().top) {
                            jQuery(footer).removeClass('stuck');
                        }

                        if (direction == 'up') {
                            jQuery(footer).addClass('stuck');
                        }
                    }
                });
            }

            Admin.handleScroll(footer, navbar, wrapper);
        }
    },
    handleScroll: function(footer, navbar, wrapper) {
        if (footer.length && jQuery(window).scrollTop() + jQuery(window).height() != jQuery(document).height()) {
            jQuery(footer).addClass('stuck');
        }

        jQuery(window).scroll(
            Admin.debounce(function() {
                if (footer.length && jQuery(window).scrollTop() + jQuery(window).height() == jQuery(document).height()) {
                    jQuery(footer).removeClass('stuck');
                }

                if (navbar.length && jQuery(window).scrollTop() === 0) {
                    jQuery(navbar).removeClass('stuck');
                }
            }, 250)
        );

        jQuery('body').on('expanded.pushMenu collapsed.pushMenu', function() {
            Admin.handleResize(footer, navbar, wrapper);
        });

        jQuery(window).resize(
            Admin.debounce(function() {
                Admin.handleResize(footer, navbar, wrapper);
            }, 250)
        );
    },
    handleResize: function(footer, navbar, wrapper) {
        setTimeout(function() {
            if (navbar.length && jQuery(navbar).hasClass('stuck')) {
                jQuery(navbar).width(jQuery(wrapper).outerWidth());
            }

            if (footer.length && jQuery(footer).hasClass('stuck')) {
                jQuery(footer).width(jQuery(wrapper).outerWidth());
            }
        }, 350); // the animation take 0.3s to execute, so we have to take the width, just after the animation ended
    },
    // http://davidwalsh.name/javascript-debounce-function
    debounce: function (func, wait, immediate) {
        var timeout;

        return function() {
            var context = this,
                args    = arguments;

            var later = function() {
                timeout = null;

                if (!immediate) {
                    func.apply(context, args);
                }
            };

            var callNow = immediate && !timeout;

            clearTimeout(timeout);
            timeout = setTimeout(later, wait);

            if (callNow) {
                func.apply(context, args);
            }
        };
    },
    setup_readmore_elements: function(subject) {
        Admin.log('[core|setup_readmore_elements] setup readmore elements on', subject);

        jQuery(subject).find('.sonata-readmore').each(function(i, ui){
            jQuery(this).readmore({
                collapsedHeight: parseInt(jQuery(this).data('readmore-height')),
                moreLink: '<a href="#">'+jQuery(this).data('readmore-more')+'</a>',
                lessLink: '<a href="#">'+jQuery(this).data('readmore-less')+'</a>'
            });
        });
    },
    handle_inline_delete_checkboxes: function() {
        var eventType = window.SONATA_CONFIG.USE_ICHECK ? 'ifChanged': 'change';

        $('.sonata-ba-form').on(eventType, '.sonata-admin-type-delete-checkbox', function() {
            var id = jQuery(this).prop('id');

            jQuery('[id^=' + id.split('__')[0] + ']:not("#' + id + '")')
                .prop('disabled', jQuery(this).is(':checked'))
            ;
        });
    }
};

jQuery(document).ready(function() {
    jQuery('html').removeClass('no-js');
    if (window.SONATA_CONFIG && window.SONATA_CONFIG.CONFIRM_EXIT) {
        jQuery('.sonata-ba-form form').each(function () { jQuery(this).confirmExit(); });
    }

    Admin.setup_per_page_switcher(document);
    Admin.setup_collection_buttons(document);
    Admin.shared_setup(document);
    Admin.handle_inline_delete_checkboxes();
});

jQuery(document).on('sonata-admin-append-form-element', function(e) {
    Admin.setup_select2(e.target);
    Admin.setup_icheck(e.target);
    Admin.setup_collection_counter(e.target);
});
