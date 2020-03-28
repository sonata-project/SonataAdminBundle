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

namespace Sonata\AdminBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Event\ConfigureQueryEvent;

class ConfigureQueryEventTest extends TestCase
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

    protected function setUp(): void
    {
        $this->admin = $this->getMockForAbstractClass(AdminInterface::class);
        $this->proxyQuery = $this->getMockForAbstractClass(ProxyQueryInterface::class);

        $this->event = new ConfigureQueryEvent($this->admin, $this->proxyQuery, 'Foo');
    }

    public function testGetAdmin(): void
    {
        $result = $this->event->getAdmin();

        $this->assertInstanceOf(AdminInterface::class, $result);
        $this->assertSame($this->admin, $result);
    }

    public function testGetProxyQuery(): void
    {
        $result = $this->event->getProxyQuery();

        $this->assertInstanceOf(ProxyQueryInterface::class, $result);
        $this->assertSame($this->proxyQuery, $result);
    }
}
