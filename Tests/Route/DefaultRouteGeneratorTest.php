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

use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Symfony\Component\Routing\Route;

class DefaultRouteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->will($this->returnValue('/foo/bar'));

        $generator = new DefaultRouteGenerator($router);

        $this->assertEquals('/foo/bar', $generator->generate('foo_bar'));
    }

    /**
     * @dataProvider getGenerateUrlTests
     */
    public function testGenerateUrl($expected, $name, array $parameters)
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('isChild')->will($this->returnValue(false));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Route'));
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('foo_code'));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(array('abc'=>'a123', 'efg'=>'e456')));

        $route1 = new Route('/foo');
        $route1->setDefault('_sonata_name', 'admin_acme_foo');

        $route2 = new Route('/foo/bar');
        $route2->setDefault('_sonata_name', 'admin_acme_bar');

        $admin->expects($this->once())
            ->method('getRoute')
            ->will($this->returnCallback(function($name) use ($route1, $route2) {
                switch ($name) {
                    case 'base.Code.Route.foo':
                        return $route1;
                    case 'base.Code.Route|foo.bar':
                        return $route2;
                }

                return false;
            }));

        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function($name, array $parameters = array())  {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return '/foo'.$params;
                    case 'admin_acme_bar':
                        return '/foo/bar'.$params;
                }

                return null;
            }));

        $generator = new DefaultRouteGenerator($router);

        $this->assertEquals($expected, $generator->generateUrl($admin, $name, $parameters));
    }

    public function getGenerateUrlTests()
    {
        return array(
            array('/foo?abc=a123&efg=e456&default_param=default_val', 'foo', array('default_param'=>'default_val')),
            array('/foo/bar?abc=a123&efg=e456&default_param=default_val', 'foo.bar', array('default_param'=>'default_val')),
        );
    }

    public function testGenerateUrlWithException()
    {
        $this->setExpectedException('RuntimeException', 'unable to find the route `base.Code.Route.foo`');

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('isChild')->will($this->returnValue(false));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Route'));
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(array()));
        $admin->expects($this->once())->method('getRoute')->will($this->returnValue(false));

        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');

        $generator = new DefaultRouteGenerator($router);
        $generator->generateUrl($admin, 'foo', array());
    }

    /**
     * @dataProvider getGenerateUrlChildTests
     */
    public function testGenerateUrlChild($expected, $name, array $parameters)
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('isChild')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Route'));
        $admin->expects($this->any())->method('getBaseCodeRoute')->will($this->returnValue('baseChild.Code.Route'));
        $admin->expects($this->any())->method('getIdParameter')->will($this->returnValue('id'));
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('foo_code'));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(array('abc'=>'a123', 'efg'=>'e456')));

        $parentAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $parentAdmin->expects($this->any())->method('getIdParameter')->will($this->returnValue('childId'));

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->expects($this->once())
            ->method('get')
            ->will($this->returnCallback(function($key) {
                if ($key == 'childId') {
                    return '987654';
                }

                return null;
            }));
        $admin->expects($this->any())->method('getRequest')->will($this->returnValue($request));

        $admin->expects($this->any())->method('getParent')->will($this->returnValue($parentAdmin));

        $route1 = new Route('/foo');
        $route1->setDefault('_sonata_name', 'admin_acme_foo');

        $route2 = new Route('/foo/bar');
        $route2->setDefault('_sonata_name', 'admin_acme_bar');

        $admin->expects($this->once())
            ->method('getRoute')
            ->will($this->returnCallback(function($name) use ($route1, $route2) {
                switch ($name) {
                    case 'baseChild.Code.Route.foo':
                        return $route1;
                    case 'baseChild.Code.Route.foo.bar':
                        return $route2;
                }

                return false;
            }));

        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function($name, array $parameters = array())  {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return '/foo'.$params;
                    case 'admin_acme_bar':
                        return '/foo/bar'.$params;
                }

                return null;
            }));

        $generator = new DefaultRouteGenerator($router);

        $this->assertEquals($expected, $generator->generateUrl($admin, $name, $parameters));
    }

    public function getGenerateUrlChildTests()
    {
        return array(
            array('/foo?abc=a123&efg=e456&default_param=default_val&childId=987654', 'foo', array('id'=>123, 'default_param'=>'default_val')),
            array('/foo/bar?abc=a123&efg=e456&default_param=default_val&childId=987654', 'foo.bar', array('id'=>123, 'default_param'=>'default_val')),
        );
    }

    /**
     * @dataProvider getGenerateUrlParentFieldDescriptionTests
     */
    public function testGenerateUrlParentFieldDescription($expected, $name, array $parameters)
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('isChild')->will($this->returnValue(false));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Route'));
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(true));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('foo_code'));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(array('abc'=>'a123', 'efg'=>'e456')));

        $route1 = new Route('/foo');
        $route1->setDefault('_sonata_name', 'admin_acme_foo');

        $route2 = new Route('/foo/bar');
        $route2->setDefault('_sonata_name', 'admin_acme_bar');

        $admin->expects($this->once())
            ->method('getRoute')
            ->will($this->returnCallback(function($name) use ($route1, $route2) {
                switch ($name) {
                    case 'base.Code.Route.foo':
                        return $route1;
                    case 'base.Code.Route|foo.bar':
                        return $route2;
                }

                return false;
            }));

        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function($name, array $parameters = array())  {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return '/foo'.$params;
                    case 'admin_acme_bar':
                        return '/foo/bar'.$params;
                }

                return null;
            }));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue(array()));

        $parentAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $parentAdmin->expects($this->any())->method('getUniqid')->will($this->returnValue('parent_foo_uniqueid'));
        $parentAdmin->expects($this->any())->method('getCode')->will($this->returnValue('parent_foo_code'));

        $fieldDescription->expects($this->any())->method('getAdmin')->will($this->returnValue($parentAdmin));
        $admin->expects($this->any())->method('getParentFieldDescription')->will($this->returnValue($fieldDescription));

        $generator = new DefaultRouteGenerator($router);

        $this->assertEquals($expected, $generator->generateUrl($admin, $name, $parameters));
    }

    public function getGenerateUrlParentFieldDescriptionTests()
    {
        return array(
            array('/foo?abc=a123&efg=e456&default_param=default_val&uniqid=foo_uniqueid&code=base.Code.Route&pcode=parent_foo_code&puniqid=parent_foo_uniqueid', 'foo', array('default_param'=>'default_val')),
            array('/foo/bar?abc=a123&efg=e456&default_param=default_val&uniqid=foo_uniqueid&code=base.Code.Route&pcode=parent_foo_code&puniqid=parent_foo_uniqueid', 'foo.bar', array('default_param'=>'default_val')),
        );
    }
}
