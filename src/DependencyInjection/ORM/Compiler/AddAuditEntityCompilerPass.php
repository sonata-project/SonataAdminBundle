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

namespace SensioLabs\AdminBundle\DependencyInjection\ORM\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AddAuditEntityCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('simplethings_entityaudit.config')) {
            return;
        }

        $auditedEntities = $container->getParameter('simplethings.entityaudit.audited_entities');
        \assert(\is_array($auditedEntities));
        $force = $container->getParameter('sensiolabs_doctrine_orm_admin.audit.force');
        \assert(\is_bool($force));

        foreach ($container->findTaggedServiceIds('sensiolabs.admin') as $id => $attributes) {
            if ('orm' !== $attributes[0]['manager_type']) {
                continue;
            }

            if (true === $force && isset($attributes[0]['audit']) && false === $attributes[0]['audit']) {
                continue;
            }

            if (false === $force && (!isset($attributes[0]['audit']) || false === $attributes[0]['audit'])) {
                continue;
            }

            $definition = $container->getDefinition($id);
            // NEXT_MAJOR: Support only model_class and remove indexed argument support
            $modelClass = $attributes[0]['model_class'] ?? $definition->getArgument(1);
            $auditedEntities[] = $this->getModelName($container, $modelClass);
        }

        $auditedEntities = array_unique($auditedEntities);

        $container->setParameter('simplethings.entityaudit.audited_entities', $auditedEntities);

        $auditManager = $container->getDefinition('sensiolabs.admin.audit.manager');
        $auditManager->addMethodCall('setReader', ['sensiolabs.admin.audit.orm.reader', $auditedEntities]);
    }

    private function getModelName(ContainerBuilder $container, string $name): string
    {
        if ('%' === $name[0]) {
            $parameter = $container->getParameter(substr($name, 1, -1));
            if (!\is_string($parameter)) {
                throw new \InvalidArgumentException(\sprintf('Cannot find the model name "%s"', $name));
            }

            return $parameter;
        }

        return $name;
    }
}
