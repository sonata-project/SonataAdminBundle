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
use Sonata\AdminBundle\Event\PersistenceEvent;

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
