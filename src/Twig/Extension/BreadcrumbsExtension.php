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

use SensioLabs\AdminBundle\Twig\BreadcrumbsRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class BreadcrumbsExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_breadcrumbs', [BreadcrumbsRuntime::class, 'renderBreadcrumbs'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction('render_breadcrumbs_for_title', [BreadcrumbsRuntime::class, 'renderBreadcrumbsForTitle'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }
}
