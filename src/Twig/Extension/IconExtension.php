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

final class IconExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('parse_icon', [$this, 'parseIcon'], ['is_safe' => ['html']]),
        ];
    }

    public function parseIcon(string $icon): string
    {
        if ('' === $icon) {
            return '';
        }

        switch (substr($icon, 0, 3)) {
            case '<i ':
                return $icon;
            // NEXT_MAJOR: remove this case
            case 'fa-':
                // only the icon name is used by dev: 'fa-plus'
                @trigger_error(
                    'The icon format "fa-icon" is deprecated since sonata-project/admin-bundle 3.x.'
                    .' You should use the full name `fa fa-icon` instead.',
                    \E_USER_DEPRECATED
                );

                return sprintf('<i class="fa %s" aria-hidden="true"></i>', $icon);
            // NEXT_MAJOR: change to fas to to support font-awesome v5
            case 'fa ':
                // full font-awesome is used by dev: 'fa fa-plus'
                // for fa v5 fas prefix should be used.
                return sprintf('<i class="%s" aria-hidden="true"></i>', $icon);
            // NEXT_MAJOR: Uncomment the exception instead.
            default:
                // only icon name is used by dev: 'plus'
                @trigger_error(
                    'The icon format "icon" is deprecated since sonata-project/admin-bundle 3.x.'
                    .' You should use the full name `fa fa-icon` instead.',
                    \E_USER_DEPRECATED
                );

                return sprintf('<i class="fa fa-%s" aria-hidden="true"></i>', $icon);
//                throw new \InvalidArgumentException(sprintf('The icon format "%s" is not supported.', $icon));
        }
    }
}
