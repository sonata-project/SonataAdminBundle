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

use Sonata\AdminBundle\Route\Handler\RouteIdHandler;

class RouteIdHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIdFromRequest()
    {
        $idParameter = 'id';
        $requestId = 1;

        $adminInterface = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\AdminInterface');
        $adminInterface->expects($this->once())
            ->method('getIdParameter')
            ->will($this->returnValue($idParameter));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->getMock();
        $request->expects($this->once())
            ->method('get')
            ->with($idParameter)
            ->will($this->returnValue($requestId));

        $routeIdHandler = new RouteIdHandler();
        $returnId = $routeIdHandler->getIdFromRequest($request, $adminInterface);
        $this->assertEquals($requestId, $returnId);
    }
}
