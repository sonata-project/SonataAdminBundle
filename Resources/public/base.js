jQuery(document).ready(function() {

    BaseApplication.add_pretty_errors(document);
    BaseApplication.add_collapsed_toggle();
});


var BaseApplication = {

    add_pretty_errors: function(subjet) {


        jQuery('div.sonata-ba-field-error', subjet).each(function(index, element) {
            var input = jQuery('input, textarea', element);

            var message = jQuery('div.sonata-ba-field-error-messages', element).html();
            jQuery('div.sonata-ba-field-error-messages', element).html('');
            if(!message) {
                message = '';
            }

            if(message.length == 0) {
                return;
            }

            input.qtip({
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
            })
        });
    },

    add_collapsed_toggle: function(subject) {
        jQuery('fieldset legend a.sonata-ba-collapsed', subject).live('click', function(event) {
            event.preventDefault();

            var fieldset = jQuery(this).closest('fieldset');
            
            jQuery('div.sonata-ba-collapsed-fields', fieldset).toggle();
            fieldset.toggleClass('sonata-ba-collapsed-fields-close');
        });
    }
}