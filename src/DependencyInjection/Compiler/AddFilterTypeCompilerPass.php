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

// NEXT_MAJOR: Uncomment next line.
// use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddFilterTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
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
                // NEXT_MAJOR: Remove "trigger_error" and uncomment the exception below.
                @trigger_error(sprintf(
                    'Not declaring a filter with an existing class name is deprecated since'
                    .' sonata-project/admin-bundle 3.x and will not work in 4.0.'
                    .' You MUST register a service with an existing class name for service "%s".',
                    $id,
                ), \E_USER_DEPRECATED);

                //throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }

            if (null !== $reflectionClass && !$reflectionClass->isSubclassOf(FilterInterface::class)) {
                // NEXT_MAJOR: Remove "trigger_error" and uncomment the exception below.
                @trigger_error(sprintf(
                    'Registering service "%s" without implementing interface "%s" is deprecated since'
                    .' sonata-project/admin-bundle 3.x and will be mandatory in 4.0.',
                    $id,
                    FilterInterface::class,
                ), \E_USER_DEPRECATED);
                // throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, FilterInterface::class));
            }

            $types[$serviceDefinition->getClass()] = $id;

            // NEXT_MAJOR: Remove this loop, only FQCN will be supported
            foreach ($attributes as $eachTag) {
                if (isset($eachTag['alias'])) {
                    $types[$eachTag['alias']] = $id;
                }
            }
        }

        $definition->replaceArgument(1, $types);
    }
}
