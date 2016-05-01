import $ from 'jquery';

import debounce from 'sonata/util/debounce';


const {Waypoint} = window;


export default function setupStickyElements (subject) {
    const $wrapper = $(subject).find('.content-wrapper');
    const $navbar = $wrapper.find('nav.navbar');
    const $footer = $wrapper.find('.sonata-ba-form-actions');

    if ($navbar.length) {
        // eslint-disable-next-line no-new
        new Waypoint.Sticky({
            element: $navbar[0],
            offset:  50,
            handler: direction => $navbar.width(direction === 'up' ? 'auto' : $wrapper.outerWidth()),
        });
    }

    if ($footer.length) {
        // eslint-disable-next-line no-new
        new Waypoint({
            element: $wrapper[0],
            offset: 'bottom-in-view',
            handler: direction => {
                const position = $('.sonata-ba-form form > .row').outerHeight() + $footer.outerHeight() - 2;

                if (position < $footer.offset().top) {
                    $footer.removeClass('stuck');
                }

                if (direction === 'up') {
                    $footer.addClass('stuck');
                }
            },
        });
    }

    handleScroll($footer, $navbar, $wrapper);
}


function handleScroll ($footer, $navbar, $wrapper) {
    const $win = $(window);
    const $doc = $(document);

    if ($footer.length && $win.scrollTop() + $win.height() !== $doc.height()) {
        $footer.addClass('stuck');
    }

    $win.on('scroll', debounce(() => {
        if ($footer.length && $win.scrollTop() + $win.height() === $doc.height()) {
            $footer.removeClass('stuck');
        }

        if ($navbar.length && $win.scrollTop() === 0) {
            $navbar.removeClass('stuck');
        }
    }, 250));

    $('body').on('expanded.pushMenu collapsed.pushMenu', () => handleResize($footer, $navbar, $wrapper));

    $win.on('resize', debounce(() => handleResize($footer, $navbar, $wrapper), 250));
}


function handleResize ($footer, $navbar, $wrapper) {
    setTimeout(() => {
        if ($navbar.length && $navbar.hasClass('stuck')) {
            $navbar.width($wrapper.outerWidth());
        }

        if ($footer.length && $footer.hasClass('stuck')) {
            $footer.width($wrapper.outerWidth());
        }
    }, 350); // the animation take 0.3s to execute, so we have to take the width, just after the animation ended
}
