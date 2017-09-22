/**
 *
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

(function ($) {
    function fetchBlock($block) {
        var overlay = $('<div class="overlay"><div class="fa fa-refresh fa-spin"></div></div>');
        // missing AdminLTE css for info-box necessary for overlay
        $block.find('.overlay-wrapper').css('position', 'relative');
        // show a loading indicator
        $block.find('.overlay-wrapper').append(overlay);
        return $.ajax($block.data('url')).done(function (json) {
            // populate the block with html
            $block.find('.url-count').html(json.count);
        }).fail(function (xhr) {
            // show an error symbol
            $block.find('.url-count').html('<div class="fa fa-remove text-danger"></div>');
        }).always(function () {
            // hide the loading indicator
            $block.find(overlay).remove();
        })
    }

    $(function () {
        $('.sonata-ajax-block').on('click', '.refresh-btn', function (event) {
            var $block = $(event.target).closest('.sonata-ajax-block');
            fetchBlock($block)
        })
    })
}(jQuery));
