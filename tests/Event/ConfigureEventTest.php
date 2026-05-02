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
use Sonata\AdminBundle\Event\ConfigureEvent;
use Sonata\AdminBundle\Mapper\MapperInterface;

final class ConfigureEventTest extends TestCase
{
    /**
     * @var ConfigureEvent<object>
     */
    private ConfigureEvent $event;

    protected function setUp(): void
    {
        $this->event = new ConfigureEvent(static::createStub(AdminInterface::class), static::createStub(MapperInterface::class), 'Foo');
    }

    public function testGetType(): void
    {
        static::assertSame('Foo', $this->event->getType());
    }

    public function testGetAdmin(): void
    {
        $result = $this->event->getAdmin();

        static::assertInstanceOf(AdminInterface::class, $result);
        static::assertSame(static::createStub(AdminInterface::class), $result);
    }

    public function testGetMapper(): void
    {
        $result = $this->event->getMapper();

        static::assertInstanceOf(MapperInterface::class, $result);
        static::assertSame(static::createStub(MapperInterface::class), $result);
    }
}
