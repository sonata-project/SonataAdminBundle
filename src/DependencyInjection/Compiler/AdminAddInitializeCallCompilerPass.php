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

/**
 * this compiler pass is registered with low priority to make sure it runs after all the other passes
 * as we want the "initialize()" calls to come after all the other calls.
 *
 * @internal
 */
final class AdminAddInitializeCallCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $admins = $container->findTaggedServiceIds('sonata.admin');
        foreach (array_keys($admins) as $id) {
            $container->getDefinition($id)->addMethodCall('initialize');
        }
    }
}
