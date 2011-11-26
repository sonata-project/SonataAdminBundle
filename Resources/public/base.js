jQuery(document).ready(function() {
    Admin.add_pretty_errors(document);
    Admin.add_collapsed_toggle();
    Admin.add_filters(document);
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
            if(jQuery(input).is("select")) {
              jQuery(element).prepend("<span></span>");
              target = jQuery('span', element);
              jQuery(input).appendTo(target);
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

    /**
     * Add the collapsed toggle option to the admin
     *
     * @param subject
     */
    add_collapsed_toggle: function(subject) {
        jQuery('fieldset legend a.sonata-ba-collapsed', subject).live('click', function(event) {
            event.preventDefault();

            var fieldset = jQuery(this).closest('fieldset');

            jQuery('div.sonata-ba-collapsed-fields', fieldset).toggle();
            fieldset.toggleClass('sonata-ba-collapsed-fields-close');
        }).click();
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
        jQuery('div.filter_container', subject).hide();
        jQuery('fieldset.filter_legend', subject).click(function(event) {
           jQuery('div.filter_container', jQuery(event.target).parent()).toggle();
        });
    },
    
    /**
     * Change object field value
     * @param MouseEvent
     */
    set_object_field_value: function(event) {
        var targetElement = Admin.stopEvent(event);
        var a = jQuery(targetElement).closest('a');
    
        jQuery.ajax({
            url: a.attr('href'),
            type: 'GET',
            success: function(json) {
                if(json.status === "OK")
                {
                    var td = jQuery(a).parent();
                    td.children().remove();
                    var result_td =json.data.replace(/<!--[\s\S]*?-->/g, "");
                    result_td = jQuery(result_td).html();
                    td.html(result_td);
                    td.effect("highlight", {'color' : '#57A957'}, 2000);
                }
                else
                    jQuery(a).parent().effect("highlight", {'color' : '#C43C35'}, 2000);
            }
        });
    }
}