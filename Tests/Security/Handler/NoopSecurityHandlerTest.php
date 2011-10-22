<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\AdminBundle\Tests\Admin\Security\Acl\Permission;

use Sonata\AdminBundle\Security\Handler\NoopSecurityHandler;

class NoopSecurityHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Sonata\AdminBundle\Security\Handler\NoopSecurityHandler
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

    /**
     * @return Sonata\AdminBundle\Admin\AdminInterface
     */
    private function getSonataAdminObject()
    {
        return $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
    }
}
