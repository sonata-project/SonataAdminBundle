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

namespace SensioLabs\AdminBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Model\AuditManager;
use SensioLabs\AdminBundle\Model\AuditReaderInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class AuditManagerTest extends TestCase
{
    public function testGetReader(): void
    {
        $container = new Container();

        $fooReader = static::createStub(AuditReaderInterface::class);
        $barReader = static::createStub(AuditReaderInterface::class);

        $container->set('foo_reader', $fooReader);
        $container->set('bar_reader', $barReader);

        $auditManager = new AuditManager($container);

        $foo1 = Foo1::class;
        $foo2 = Foo2::class;

        static::assertFalse($auditManager->hasReader($foo1));

        $auditManager->setReader('foo_reader', [$foo1, $foo2]);

        static::assertTrue($auditManager->hasReader($foo1));
        static::assertSame($fooReader, $auditManager->getReader($foo1));
    }

    public function testGetReaderWithException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('The class "%s" does not have any reader manager', Foo1::class));

        $auditManager = new AuditManager(new Container());

        $foo = Foo1::class;
        $auditManager->getReader($foo);
    }
}

final class Foo1
{
}
final class Foo2
{
}
