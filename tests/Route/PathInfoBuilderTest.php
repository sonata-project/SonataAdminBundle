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
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Model\AuditManagerInterface;
use SensioLabs\AdminBundle\Route\PathInfoBuilder;
use SensioLabs\AdminBundle\Route\RouteCollection;

final class PathInfoBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $audit = $this->createMock(AuditManagerInterface::class);
        $audit->expects(static::once())->method('hasReader')->willReturn(true);

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects(static::once())->method('getChildren')->willReturn([]);
        $admin->expects(static::once())->method('isAclEnabled')->willReturn(true);

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $pathBuilder = new PathInfoBuilder($audit);

        $pathBuilder->build($admin, $routeCollection);

        static::assertCount(11, $routeCollection->getElements());
    }
}
