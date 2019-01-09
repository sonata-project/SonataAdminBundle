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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test for AuditManager.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class AuditManagerTest extends TestCase
{
    public function testGetReader()
    {
        $container = $this->getMockForAbstractClass(ContainerInterface::class);

        $fooReader = $this->getMockForAbstractClass(AuditReaderInterface::class);
        $barReader = $this->getMockForAbstractClass(AuditReaderInterface::class);

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($fooReader, $barReader) {
                switch ($id) {
                    case 'foo_reader':
                        return $fooReader;

                    case 'bar_reader':
                        return $barReader;
                }
            }));

        $auditManager = new AuditManager($container);

        $this->assertFalse($auditManager->hasReader('Foo\Foo1'));

        $auditManager->setReader('foo_reader', ['Foo\Foo1', 'Foo\Foo2']);

        $this->assertTrue($auditManager->hasReader('Foo\Foo1'));
        $this->assertSame($fooReader, $auditManager->getReader('Foo\Foo1'));
    }

    public function testGetReaderWithException()
    {
        $this->expectException(\RuntimeException::class, 'The class "Foo\Foo" does not have any reader manager');

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $auditManager = new AuditManager($container);

        $auditManager->getReader('Foo\Foo');
    }
}
