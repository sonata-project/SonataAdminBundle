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

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoleSecurityCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('sonata.admin.security.handler.role')) {
            return;
        }

        $roleHandlerDefinition = $container->getDefinition('sonata.admin.security.handler.role');

        foreach ($container->findTaggedServiceIds('sonata.admin.role_security') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (isset($attributes['role_prefix'])) {
                    $roleHandlerDefinition->addMethodCall('setCustomRolePrefix', [
                        $id,
                        $attributes['role_prefix']
                    ]);
                }
            }
        }
    }
}
