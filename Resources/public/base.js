jQuery(document).ready(function() {
    jQuery('html').removeClass('no-js');
    if (window.SONATA_CONFIG && window.SONATA_CONFIG.CONFIRM_EXIT) {
        jQuery('.sonata-ba-form form').confirmExit();
    }

    Admin.setup_select2(document);
    Admin.setup_xeditable(document);
    Admin.add_pretty_errors(document);
    Admin.add_filters(document);
    Admin.set_object_field_value(document);
    Admin.setup_collection_buttons(document);
    Admin.setup_per_page_switcher(document);
    Admin.setup_form_tabs_for_errors(document);
    Admin.setup_inline_form_errors(document);
});

jQuery(document).on('sonata-admin-append-form-element', function(e) {
    Admin.setup_select2(e.target);
});

var Admin = {

    setup_select2: function(subject) {
        if (window.SONATA_CONFIG && window.SONATA_CONFIG.USE_SELECT2 && window.Select2) {
            jQuery('select:not([data-sonata-select2="false"])', subject).each(function() {
                var select = $(this);

                var allowClearEnabled = false;

                if (select.find('option[value=""]').length) {
                    allowClearEnabled = true;
                }

                if (select.attr('data-sonata-select2-allow-clear')==='true') {
                    allowClearEnabled = true;
                } else if (select.attr('data-sonata-select2-allow-clear')==='false') {
                    allowClearEnabled = false;
                }

                select.select2({
                    width: 'resolve',
                    minimumResultsForSearch: 10,
                    allowClear: allowClearEnabled
                });

                var popover = select.data('popover');

                if (undefined !== popover) {
                    select
                        .select2('container')
                        .popover(popover.options)
                    ;
                }
            });
        }
    },

    setup_xeditable: function(subject) {
        jQuery('.x-editable', subject).editable({
            emptyclass: 'editable-empty btn btn-small',
            emptytext: '<i class="icon-edit"></i>',
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
     * display related errors messages
     *
     * @param subject
     */
    add_pretty_errors: function(subject) {

        Admin.setup_select2(subject);

        jQuery('div.sonata-ba-field-error', subject).each(function(index, element) {
            var input = jQuery(':input', element);

            if (!input.length) {
                return;
            }

            var message = jQuery('div.sonata-ba-field-error-messages', element).html();
            jQuery('div.sonata-ba-field-error-messages', element).remove();

            if (!message || message.length == 0) {
                return;
            }

            var target = input,
                fieldShortDescription = input.closest('.field-container').find('.field-short-description'),
                select2 = input.closest('.select2-container')
                ;
    
            if (fieldShortDescription.length) {
                target = fieldShortDescription;
            } else if (select2.length) {
                target= select2;
            }

            target.popover({
                content: message,
                trigger: 'hover',
                html: true,
                placement: 'right',
                template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-content alert-error"><p></p></div></div></div>'
            });

        });
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
        jQuery('div.filter_container .sonata-filter-option', subject).hide();
        jQuery('fieldset.filter_legend', subject).click(function(event) {
            jQuery('div.filter_container .sonata-filter-option', jQuery(event.target).parent()).toggle();
        });
    },

    /**
     * Change object field value
     * @param subject
     */
    set_object_field_value: function(subject) {

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

    setup_collection_buttons: function(subject) {

        jQuery(subject).on('click', '.sonata-collection-add', function(event) {
            Admin.stopEvent(event);

            var container = jQuery(this).closest('[data-prototype]');
            var proto = container.attr('data-prototype');
            var protoName = container.attr('data-prototype-name') || '__name__';
            // Set field id
            var idRegexp = new RegExp(container.attr('id')+'_'+protoName,'g');
            proto = proto.replace(idRegexp, container.attr('id')+'_'+(container.children().length - 1));

            // Set field name
            var parts = container.attr('id').split('_');
            var nameRegexp = new RegExp(parts[parts.length-1]+'\\]\\['+protoName,'g');
            proto = proto.replace(nameRegexp, parts[parts.length-1]+']['+(container.children().length - 1));
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
        });
    },

    setup_per_page_switcher: function(subject) {
        jQuery('select.per-page').change(function(event) {
            jQuery('input[type=submit]').hide();

            window.top.location.href=this.options[this.selectedIndex].value;
        });
    },

    setup_form_tabs_for_errors: function(subject) {
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
        var tabs = form.find('.nav-tabs a'),
            firstTabWithErrors;

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
        var deleteCheckboxSelector = '.sonata-ba-field-inline-table [id$="_delete"][type="checkbox"]';

        jQuery(deleteCheckboxSelector, subject).each(function() {
            Admin.switch_inline_form_errors(jQuery(this));
        });

        $(subject).on('change', deleteCheckboxSelector, function() {
            Admin.switch_inline_form_errors(jQuery(this));
        });
    },

    /**
     * Disable inline form errors when the row is marked for deletion
     */
    switch_inline_form_errors: function(deleteCheckbox) {
        var row = deleteCheckbox.closest('.sonata-ba-field-inline-table'),
            errors = row.find('.sonata-ba-field-error-messages')
        ;

        if (deleteCheckbox.is(':checked')) {
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
    }
};
