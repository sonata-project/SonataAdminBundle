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

use Sonata\AdminBundle\Command\GenerateAdminCommand;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This class injects available model managers to services which depend on them.
 *
 * @author Gaurav Singh Faudjdar <faujdar@gmail.com>
 */
final class ModelManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $availableManagers = [];

        foreach ($container->getServiceIds() as $id) {
            if (0 !== strpos($id, 'sonata.admin.manager.') || !is_subclass_of($container->getDefinition($id)->getClass(), ModelManagerInterface::class)) {
                continue;
            }

            $availableManagers[$id] = $container->getDefinition($id);
        }

        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['MakerBundle'])) {
            $adminMakerDefinition = $container->getDefinition('sonata.admin.maker');
            $adminMakerDefinition->replaceArgument(1, $availableManagers);
        }

        $generateAdminCommandDefinition = $container->getDefinition(GenerateAdminCommand::class);
        $generateAdminCommandDefinition->replaceArgument(1, $availableManagers);
    }
}
