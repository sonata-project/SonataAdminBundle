<?php

/*
 * This file is part of the Sonata package.
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

    public function testBuild()
    {
        $audit = $this->getMock('Sonata\AdminBundle\Model\AuditManagerInterface');
        $audit->expects($this->once())->method('hasReader')->will($this->returnValue(true));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getChildren')->will($this->returnValue(array()));

        $routeCollection = new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'baseControllerName');

        $pathBuilder = new QueryStringBuilder($audit);

        $pathBuilder->build($admin, $routeCollection);

        $this->assertCount(9,$routeCollection->getElements());
    }
}
