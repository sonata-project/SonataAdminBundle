<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

/*
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddFilterTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('sonata.admin.builder.filter.factory');
        $types = array();

        foreach($container->findTaggedServiceIds('sonata.admin.filter.type') as $id => $attributes) {
            $name = $attributes[0]['alias'];

            $container->getDefinition($id)->setScope(ContainerInterface::SCOPE_PROTOTYPE);

            $types[$name] = $id;
        }

        $definition->replaceArgument(1, $types);
    }
}