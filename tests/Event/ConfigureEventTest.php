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

class ConfigureEventTest extends TestCase
{
    /**
     * @var ConfigureEvent
     */
    private $event;

    /**
     * @var AdminInterface<object>
     */
    private $admin;

    /**
     * @var MapperInterface
     */
    private $mapper;

    protected function setUp(): void
    {
        $this->admin = $this->createMock(AdminInterface::class);
        $this->mapper = $this->createMock(MapperInterface::class);

        $this->event = new ConfigureEvent($this->admin, $this->mapper, 'Foo');
    }

    public function testGetType(): void
    {
        $this->assertSame('Foo', $this->event->getType());
    }

    public function testGetAdmin(): void
    {
        $result = $this->event->getAdmin();

        $this->assertInstanceOf(AdminInterface::class, $result);
        $this->assertSame($this->admin, $result);
    }

    public function testGetMapper(): void
    {
        $result = $this->event->getMapper();

        $this->assertInstanceOf(MapperInterface::class, $result);
        $this->assertSame($this->mapper, $result);
    }
}
