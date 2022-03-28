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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Event\ConfigureQueryEvent;

final class ConfigureQueryEventTest extends TestCase
{
    private ConfigureQueryEvent $event;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private $admin;

    /**
     * @var ProxyQueryInterface&MockObject
     */
    private $proxyQuery;

    protected function setUp(): void
    {
        $this->admin = $this->createMock(AdminInterface::class);
        $this->proxyQuery = $this->createMock(ProxyQueryInterface::class);

        $this->event = new ConfigureQueryEvent($this->admin, $this->proxyQuery, 'Foo');
    }

    public function testGetContext(): void
    {
        static::assertSame('Foo', $this->event->getContext());
    }

    public function testGetAdmin(): void
    {
        $result = $this->event->getAdmin();

        static::assertInstanceOf(AdminInterface::class, $result);
        static::assertSame($this->admin, $result);
    }

    public function testGetProxyQuery(): void
    {
        $result = $this->event->getProxyQuery();

        static::assertInstanceOf(ProxyQueryInterface::class, $result);
        static::assertSame($this->proxyQuery, $result);
    }
}
