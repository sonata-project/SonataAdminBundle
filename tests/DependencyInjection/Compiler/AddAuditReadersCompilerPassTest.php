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

namespace Sonata\AdminBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddAuditReadersCompilerPass;
use Sonata\AdminBundle\Model\AuditManager;
use Sonata\AdminBundle\Tests\Fixtures\Model\AuditReader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

final class AddAuditReadersCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess(): void
    {
        $auditManagerDefinition = new Definition(AuditManager::class, [
            // NEXT_MAJOR: Remove next line.
            new Container(),
            null,
        ]);

        $this->container
            ->setDefinition('sonata.admin.audit.manager', $auditManagerDefinition);

        $auditReader = new Definition(AuditReader::class);
        $auditReader
            ->addTag(AddAuditReadersCompilerPass::AUDIT_READER_TAG);

        $this->container
            ->setDefinition('std_audit_reader', $auditReader);

        $this->compile();

        $this->assertContainerBuilderHasServiceLocator(
            // NEXT_MAJOR: Change index from 1 to 0.
            (string) $this->container->getDefinition('sonata.admin.audit.manager')->getArgument(1),
            [
                'std_audit_reader' => new Reference('std_audit_reader'),
            ]
        );
    }

    public function testServiceTaggedMustImplementInterface(): void
    {
        $auditManagerDefinition = new Definition(AuditManager::class);

        $this->container
            ->setDefinition('sonata.admin.audit.manager', $auditManagerDefinition);

        $auditReader = new Definition(\stdClass::class);
        $auditReader
            ->addTag(AddAuditReadersCompilerPass::AUDIT_READER_TAG);

        $this->container
            ->setDefinition('std_audit_reader', $auditReader);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Service "std_audit_reader" MUST implement "Sonata\AdminBundle\Model\AuditReaderInterface".');

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddAuditReadersCompilerPass());
    }
}
