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

namespace Sonata\AdminBundle\Twig;

use Twig\Extension\RuntimeExtensionInterface;

final class IconRuntime implements RuntimeExtensionInterface
{
    /**
     * The Font Awesome style classes.
     */
    public const DEFAULT_PREFIXES = ['fa', 'fas', 'far', 'fab', 'fal', 'fad'];

    /**
     * @param string[] $prefixes CSS classes an icon may start with
     */
    public function __construct(
        private array $prefixes = self::DEFAULT_PREFIXES,
    ) {
    }

    public function parseIcon(string $icon): string
    {
        if ('' === $icon || str_starts_with($icon, '<')) {
            return $icon;
        }

        foreach ($this->prefixes as $prefix) {
            if (str_starts_with($icon, $prefix.' ')) {
                return \sprintf('<i class="%s" aria-hidden="true"></i>', $icon);
            }
        }

        throw new \InvalidArgumentException(\sprintf('The icon format "%s" is not supported.', $icon));
    }
}
