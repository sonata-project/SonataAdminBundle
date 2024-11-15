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

use Sonata\AdminBundle\Twig\IconRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class IconExtension extends AbstractExtension
{
    /**
     * NEXT_MAJOR: Remove this constructor.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private IconRuntime $iconRuntime,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('parse_icon', [IconRuntime::class, 'parseIcon'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use IconRuntime::parseIcon() instead
     */
    public function parseIcon(string $icon): string
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            IconRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->iconRuntime->parseIcon($icon);
    }
}
