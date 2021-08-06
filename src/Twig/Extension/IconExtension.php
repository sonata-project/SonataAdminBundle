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

        if (
            0 !== strpos($icon, 'fa ')
            && 0 !== strpos($icon, 'fas ')
            && 0 !== strpos($icon, 'far ')
            && 0 !== strpos($icon, 'fab ')
            && 0 !== strpos($icon, 'fal ')
            && 0 !== strpos($icon, 'fad ')
        ) {
            throw new \InvalidArgumentException(sprintf('The icon format "%s" is not supported.', $icon));
        }

        return sprintf('<i class="%s" aria-hidden="true"></i>', $icon);
    }
}
