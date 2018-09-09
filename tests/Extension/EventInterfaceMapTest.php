<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Extension;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Extension\Event\EventInterface;
use Sonata\AdminBundle\Extension\EventInterfaceMap;

class EventInterfaceMapTest extends TestCase
{
    public function testBadEvent()
    {
        $this->expectException(InvalidArgumentException::class);

        EventInterfaceMap::get($this->createMock(EventInterface::class));
    }
}
