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

use Sonata\AdminBundle\Route\PathInfoBuilder;
use Sonata\AdminBundle\Route\RouteCollection;

class PathInfoBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $audit = $this->getMock('Sonata\AdminBundle\Model\AuditManagerInterface');
        $audit->expects($this->once())->method('hasReader')->will($this->returnValue(true));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getChildren')->will($this->returnValue(array()));
        $admin->expects($this->once())->method('isAclEnabled')->will($this->returnValue(true));

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $pathBuilder = new PathInfoBuilder($audit);

        $pathBuilder->build($admin, $routeCollection);

        $this->assertCount(11, $routeCollection->getElements());
    }
}
