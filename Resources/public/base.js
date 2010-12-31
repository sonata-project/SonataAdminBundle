jQuery(document).ready(function() {


    jQuery('div.sonata-ba-field-error').each(function(index, element) {
        var input = jQuery('input, textarea', element);

        var message = jQuery('div.sonata-ba-field-error-messages', element).html();
        jQuery('div.sonata-ba-field-error-messages', element).html('');
        if(!message) {
            message = '';
        }
        
        input.qtip({
            content: message,
            show: 'mouseover',
            hide: 'mouseout',
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

});