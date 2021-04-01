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

use Sonata\AdminBundle\Model\AuditReaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * NEXT_MAJOR: Remove the "since" part of the internal annotation.
 *
 * @internal since sonata-project/admin-bundle version 4.0
 */
final class AddAuditReadersCompilerPass implements CompilerPassInterface
{
    public const AUDIT_READER_TAG = 'sonata.admin.audit_reader';

    public function process(ContainerBuilder $container)
    {
        if (!$container->has('sonata.admin.audit.manager')) {
            return;
        }

        $definition = $container->getDefinition('sonata.admin.audit.manager');
        $readers = [];

        foreach ($container->findTaggedServiceIds(self::AUDIT_READER_TAG, true) as $id => $attributes) {
            $serviceDefinition = $container->getDefinition($id);

            if (!is_subclass_of($serviceDefinition->getClass(), AuditReaderInterface::class)) {
                throw new LogicException(sprintf(
                    'Service "%s" MUST implement "%s".',
                    $id,
                    AuditReaderInterface::class
                ));
            }

            $readers[$id] = new Reference($id);
        }

        // NEXT_MAJOR: Change index from 1 to 0.
        $definition->replaceArgument(1, ServiceLocatorTagPass::register($container, $readers));
    }
}
