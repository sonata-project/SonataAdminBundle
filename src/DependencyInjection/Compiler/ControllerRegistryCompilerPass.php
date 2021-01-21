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

use Sonata\AdminBundle\Controller\CRUDControllerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ControllerRegistryCompilerPass implements CompilerPassInterface
{
    public const CONTROLLER_TAG = 'sonata.admin.controller';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonata.admin.controller_registry')) {
            return;
        }

        $controllerRegistry = $container->getDefinition('sonata.admin.controller_registry');

        $controllers = [];

        foreach ($container->findTaggedServiceIds(self::CONTROLLER_TAG) as $id => $tags) {
            $definition = $container->getDefinition($id);

            $class = $definition->getClass();

            if (!is_subclass_of($class, CRUDControllerInterface::class)) {
                throw new \InvalidArgumentException(sprintf(
                    'Service "%s" must implement interface "%s".',
                    $id,
                    CRUDControllerInterface::class
                ));
            }

            $controllers[$class::getSupportedAdmin()] = $id;
        }

        $controllerRegistry->replaceArgument(0, $controllers);
    }
}
