/*

 This file is part of the Sonata package.

 (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

 */

var handleInlineDeleteCheckboxes = function() {
    var checkboxes = jQuery('.sonata-admin-type-delete-checkbox');

    if(checkboxes.length) {
        var eventType = window.SONATA_CONFIG.USE_ICHECK ? 'ifChanged': 'change';

        checkboxes.on(eventType, function() {
            var id = jQuery(this).prop('id');

            jQuery('[id^=' + id.split('__')[0] + ']')
                .not('#' + id)
                .prop('disabled', jQuery(this).is(':checked'))
            ;
        });
    }
};

jQuery(document).on(
    'ready sonata-admin-setup-list-modal sonata-admin-append-form-element sonata.add_element', 
    handleInlineDeleteCheckboxes
);
