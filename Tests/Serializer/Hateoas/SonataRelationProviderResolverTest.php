<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Serializer\Hateoas;

use Sonata\AdminBundle\Serializer\Hateoas\SonataRelationProviderResolver;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;


/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class SonataRelationProviderResolverTest extends \PHPUnit_Framework_TestCase
{
    private $relationProviderResolver;
    private $pool;
    private $generator;
    private $object;
    private $admin;

    public function setUp()
    {
        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
            ->disableOriginalConstructor()
            ->getMock();
        $this->generator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->object = new \stdClass;
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $this->relationProvider = $this->getMockBuilder('Hateoas\Configuration\RelationProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->relationProviderResolver = new SonataRelationProviderResolver($this->pool, $this->generator);
    }

    public function provideGetRelationProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider provideGetRelationProvider
     */
    public function testGetRelationProvider($hasAdmin)
    {
        $expectation = $this->pool->expects($this->once())
            ->method('hasAdminByClass')
            ->with('stdClass');
        $expectation->will($this->returnValue($hasAdmin));

        $res = $this->relationProviderResolver->getRelationProvider($this->relationProvider, $this->object);

        if (false === $hasAdmin) {
            $this->assertNull($res);
            return;
        }

        $this->assertTrue(is_callable($res));
    }

    public function provideGetRelations()
    {
        return array(
            array(
                array(
                    'view_object' => array(
                        'path' => '/path/to/view',
                    ),
                    'edit_object' => array(
                        'path' => '/path/to/edit',
                    ),
                )
            )
        );
    }

    /**
     * @dataProvider provideGetRelations
     */
    public function testGetRelations($routes)
    {
        $routeCollection = new RouteCollection();
        foreach ($routes as $routeName => $routeConfig) {
            $routeCollection->add($routeName, new Route($routeConfig['path']));
        }

        $this->pool->expects($this->once())
            ->method('getAdminByClass')
            ->with('stdClass')
            ->will($this->returnValue($this->admin));

        $this->admin->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue($routeCollection));

        $relations = $this->relationProviderResolver->getRelations($this->object);

        $this->assertCount(count($routes), $relations);
    }
}
