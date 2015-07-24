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

use Sonata\AdminBundle\Route\QueryStringBuilder;
use Sonata\AdminBundle\Route\RouteCollection;

class QueryStringBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getBuildTests
     */
    public function testBuild(array $expectedRoutes, $hasReader, $aclEnabled, $getParent)
    {
        $audit = $this->getMock('Sonata\AdminBundle\Model\AuditManagerInterface');
        $audit->expects($this->once())->method('hasReader')->will($this->returnValue($hasReader));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getParent')->will($this->returnValue($getParent));
        $admin->expects($this->any())->method('getChildren')->will($this->returnValue(array()));
        $admin->expects($this->once())->method('isAclEnabled')->will($this->returnValue($aclEnabled));

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $pathBuilder = new QueryStringBuilder($audit);

        $pathBuilder->build($admin, $routeCollection);

        $this->assertCount(count($expectedRoutes), $routeCollection->getElements());

        foreach ($expectedRoutes as $expectedRoute) {
            $this->assertTrue($routeCollection->has($expectedRoute), sprintf('Expected route: "%s" doesn`t exist.', $expectedRoute));
        }
    }

    public function getBuildTests()
    {
        return array(
            array(array('list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'history', 'history_view_revision', 'history_compare_revisions', 'acl'), true, true, null),
            array(array('list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'acl'), false, true, null),
            array(array('list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'history', 'history_view_revision', 'history_compare_revisions'), true, false, null),
            array(array('list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'history', 'history_view_revision', 'history_compare_revisions', 'acl'), true, true, $this->getMock('Sonata\AdminBundle\Admin\AdminInterface')),
        );
    }

    public function testBuildWithChildren()
    {
        $audit = $this->getMock('Sonata\AdminBundle\Model\AuditManagerInterface');
        $audit->expects($this->once())->method('hasReader')->will($this->returnValue(true));

        $childRouteCollection1 = new RouteCollection('child1.Code.Route', 'child1RouteName', 'child1RoutePattern', 'child1ControllerName');
        $childRouteCollection1->add('foo');
        $childRouteCollection1->add('bar');

        $childRouteCollection2 = new RouteCollection('child2.Code.Route', 'child2RouteName', 'child2RoutePattern', 'child2ControllerName');
        $childRouteCollection2->add('baz');

        $child1 = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $child1->expects($this->once())->method('getRoutes')->will($this->returnValue($childRouteCollection1));

        $child2 = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $child2->expects($this->once())->method('getRoutes')->will($this->returnValue($childRouteCollection2));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getParent')->will($this->returnValue(null));
        $admin->expects($this->once())->method('getChildren')->will($this->returnValue(array($child1, $child2)));
        $admin->expects($this->once())->method('isAclEnabled')->will($this->returnValue(true));

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $pathBuilder = new QueryStringBuilder($audit);

        $pathBuilder->build($admin, $routeCollection);

        $expectedRoutes = array('list', 'create', 'batch', 'edit', 'delete', 'show', 'export', 'history', 'history_view_revision', 'history_compare_revisions', 'acl', 'child1.Code.Route.foo', 'child1.Code.Route.bar', 'child2.Code.Route.baz');
        $this->assertCount(count($expectedRoutes), $routeCollection->getElements());

        foreach ($expectedRoutes as $expectedRoute) {
            $this->assertTrue($routeCollection->has($expectedRoute), sprintf('Expected route: "%s" doesn`t exist.', $expectedRoute));
        }
    }
}
