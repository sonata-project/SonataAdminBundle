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
 * @internal
 */
final class AddAuditReadersCompilerPass implements CompilerPassInterface
{
    public const AUDIT_READER_TAG = 'sonata.admin.audit_reader';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('sonata.admin.audit.manager')) {
            return;
        }

        $definition = $container->getDefinition('sonata.admin.audit.manager');
        $readers = [];

        foreach ($container->findTaggedServiceIds(self::AUDIT_READER_TAG, true) as $id => $tags) {
            $serviceDefinition = $container->getDefinition($id);

            if (!is_subclass_of($serviceDefinition->getClass() ?? '', AuditReaderInterface::class)) {
                throw new LogicException(sprintf(
                    'Service "%s" MUST implement "%s".',
                    $id,
                    AuditReaderInterface::class
                ));
            }

            $readers[$id] = new Reference($id);
        }

        $definition->replaceArgument(0, ServiceLocatorTagPass::register($container, $readers));
    }
}
