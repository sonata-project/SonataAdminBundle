<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Twig\Extension;

use SensioLabs\AdminBundle\Twig\CanonicalizeRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CanonicalizeExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('canonicalize_locale_for_moment', [CanonicalizeRuntime::class, 'getCanonicalizedLocaleForMoment']),
            new TwigFunction('canonicalize_locale_for_select2', [CanonicalizeRuntime::class, 'getCanonicalizedLocaleForSelect2']),
        ];
    }
}
