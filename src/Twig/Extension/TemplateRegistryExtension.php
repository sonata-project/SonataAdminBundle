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

use SensioLabs\AdminBundle\Twig\TemplateRegistryRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TemplateRegistryExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_admin_template', [TemplateRegistryRuntime::class, 'getAdminTemplate']),
            new TwigFunction('get_global_template', [TemplateRegistryRuntime::class, 'getGlobalTemplate']),
        ];
    }
}
