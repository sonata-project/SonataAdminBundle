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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class AdminMakerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('sonata.admin.maker')) {
            return;
        }

        if (!$container->hasParameter('sonata.admin.configuration.default_controller')) {
            return;
        }

        $defaultController = $container->getParameter('sonata.admin.configuration.default_controller');
        \assert(\is_string($defaultController));

        if (!$container->hasDefinition($defaultController)) {
            return;
        }

        $adminMaker = $container->getDefinition('sonata.admin.maker');
        $controllerDefinition = $container->getDefinition($defaultController);

        $adminMaker->replaceArgument(2, $controllerDefinition->getClass());
    }
}
