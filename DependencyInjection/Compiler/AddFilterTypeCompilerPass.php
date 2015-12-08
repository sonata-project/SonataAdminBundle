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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AddFilterTypeCompilerPass.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddFilterTypeCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('sonata.admin.builder.filter.factory');
        $types = array();

        foreach ($container->findTaggedServiceIds('sonata.admin.filter.type') as $id => $attributes) {
            if (method_exists($definition, 'setShared')) { // Symfony 2.8+
                $container->getDefinition($id)->setShared(false);
            } else { // For Symfony <2.8 compatibility
                $container->getDefinition($id)->setScope(ContainerInterface::SCOPE_PROTOTYPE);
            }

            foreach ($attributes as $eachTag) {
                $types[$eachTag['alias']] = $id;
            }
        }

        $definition->replaceArgument(1, $types);
    }
}
