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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Event\ConfigureQueryEvent;

class ConfigureQueryEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigureQueryEvent
     */
    private $event;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var ProxyQueryInterface
     */
    private $proxyQuery;

    protected function setUp()
    {
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->proxyQuery = $this->getMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface');

        $this->event = new ConfigureQueryEvent($this->admin, $this->proxyQuery, 'Foo');
    }

    public function testGetContext()
    {
        $this->assertEquals('Foo', $this->event->getContext());
    }

    public function testGetAdmin()
    {
        $result = $this->event->getAdmin();

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\AdminInterface', $result);
        $this->assertEquals($this->admin, $result);
    }

    public function testGetProxyQuery()
    {
        $result = $this->event->getProxyQuery();

        $this->assertInstanceOf('Sonata\AdminBundle\Datagrid\ProxyQueryInterface', $result);
        $this->assertEquals($this->proxyQuery, $result);
    }
}