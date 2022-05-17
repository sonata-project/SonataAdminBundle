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

namespace Sonata\AdminBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Exception\AbstractClassException;
use Sonata\AdminBundle\Tests\Fixtures\Entity\AbstractEntity;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Bar;
use Sonata\AdminBundle\Util\Instantiator;

/**
 * @author Morgan Abraham <morgan@geekimo.me>
 */
final class InstantiatorTest extends TestCase
{
    public function testAbstractClassThrowsException(): void
    {
        $this->expectException(AbstractClassException::class);

        Instantiator::instantiate(AbstractEntity::class);
    }

    public function testNotAbstractClassDoesntThrowsException(): void
    {
        static::assertInstanceOf(Bar::class, Instantiator::instantiate(Bar::class));
    }
}
