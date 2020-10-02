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
use Symfony\Component\DependencyInjection\Container;

/**
 * Test for AuditManager.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class AuditManagerTest extends TestCase
{
    public function testGetReader(): void
    {
        $container = new Container();

        $fooReader = $this->getMockForAbstractClass(AuditReaderInterface::class);
        $barReader = $this->getMockForAbstractClass(AuditReaderInterface::class);

        $container->set('foo_reader', $fooReader);
        $container->set('bar_reader', $barReader);

        $auditManager = new AuditManager($container);

        $this->assertFalse($auditManager->hasReader('Foo\Foo1'));

        $auditManager->setReader('foo_reader', ['Foo\Foo1', 'Foo\Foo2']);

        $this->assertTrue($auditManager->hasReader('Foo\Foo1'));
        $this->assertSame($fooReader, $auditManager->getReader('Foo\Foo1'));
    }

    public function testGetReaderWithException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The class "Foo\Foo" does not have any reader manager');

        $auditManager = new AuditManager(new Container());

        $auditManager->getReader('Foo\Foo');
    }
}
