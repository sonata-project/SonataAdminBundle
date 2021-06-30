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

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * This class injects available model managers to services which depend on them.
 *
 * @internal
 *
 * @author Gaurav Singh Faudjdar <faujdar@gmail.com>
 */
final class ModelManagerCompilerPass implements CompilerPassInterface
{
    public const MANAGER_TAG = 'sonata.admin.manager';

    public function process(ContainerBuilder $container): void
    {
        $availableManagers = [];

        foreach ($container->findTaggedServiceIds(self::MANAGER_TAG) as $id => $tags) {
            $definition = $container->findDefinition($id);

            $class = $definition->getClass();
            if (null === $class || !is_subclass_of($class, ModelManagerInterface::class)) {
                throw new LogicException(sprintf(
                    'Service "%s" must implement `%s`.',
                    $id,
                    ModelManagerInterface::class
                ));
            }

            $availableManagers[$id] = $definition;
        }

        if ([] !== $availableManagers) {
            $bundles = $container->getParameter('kernel.bundles');
            \assert(\is_array($bundles));

            if (isset($bundles['MakerBundle'])) {
                $adminMakerDefinition = $container->getDefinition('sonata.admin.maker');
                $adminMakerDefinition->replaceArgument(1, $availableManagers);
            }
        }
    }
}
