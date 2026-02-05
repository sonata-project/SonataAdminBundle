<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AddAuditReadersCompilerPass;
use SensioLabs\AdminBundle\Model\AuditManager;
use SensioLabs\AdminBundle\Tests\Fixtures\Model\AuditReader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

final class AddAuditReadersCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess(): void
    {
        $auditManagerDefinition = new Definition(AuditManager::class, [
            null,
        ]);

        $this->container
            ->setDefinition('sensiolabs.admin.audit.manager', $auditManagerDefinition);

        $auditReader = new Definition(AuditReader::class);
        $auditReader
            ->addTag(AddAuditReadersCompilerPass::AUDIT_READER_TAG);

        $this->container
            ->setDefinition('std_audit_reader', $auditReader);

        $this->compile();

        $serviceLocator = $this->container->getDefinition('sensiolabs.admin.audit.manager')->getArgument(0);
        static::assertInstanceOf(Reference::class, $serviceLocator);

        self::assertContainerBuilderHasServiceLocator(
            (string) $serviceLocator,
            [
                'std_audit_reader' => new Reference('std_audit_reader'),
            ]
        );
    }

    public function testServiceTaggedMustImplementInterface(): void
    {
        $auditManagerDefinition = new Definition(AuditManager::class);

        $this->container
            ->setDefinition('sensiolabs.admin.audit.manager', $auditManagerDefinition);

        $auditReader = new Definition(\stdClass::class);
        $auditReader
            ->addTag(AddAuditReadersCompilerPass::AUDIT_READER_TAG);

        $this->container
            ->setDefinition('std_audit_reader', $auditReader);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Service "std_audit_reader" MUST implement "SensioLabs\AdminBundle\Model\AuditReaderInterface".');

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddAuditReadersCompilerPass());
    }
}
