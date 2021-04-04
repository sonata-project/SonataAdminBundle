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

use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AddFilterTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('sonata.admin.builder.filter.factory')) {
            return;
        }

        $definition = $container->getDefinition('sonata.admin.builder.filter.factory');
        $types = [];

        foreach ($container->findTaggedServiceIds('sonata.admin.filter.type') as $id => $attributes) {
            $serviceDefinition = $container->getDefinition($id);

            $serviceDefinition->setShared(false);
            $serviceDefinition->setPublic(true); // Temporary fix until we can support service locators

            $class = $serviceDefinition->getClass();
            $reflectionClass = $container->getReflectionClass($class);

            if (null === $reflectionClass) {
                throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }

            if (!$reflectionClass->isSubclassOf(FilterInterface::class)) {
                throw new InvalidArgumentException(sprintf('Service "%s" MUST implement interface "%s".', $id, FilterInterface::class));
            }

            $types[$serviceDefinition->getClass()] = $id;
        }

        $definition->replaceArgument(1, $types);
    }
}
