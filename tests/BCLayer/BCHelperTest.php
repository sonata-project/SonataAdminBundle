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

namespace Sonata\AdminBundle\Tests\BCLayer;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\BCLayer\BCHelper;
use Sonata\AdminBundle\Model\ProxyResolverInterface;
use Sonata\AdminBundle\Tests\Fixtures\Proxy\__CG__\stdClass as Proxy;

final class BCHelperTest extends TestCase
{
    public function testGetClass(): void
    {
        static::assertSame(\stdClass::class, BCHelper::getClass(new \stdClass()));
        static::assertSame(self::class, BCHelper::getClass($this));
    }

    public function testGetProxyClass(): void
    {
        $this->expectUserDeprecationMessage(\sprintf(
            'Using proxy class "%s" without a model manager which implements %s is deprecated'
            .' since sonata-project/admin-bundle version 4.17 and will not work in 5.0 version.',
            Proxy::class,
            ProxyResolverInterface::class
        ));

        static::assertSame(\stdClass::class, BCHelper::getClass(new Proxy()));
    }
}
