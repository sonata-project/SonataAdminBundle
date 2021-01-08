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

namespace Sonata\AdminBundle\Tests\Route;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Route\RoutesCache;
use Sonata\AdminBundle\Route\RoutesCacheWarmUp;
use Symfony\Component\DependencyInjection\Container;

class RoutesCacheWarmUpTest extends TestCase
{
    /**
     * @var RoutesCacheWarmUp
     */
    protected $routesCacheWarmUp;

    protected function setUp(): void
    {
        $routesCache = $this->getMockBuilder(RoutesCache::class)->disableOriginalConstructor()->getMock();
        $pool = new Pool(new Container());

        $this->routesCacheWarmUp = new RoutesCacheWarmUp($routesCache, $pool);
    }

    public function testIsOptional(): void
    {
        $this->assertTrue($this->routesCacheWarmUp->isOptional());
    }
}
