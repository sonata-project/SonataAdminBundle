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

namespace SensioLabs\AdminBundle\User\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RolesMatrixCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('sensiolabs.admin.user.admin_roles_builder')) {
            return;
        }

        $excludedAdmins = [];

        foreach ($container->findTaggedServiceIds('sensiolabs.admin') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (isset($attributes['show_in_roles_matrix']) && false === $attributes['show_in_roles_matrix']) {
                    $excludedAdmins[] = $id;
                }
            }
        }

        if ([] !== $excludedAdmins) {
            $container->getDefinition('sensiolabs.admin.user.admin_roles_builder')
                ->addMethodCall('setExcludedAdmins', [$excludedAdmins]);
        }
    }
}
