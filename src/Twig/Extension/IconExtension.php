<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class IconExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return array(
            new TwigFilter('parse_icon', array($this, 'parseIcon')),
        );
    }

    public function parseIcon(string $icon, bool $inline = false): string
    {
        switch (substr($icon,0,3)) {
            case "<i ":
                // full style is used by dev: '<i class="fa fa-plus"></i>'
                if ($inline) {
                    preg_match('/"([^"]+)"/', $icon, $output);
                    $iconHtml = $output[1];
                } else {
                    $iconHtml = $icon;
                }
                break;
            // NEXT_MAJOR: remove this case
            case "fa-":
                // only the icon name is used by dev: 'fa-plus'
                @trigger_error(
                    //'Using this format for icons is deprecated. Please use "<i class="fa fa-icon"></i>" or "fa fa-icon"',
                    'The icon format "fa-icon" is deprecated since sonata-project/admin-bundle 3.XX. You should stop using it, as it will not be supported in 4.0.',
                    \E_USER_DEPRECATED
                );
                if($inline) {
                    $iconHtml = "fa ".$icon;
                } else {
                    $iconHtml = sprintf('<i class="fa %s"></i>', $icon);
                }
                break;
            // NEXT_MAJOR: change to fas to to support font-awesome v5
            case "fa ":
                // full font-awesome is used by dev: 'fa fa-plus'
                // for fa v5 fas prefix should be used.
                if ($inline) {
                    $iconHtml = $icon;
                } else {
                    $iconHtml = sprintf('<i class="%s"></i>', $icon);
                }
                break;

            default:
                // only icon name is used by dev: 'plus'
                @trigger_error(
                    'The icon format "icon" is deprecated since sonata-project/admin-bundle 3.XX. You should stop using it, as it will not be supported in 4.0.',
                    \E_USER_DEPRECATED
                );
                if ($inline) {
                    $iconHtml = "fa fa-" . $icon;
                } else {
                    $iconHtml = sprintf('<i class="fa fa-%s"></i>', $icon);
                }
                // NEXT_MAJOR: replace default case with next lines
                //@trigger_error(
                //    'The icon format is not longer supported. Please use "<i class="fas fa-icon"></i>" or "fas fa-icon"',
                //    \E_USER_ERROR
                //);
            //
        }

        return $iconHtml;
    }
}
