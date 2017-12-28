<?php

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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddFilterTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('sonata.admin.builder.filter.factory');
        $types = [];

        foreach ($container->findTaggedServiceIds('sonata.admin.filter.type') as $id => $attributes) {
            $serviceDefinition = $container->getDefinition($id);

            $serviceDefinition->setShared(false);
            $serviceDefinition->setPublic(true); // Temporary fix until we can support service locators

            $types[$serviceDefinition->getClass()] = $id;

            // NEXT_MAJOR: Remove the alias when dropping support for symfony 2.x
            foreach ($attributes as $eachTag) {
                $types[$eachTag['alias']] = $id;
            }
        }

        $definition->replaceArgument(1, $types);
    }
}
