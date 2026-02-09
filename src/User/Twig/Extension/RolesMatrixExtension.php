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

namespace SensioLabs\AdminBundle\User\Twig\Extension;

use SensioLabs\AdminBundle\User\Twig\RolesMatrixRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RolesMatrixExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sensiolabs_admin_render_roles_matrix', [RolesMatrixRuntime::class, 'renderMatrix'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('sensiolabs_admin_render_roles_list', [RolesMatrixRuntime::class, 'renderRolesList'], [
                'is_safe' => ['html'],
            ]),
        ];
    }
}
