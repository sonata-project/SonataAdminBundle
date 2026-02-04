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
use SensioLabs\AdminBundle\Event\PersistenceEvent;

final class PersistenceEventTest extends TestCase
{
    /**
     * @var PersistenceEvent<object>
     */
    private PersistenceEvent $event;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    private object $object;

    protected function setUp(): void
    {
        $object = new \stdClass();

        $this->admin = $this->createMock(AdminInterface::class);
        $this->object = $object;

        $this->event = new PersistenceEvent($this->admin, $this->object, 'Foo');
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

    public function testGetObject(): void
    {
        static::assertSame($this->object, $this->event->getObject());
    }
}
