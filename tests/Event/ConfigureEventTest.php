<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Event;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Event\ConfigureEvent;
use SensioLabs\AdminBundle\Mapper\MapperInterface;

final class ConfigureEventTest extends TestCase
{
    /**
     * @var ConfigureEvent<object>
     */
    private ConfigureEvent $event;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    /**
     * @var MapperInterface<object>&MockObject
     */
    private MapperInterface $mapper;

    protected function setUp(): void
    {
        $this->admin = $this->createMock(AdminInterface::class);
        $this->mapper = $this->createMock(MapperInterface::class);

        $this->event = new ConfigureEvent($this->admin, $this->mapper, 'Foo');
    }

    public function testGetType(): void
    {
        static::assertSame('Foo', $this->event->getType());
    }

    public function testGetAdmin(): void
    {
        $result = $this->event->getAdmin();

        static::assertInstanceOf(AdminInterface::class, $result);
        static::assertSame($this->admin, $result);
    }

    public function testGetMapper(): void
    {
        $result = $this->event->getMapper();

        static::assertInstanceOf(MapperInterface::class, $result);
        static::assertSame($this->mapper, $result);
    }
}
