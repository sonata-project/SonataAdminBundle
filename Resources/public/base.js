jQuery(document).ready(function() {
    jQuery('html').removeClass('no-js');
    Admin.add_pretty_errors(document);
    Admin.add_filters(document);
    Admin.set_object_field_value(document);
    Admin.setup_collection_buttons(document);
    Admin.setup_per_page_switcher(document);
    Admin.setup_form_tabs_for_errors(document);
});

var Admin = {

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
        jQuery('div.sonata-ba-field-error', subject).each(function(index, element) {
            var input = jQuery('input, textarea, select', element);

            var message = jQuery('div.sonata-ba-field-error-messages', element).html();
            jQuery('div.sonata-ba-field-error-messages', element).html('');
            if (!message) {
                message = '';
            }

            if (message.length == 0) {
                return;
            }

            var target;

            /* Hack to handle qTip on select */
            if(jQuery(input).is('select')) {
                input.wrap('<span></span>');
                target = input.parent();
            }
            else {
                target = input;
            }

            target.qtip({
                content: message,
                show: 'focusin',
                hide: 'focusout',
                position: {
                    corner: {
                        target: 'rightMiddle',
                        tooltip: 'leftMiddle'
                    }
                },
                style: {
                    name: 'red',
                    border: {
                        radius: 2
                    },
                    tip: 'leftMiddle'
                }
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
            // Set field id
            var idRegexp = new RegExp(container.attr('id')+'___name__','g');
            proto = proto.replace(idRegexp, container.attr('id')+'_'+(container.children().length - 1));

            // Set field name
            var parts = container.attr('id').split('_');
            var nameRegexp = new RegExp(parts[parts.length-1]+'\\]\\[__name__','g');
            proto = proto.replace(nameRegexp, parts[parts.length-1]+']['+(container.children().length - 1));
            jQuery(proto).insertBefore(jQuery(this).parent());

            jQuery(this).trigger('sonata-collection-item-added');
        });

        jQuery(subject).on('click', '.sonata-collection-delete', function(event) {
            Admin.stopEvent(event);

            jQuery(this).closest('.sonata-collection-row').remove();

            jQuery(this).trigger('sonata-collection-item-deleted');
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
    }
}
