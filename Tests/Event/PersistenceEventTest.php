<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Event;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Event\PersistenceEvent;

class PersistenceEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PersistenceEvent
     */
    private $event;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var mixed
     */
    private $object;

    protected function setUp()
    {
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->object = new \stdClass();

        $this->event = new PersistenceEvent($this->admin, $this->object, 'Foo');
    }

    public function testGetType()
    {
        $this->assertSame('Foo', $this->event->getType());
    }

    public function testGetAdmin()
    {
        $result = $this->event->getAdmin();

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\AdminInterface', $result);
        $this->assertSame($this->admin, $result);
    }

    public function testGetObject()
    {
        $this->assertSame($this->object, $this->event->getObject());
    }
}
