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

use SensioLabs\AdminBundle\Twig\XEditableRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class XEditableExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'sensiolabs_xeditable_type',
                [XEditableRuntime::class, 'getXEditableType']
            ),
            new TwigFilter(
                'sensiolabs_xeditable_choices',
                [XEditableRuntime::class, 'getXEditableChoices']
            ),
        ];
    }
}
