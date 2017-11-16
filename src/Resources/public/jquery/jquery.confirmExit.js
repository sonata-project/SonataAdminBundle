/*!
* jQuery confirmExit plugin
* https://github.com/dunglas/jquery.confirmExit
*
* Copyright 2012 Kévin Dunglas <dunglas@gmail.com>
* Released under the MIT license
* http://www.opensource.org/licenses/mit-license.php
*/
(function ($) {
    $.fn.confirmExit = function() {
        $(this).attr('data-original', $(this).serialize());

		$(this).on('submit', function() {
            $(this).removeAttr('data-original');
        });

        return $(this);
	}

    $(window).on('beforeunload', function(event) {
        var e = event || window.event,
            message = window.SONATA_TRANSLATIONS.CONFIRM_EXIT,
            changes = false
        ;

        $('form[data-original]').each(function() {
            if ($(this).attr('data-original') !== $(this).serialize()) {
                changes = true;

                return;
            }
        });

        if (changes) {
            // For old IE and Firefox
            if (e) {
                e.returnValue = message;
            }

            return message;
        }
    });
})(jQuery);
