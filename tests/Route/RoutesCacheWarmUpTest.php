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

class RoutesCacheWarmUpTest extends TestCase
{
    /**
     * @var RoutesCacheWarmUp
     */
    protected $routesCacheWarmUp;

    public function setUp()
    {
        $routesCache = $this->getMockBuilder(RoutesCache::class)->disableOriginalConstructor()->getMock();
        $pool = $this->getMockBuilder(Pool::class)->disableOriginalConstructor()->getMock();

        $this->routesCacheWarmUp = new RoutesCacheWarmUp($routesCache, $pool);
    }

    public function testIsOptional()
    {
        $this->assertTrue($this->routesCacheWarmUp->isOptional());
    }
}
