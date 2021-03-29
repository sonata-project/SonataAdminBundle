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

namespace Sonata\AdminBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Model\AuditManager;
use Sonata\AdminBundle\Model\AuditReaderInterface;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class AuditManagerTest extends TestCase
{
    // NEXT_MAJOR: Remove next line.
    use ExpectDeprecationTrait;

    public function testGetReader(): void
    {
        $container = new Container();

        $fooReader = $this->createStub(AuditReaderInterface::class);
        $barReader = $this->createStub(AuditReaderInterface::class);

        $container->set('foo_reader', $fooReader);
        $container->set('bar_reader', $barReader);

        $auditManager = new AuditManager($container);

        /** @var class-string $foo1 */
        $foo1 = 'Foo\Foo1';
        /** @var class-string $foo2 */
        $foo2 = 'Foo\Foo2';

        $this->assertFalse($auditManager->hasReader($foo1));

        $auditManager->setReader('foo_reader', [$foo1, $foo2]);

        $this->assertTrue($auditManager->hasReader($foo1));
        $this->assertSame($fooReader, $auditManager->getReader($foo1));
    }

    public function testGetReaderWithException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The class "Foo\Foo" does not have any reader manager');

        $auditManager = new AuditManager(new Container());

        /** @var class-string $foo */
        $foo = 'Foo\Foo';
        $auditManager->getReader($foo);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testReaderShouldBeTagged(): void
    {
        $container = new Container();

        $fooReader = $this->createStub(AuditReaderInterface::class);

        $container->set('foo_reader', $fooReader);

        $auditManager = new AuditManager($container, new Container());

        $this->expectDeprecation('Not registering the audit reader "foo_reader" with tag "sonata.admin.audit_reader" is deprecated since sonata-project/admin-bundle 3.95 and will not work in 4.0. You MUST add "sonata.admin.audit_reader" tag to the service "foo_reader".');

        $auditManager->setReader('foo_reader', ['Foo\Foo1', 'Foo\Foo2']);
    }
}
