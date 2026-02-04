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

namespace SensioLabs\AdminBundle\Tests\Route;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Route\RoutesCache;
use SensioLabs\AdminBundle\Route\RoutesCacheWarmUp;
use Symfony\Component\DependencyInjection\Container;

final class RoutesCacheWarmUpTest extends TestCase
{
    private RoutesCacheWarmUp $routesCacheWarmUp;

    protected function setUp(): void
    {
        $pool = new Pool(new Container());

        $this->routesCacheWarmUp = new RoutesCacheWarmUp(new RoutesCache('test', false), $pool);
    }

    public function testIsOptional(): void
    {
        static::assertTrue($this->routesCacheWarmUp->isOptional());
    }
}
