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
use Sonata\AdminBundle\Route\QueryStringBuilder;
use Sonata\AdminBundle\Route\RouteCollection;

class QueryStringBuilderTest extends TestCase
{
    /**
     * @dataProvider getBuildTests
     */
    public function testBuild(array $expectedRoutes, bool $hasReader, bool $aclEnabled, ?AdminInterface $getParent): void
    {
        $audit = $this->getMockForAbstractClass(AuditManagerInterface::class);
        $audit->expects($this->once())->method('hasReader')->willReturn($hasReader);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->once())->method('getParent')->willReturn($getParent);
        $admin->method('getChildren')->willReturn([]);
        $admin->expects($this->once())->method('isAclEnabled')->willReturn($aclEnabled);

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $pathBuilder = new QueryStringBuilder($audit);

        $pathBuilder->build($admin, $routeCollection);

        $this->assertCount(\count($expectedRoutes), $routeCollection->getElements());

        foreach ($expectedRoutes as $expectedRoute) {
            $this->assertTrue($routeCollection->has($expectedRoute), sprintf('Expected route: "%s" doesn`t exist.', $expectedRoute));
        }
    }

    public function getBuildTests(): array
    {
        return [
            [['list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'history', 'history_view_revision', 'history_compare_revisions', 'acl'], true, true, null],
            [['list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'acl'], false, true, null],
            [['list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'history', 'history_view_revision', 'history_compare_revisions'], true, false, null],
            [['list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'history', 'history_view_revision', 'history_compare_revisions', 'acl'], true, true, $this->createMock(AdminInterface::class)],
        ];
    }

    public function testBuildWithChildren(): void
    {
        $audit = $this->getMockForAbstractClass(AuditManagerInterface::class);
        $audit->expects($this->once())->method('hasReader')->willReturn(true);

        $childRouteCollection1 = new RouteCollection('child1.Code.Route', 'child1RouteName', 'child1RoutePattern', 'child1ControllerName');
        $childRouteCollection1->add('foo');
        $childRouteCollection1->add('bar');

        $childRouteCollection2 = new RouteCollection('child2.Code.Route', 'child2RouteName', 'child2RoutePattern', 'child2ControllerName');
        $childRouteCollection2->add('baz');

        $child1 = $this->getMockForAbstractClass(AdminInterface::class);
        $child1->expects($this->once())->method('getRoutes')->willReturn($childRouteCollection1);

        $child2 = $this->getMockForAbstractClass(AdminInterface::class);
        $child2->expects($this->once())->method('getRoutes')->willReturn($childRouteCollection2);

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $admin->expects($this->once())->method('getParent')->willReturn(null);
        $admin->expects($this->once())->method('getChildren')->willReturn([$child1, $child2]);
        $admin->expects($this->once())->method('isAclEnabled')->willReturn(true);

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $pathBuilder = new QueryStringBuilder($audit);

        $pathBuilder->build($admin, $routeCollection);

        $expectedRoutes = ['list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'history', 'history_view_revision', 'history_compare_revisions', 'acl', 'child1.Code.Route.foo', 'child1.Code.Route.bar', 'child2.Code.Route.baz'];
        $this->assertCount(\count($expectedRoutes), $routeCollection->getElements());

        foreach ($expectedRoutes as $expectedRoute) {
            $this->assertTrue($routeCollection->has($expectedRoute), sprintf('Expected route: "%s" doesn`t exist.', $expectedRoute));
        }
    }
}
