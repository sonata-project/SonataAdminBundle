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
        if ('' === $icon || 0 === strpos($icon, '<')) {
            return $icon;
        }

        // NEXT_MAJOR: remove this check.
        if ('fa-' === substr($icon, 0, 3)) {
            // only the icon name is used by dev: 'fa-plus'
            @trigger_error(
                'The icon format "fa-icon" is deprecated since sonata-project/admin-bundle 3.103.'
                .' You should use the full name `fa fa-icon` instead.',
                \E_USER_DEPRECATED
            );

            return sprintf('<i class="fa %s" aria-hidden="true"></i>', $icon);
        }

        if (
            0 !== strpos($icon, 'fa ')
            && 0 !== strpos($icon, 'fas ')
            && 0 !== strpos($icon, 'far ')
            && 0 !== strpos($icon, 'fal ')
            && 0 !== strpos($icon, 'fad ')
        ) {
            // NEXT_MAJOR: uncomment the exception.
            @trigger_error(
                'The icon format "icon" is deprecated since sonata-project/admin-bundle 3.103.'
                .' You should use the full name `fa fa-icon` instead.',
                \E_USER_DEPRECATED
            );

            return sprintf('<i class="fa fa-%s" aria-hidden="true"></i>', $icon);

//            throw new \InvalidArgumentException(sprintf('The icon format "%s" is not supported.', $icon));
        }

        return sprintf('<i class="%s" aria-hidden="true"></i>', $icon);
    }
}
