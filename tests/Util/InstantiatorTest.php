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

namespace SensioLabs\AdminBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Exception\AbstractClassException;
use SensioLabs\AdminBundle\Tests\Fixtures\Entity\AbstractEntity;
use SensioLabs\AdminBundle\Tests\Fixtures\Entity\Bar;
use SensioLabs\AdminBundle\Util\Instantiator;

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
