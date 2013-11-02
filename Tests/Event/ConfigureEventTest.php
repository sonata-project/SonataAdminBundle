<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Event;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Event\ConfigureEvent;

class ConfigureEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigureEvent
     */
    private $event;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var BaseMapper
     */
    private $mapper;

    protected function setUp()
    {
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->mapper = $this->getMockBuilder('Sonata\AdminBundle\Mapper\BaseMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = new ConfigureEvent($this->admin, $this->mapper, 'Foo');
    }

    public function testGetType()
    {
        $this->assertEquals('Foo', $this->event->getType());
    }

    public function testGetAdmin()
    {
        $result = $this->event->getAdmin();

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\AdminInterface', $result);
        $this->assertEquals($this->admin, $result);
    }

    public function testGetMapper()
    {
        $result = $this->event->getMapper();

        $this->assertInstanceOf('Sonata\AdminBundle\Mapper\BaseMapper', $result);
        $this->assertEquals($this->mapper, $result);
    }
}
