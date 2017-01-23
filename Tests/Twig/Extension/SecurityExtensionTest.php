<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Twig\Extension\SecurityExtension;

class SecurityExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var SecurityHandlerInterface
     */
    private $securityHandler;

    public function setUp()
    {
        $this->securityHandler = $this->getMock('Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface');
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->admin->method('getSecurityHandler')->willReturn($this->securityHandler);
    }

    public function testIsGranted()
    {
        $this->securityHandler
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->identicalTo($this->admin), 'LIST', $this->identicalTo($this->admin))
            ->willReturn(true)
        ;

        $extension = new SecurityExtension();
        $extension->setAdmin($this->admin);
        $this->assertTrue($extension->isGranted('LIST'));
    }

    public function testIsGrantedWithObject()
    {
        $object = new \stdClass();

        $this->securityHandler
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->identicalTo($this->admin), 'EDIT', $this->identicalTo($object))
            ->willReturn(true)
        ;

        $extension = new SecurityExtension();
        $extension->setAdmin($this->admin);
        $this->assertTrue($extension->isGranted('EDIT', $object));
    }

    public function testIsGrantedWithAdmin()
    {
        $otherAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $otherAdmin
            ->expects($this->once())
            ->method('getSecurityHandler')
            ->willReturn($this->securityHandler)
        ;

        $this->securityHandler
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->identicalTo($otherAdmin), 'CREATE', $this->identicalTo($otherAdmin))
            ->willReturn(true)
        ;

        $extension = new SecurityExtension();
        $extension->setAdmin($this->admin);
        $this->assertTrue($extension->isGranted('CREATE', null, $otherAdmin));
    }
}
