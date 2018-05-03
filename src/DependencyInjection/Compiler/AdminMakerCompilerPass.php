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
 * This class injects available admin managers to the AdminMaker.
 *
 * @author Gaurav Singh Faudjdar <faujdar@gmail.com>
 */
class AdminMakerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $managers = ['sonata.admin.manager.orm',
            'sonata.admin.manager.doctrine_mongodb',
            'sonata.admin.manager.doctrine_phpcr', ];

        $availableManagers = [];
        foreach ($managers as $manager) {
            if ($container->hasDefinition($manager)) {
                $availableManagers[$manager] = $container->getDefinition($manager);
            }
        }

        $definition = $container->getDefinition('sonata.admin.maker');
        $definition->setArgument(0, $container->getParameter('kernel.project_dir'));
        $definition->setArgument(1, $availableManagers);
    }
}
