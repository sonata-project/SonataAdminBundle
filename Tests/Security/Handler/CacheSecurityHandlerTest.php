<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Security\Handler;

use Sonata\AdminBundle\Security\Handler\CacheSecurityHandler;

class CacheSecurityHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsGranted()
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $object = new \stdClass();

        $decorated = $this->getMock('Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface');
        $decorated->expects($this->once())
            ->method('isGranted')
            ->with($this->identicalTo($admin), 'CREATE', $this->identicalTo($object))
            ->willReturn(true)
        ;

        $securityHandler = new CacheSecurityHandler($decorated);

        $this->assertTrue($securityHandler->isGranted($admin, 'CREATE', $object));
        $this->assertTrue($securityHandler->isGranted($admin, 'CREATE', $object));
    }
}
