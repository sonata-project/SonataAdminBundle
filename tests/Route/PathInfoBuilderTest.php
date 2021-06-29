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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Model\AuditManagerInterface;
use Sonata\AdminBundle\Route\PathInfoBuilder;
use Sonata\AdminBundle\Route\RouteCollection;

class PathInfoBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $audit = $this->getMockForAbstractClass(AuditManagerInterface::class);
        $audit->expects(self::once())->method('hasReader')->willReturn(true);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects(self::once())->method('getChildren')->willReturn([]);
        $admin->expects(self::once())->method('isAclEnabled')->willReturn(true);

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $pathBuilder = new PathInfoBuilder($audit);

        $pathBuilder->build($admin, $routeCollection);

        self::assertCount(11, $routeCollection->getElements());
    }
}
