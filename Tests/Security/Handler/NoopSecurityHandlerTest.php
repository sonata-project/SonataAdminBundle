<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\AdminBundle\Tests\Security\Handler;

use Sonata\AdminBundle\Security\Handler\NoopSecurityHandler;
use Sonata\AdminBundle\Admin\AdminInterface;

class NoopSecurityHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NoopSecurityHandler
     */
    private $handler = null;

    public function setUp()
    {
        $this->handler = new NoopSecurityHandler();
    }

    public function testIsGranted()
    {
        $this->assertTrue($this->handler->isGranted($this->getSonataAdminObject(), array('TOTO')));
        $this->assertTrue($this->handler->isGranted($this->getSonataAdminObject(), 'TOTO'));
    }

    public function testBuildSecurityInformation()
    {
        $this->assertEquals(array(), $this->handler->buildSecurityInformation($this->getSonataAdminObject()));
    }

    public function testCreateObjectSecurity()
    {
        $this->assertNull($this->handler->createObjectSecurity($this->getSonataAdminObject(), new \stdClass()));
    }

    public function testDeleteObjectSecurity()
    {
        $this->assertNull($this->handler->deleteObjectSecurity($this->getSonataAdminObject(), new \stdClass()));
    }

    public function testGetBaseRole()
    {
        $this->assertEquals('', $this->handler->getBaseRole($this->getSonataAdminObject()));
    }

    /**
     * @return AdminInterface
     */
    private function getSonataAdminObject()
    {
        return $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
    }
}
