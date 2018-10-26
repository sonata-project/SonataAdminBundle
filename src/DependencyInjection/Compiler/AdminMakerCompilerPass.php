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
final class AdminMakerCompilerPass implements CompilerPassInterface
{
    const MANAGERS = [
        'sonata.admin.manager.orm',
        'sonata.admin.manager.doctrine_mongodb',
        'sonata.admin.manager.doctrine_phpcr',
    ];

    public function process(ContainerBuilder $container)
    {
        $availableManagers = [];
        foreach (self::MANAGERS as $manager) {
            if ($container->hasDefinition($manager)) {
                $availableManagers[$manager] = $container->getDefinition($manager);
            }
        }

        $definition = $container->getDefinition('sonata.admin.maker');
        $definition->replaceArgument(1, $availableManagers);
    }
}
