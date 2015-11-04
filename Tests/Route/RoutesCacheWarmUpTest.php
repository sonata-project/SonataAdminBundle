<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Route;

use Sonata\AdminBundle\Route\RoutesCacheWarmUp;

class RoutesCacheWarmUpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RoutesCacheWarmUp
     */
    protected $routesCacheWarmUp;

    public function setUp()
    {
        $routesCache = $this->getMockBuilder('Sonata\AdminBundle\Route\RoutesCache')->disableOriginalConstructor()->getMock();
        $pool        = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();

        $this->routesCacheWarmUp = new RoutesCacheWarmUp($routesCache, $pool);
    }

    public function testIsOptional()
    {
        $this->assertTrue($this->routesCacheWarmUp->isOptional());
    }
}
