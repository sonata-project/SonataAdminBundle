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
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Gaurav Singh Faudjdar <faujdar@gmail.com>
 */
final class ModelManagerCompilerPass implements CompilerPassInterface
{
    public const MANAGER_TAG = 'sonata.admin.manager';

    public function process(ContainerBuilder $container): void
    {
        $availableManagers = [];

        // NEXT_MAJOR: Replace the `foreach()` clause with the following one.
        // foreach ($container->findTaggedServiceIds(self::MANAGER_TAG) as $id => $tags) {
        foreach ($container->getDefinitions() as $id => $definition) {
            // NEXT_MAJOR: Remove this check.
            if (!$definition->hasTag(self::MANAGER_TAG) && 0 !== strpos($id, 'sonata.admin.manager.')) {
                continue;
            }

            if (!is_subclass_of($definition->getClass(), ModelManagerInterface::class)) {
                throw new LogicException(sprintf('Service "%s" must implement `%s`.', $id, ModelManagerInterface::class));
            }

            // NEXT_MAJOR: Remove this check.
            if (!$definition->hasTag(self::MANAGER_TAG)) {
                @trigger_error(sprintf(
                    'Not setting the "%s" tag on the "%s" service is deprecated since sonata-project/admin-bundle 3.60.',
                    self::MANAGER_TAG,
                    $id
                ), E_USER_DEPRECATED);

                $definition->addTag(self::MANAGER_TAG);
            }

            $availableManagers[$id] = $definition;
        }

        if (!empty($availableManagers)) {
            $bundles = $container->getParameter('kernel.bundles');
            if (isset($bundles['MakerBundle'])) {
                $adminMakerDefinition = $container->getDefinition('sonata.admin.maker');
                $adminMakerDefinition->replaceArgument(1, $availableManagers);
            }
        }
    }
}
