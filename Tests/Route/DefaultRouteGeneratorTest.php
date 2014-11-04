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
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RoutesCache;
use Symfony\Component\Routing\Route;

class DefaultRouteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $cacheTempFolder;

    public function setUp()
    {
        $this->cacheTempFolder = sys_get_temp_dir().'/sonata_test_route';

        exec('rm -rf '.$this->cacheTempFolder);
    }

    public function testGenerate()
    {
        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->will($this->returnValue('/foo/bar'));

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertEquals('/foo/bar', $generator->generate('foo_bar'));
    }

    /**
     * @dataProvider getGenerateUrlTests
     */
    public function testGenerateUrl($expected, $name, array $parameters)
    {
        $childCollection = new RouteCollection('base.Code.Foo|base.Code.Bar', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Foo', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->any())->method('isChild')->will($this->returnValue(false));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Foo'));
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('foo_code'));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(array('abc'=>'a123', 'efg'=>'e456')));
        $admin->expects($this->any())->method('getRoutes')->will($this->returnValue($collection));
        $admin->expects($this->any())->method('getExtensions')->will($this->returnValue(array()));

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function($name, array $parameters = array()) {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return '/foo'.$params;
                    case 'admin_acme_child_bar':
                        return '/foo/bar'.$params;
                }

                return null;
            }));

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertEquals($expected, $generator->generateUrl($admin, $name, $parameters));
    }

    public function getGenerateUrlTests()
    {
        return array(
            array('/foo?abc=a123&efg=e456&default_param=default_val', 'foo', array('default_param'=>'default_val')),
            array('/foo/bar?abc=a123&efg=e456&default_param=default_val', 'base.Code.Bar.bar', array('default_param'=>'default_val')),
        );
    }

    public function testGenerateUrlWithException()
    {
        $this->setExpectedException('RuntimeException', 'unable to find the route `base.Code.Route.foo`');

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->any())->method('isChild')->will($this->returnValue(false));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Route'));
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(array()));
        $admin->expects($this->exactly(2))->method('getRoutes')->will($this->returnValue(new RouteCollection('base.Code.Route', 'baseRouteName', 'baseRoutePattern', 'BundleName:ControllerName')));
        $admin->expects($this->any())->method('getExtensions')->will($this->returnValue(array()));

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);
        $generator->generateUrl($admin, 'foo', array());
    }

    /**
     * @dataProvider getGenerateUrlChildTests
     */
    public function testGenerateUrlChild($type, $expected, $name, array $parameters)
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->any())->method('isChild')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Child'));
        $admin->expects($this->any())->method('getBaseCodeRoute')->will($this->returnValue('base.Code.Parent|base.Code.Child'));
        $admin->expects($this->any())->method('getIdParameter')->will($this->returnValue('id'));
        $admin->expects($this->any())->method('hasParentFieldDescription')->will($this->returnValue(false));
        $admin->expects($this->any())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('foo_code'));
        $admin->expects($this->any())->method('getPersistentParameters')->will($this->returnValue(array('abc'=>'a123', 'efg'=>'e456')));
        $admin->expects($this->any())->method('getRoutes')->will($this->returnValue($childCollection));
        $admin->expects($this->any())->method('getExtensions')->will($this->returnValue(array()));

        $parentAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $parentAdmin->expects($this->any())->method('getIdParameter')->will($this->returnValue('childId'));
        $parentAdmin->expects($this->any())->method('getRoutes')->will($this->returnValue($collection));
        $parentAdmin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Parent'));
        $parentAdmin->expects($this->any())->method('getExtensions')->will($this->returnValue(array()));

        // no request attached in this test, so this will not be used
        $parentAdmin->expects($this->never())->method('getPersistentParameters')->will($this->returnValue(array('from'=>'parent')));

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->attributes = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
        $request->attributes->expects($this->any())->method('has')->will($this->returnValue(true));
        $request->attributes->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($key) {
                if ($key == 'childId') {
                    return '987654';
                }

                return null;
            }));

        $admin->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $admin->expects($this->any())->method('getParent')->will($this->returnValue($parentAdmin));

        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function($name, array $parameters = array()) {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return '/foo'.$params;
                    case 'admin_acme_child_bar':
                        return '/foo/bar'.$params;
                }

                return null;
            }));

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertEquals($expected, $generator->generateUrl($type == 'child' ? $admin : $parentAdmin, $name, $parameters));
    }

    public function getGenerateUrlChildTests()
    {
        return array(
            array('parent', '/foo?id=123&default_param=default_val', 'foo', array('id'=>123, 'default_param'=>'default_val')),
            array('parent', '/foo/bar?id=123&default_param=default_val', 'base.Code.Child.bar', array('id'=>123, 'default_param'=>'default_val')),
            array('child', '/foo/bar?abc=a123&efg=e456&default_param=default_val&childId=987654', 'bar', array('id'=>123, 'default_param'=>'default_val')),
        );
    }

    /**
     * @dataProvider getGenerateUrlParentFieldDescriptionTests
     */
    public function testGenerateUrlParentFieldDescription($expected, $name, array $parameters)
    {
        $childCollection = new RouteCollection('base.Code.Parent|base.Code.Child', 'admin_acme_child', '/foo/', 'BundleName:ControllerName');
        $childCollection->add('bar');

        $collection = new RouteCollection('base.Code.Parent', 'admin_acme', '/', 'BundleName:ControllerName');
        $collection->add('foo');
        $collection->addCollection($childCollection);

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->any())->method('isChild')->will($this->returnValue(false));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('base.Code.Parent'));
        // embeded admin (not nested ...)
        $admin->expects($this->once())->method('hasParentFieldDescription')->will($this->returnValue(true));
        $admin->expects($this->once())->method('hasRequest')->will($this->returnValue(true));
        $admin->expects($this->any())->method('getUniqid')->will($this->returnValue('foo_uniqueid'));
        $admin->expects($this->any())->method('getCode')->will($this->returnValue('foo_code'));
        $admin->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(array('abc'=>'a123', 'efg'=>'e456')));
        $admin->expects($this->any())->method('getExtensions')->will($this->returnValue(array()));
        $admin->expects($this->any())->method('getRoutes')->will($this->returnValue($collection));

        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function($name, array $parameters = array()) {
                $params = '';
                if (!empty($parameters)) {
                    $params .= '?'.http_build_query($parameters);
                }

                switch ($name) {
                    case 'admin_acme_foo':
                        return '/foo'.$params;
                    case 'admin_acme_child_bar':
                        return '/foo/bar'.$params;
                }

                return null;
            }));

        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getOption')->will($this->returnValue(array()));

        $parentAdmin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $parentAdmin->expects($this->any())->method('getUniqid')->will($this->returnValue('parent_foo_uniqueid'));
        $parentAdmin->expects($this->any())->method('getCode')->will($this->returnValue('parent_foo_code'));
        $parentAdmin->expects($this->any())->method('getExtensions')->will($this->returnValue(array()));

        $fieldDescription->expects($this->any())->method('getAdmin')->will($this->returnValue($parentAdmin));
        $admin->expects($this->any())->method('getParentFieldDescription')->will($this->returnValue($fieldDescription));

        $cache = new RoutesCache($this->cacheTempFolder, true);

        $generator = new DefaultRouteGenerator($router, $cache);

        $this->assertEquals($expected, $generator->generateUrl($admin, $name, $parameters));
    }

    public function getGenerateUrlParentFieldDescriptionTests()
    {
        return array(
            array('/foo?abc=a123&efg=e456&default_param=default_val&uniqid=foo_uniqueid&code=base.Code.Parent&pcode=parent_foo_code&puniqid=parent_foo_uniqueid', 'foo', array('default_param'=>'default_val')),
            // this second test does not make sense as we cannot have embeded admin with nested admin....
            array('/foo/bar?abc=a123&efg=e456&default_param=default_val&uniqid=foo_uniqueid&code=base.Code.Parent&pcode=parent_foo_code&puniqid=parent_foo_uniqueid', 'base.Code.Child.bar', array('default_param'=>'default_val')),
        );
    }
}
