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

use Sonata\AdminBundle\Twig\RenderElementRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class RenderElementExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'render_list_element',
                [RenderElementRuntime::class, 'renderListElement'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFilter(
                'render_view_element',
                [RenderElementRuntime::class, 'renderViewElement'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFilter(
                'render_view_element_compare',
                [RenderElementRuntime::class, 'renderViewElementCompare'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFilter(
                'render_relation_element',
                [RenderElementRuntime::class, 'renderRelationElement']
            ),
        ];
    }
}
