<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\AdminBundle\Model\AuditManager;

/**
 * Test for AuditManager
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class AuditManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetReader()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $fooReader = $this->getMock('Sonata\AdminBundle\Model\AuditReaderInterface');
        $barReader = $this->getMock('Sonata\AdminBundle\Model\AuditReaderInterface');

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($id) use ($fooReader, $barReader) {
                switch ($id) {
                    case 'foo_reader':
                        return $fooReader;
                        break;

                    case 'bar_reader':
                        return $barReader;
                        break;
                }

                return null;
            }));

        $auditManager = new AuditManager($container);

        $this->assertFalse($auditManager->hasReader('Foo\Foo1'));

        $auditManager->setReader('foo_reader', array('Foo\Foo1', 'Foo\Foo2'));

        $this->assertTrue($auditManager->hasReader('Foo\Foo1'));
        $this->assertSame($fooReader, $auditManager->getReader('Foo\Foo1'));
    }

    public function testGetReaderWithException()
    {
        $this->setExpectedException('\RuntimeException', 'The class "Foo\Foo" does not have any reader manager');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $auditManager = new AuditManager($container);

        $auditManager->getReader('Foo\Foo');
    }
}
