<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Menu\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Menu\Provider\GroupMenuProvider;
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class GroupMenuProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MockObject|Pool
     */
    private $pool;
    /**
     * @var MockObject|MenuProviderInterface
     */
    private $provider;
    /**
     * @var MockObject|FactoryInterface
     */
    private $factory;

    /**
     * @var MockObject
     */
    private $checker;

    protected function setUp()
    {
        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        $this->checker = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')
            ->setMethods(array('isGranted'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new MenuFactory();

        $this->provider = new GroupMenuProvider($this->factory, $this->pool, $this->checker);
    }

    public function testGroupMenuProviderName()
    {
        $this->assertTrue($this->provider->has('sonata_group_menu'));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @param array $adminGroups
     *
     * @group legacy
     * @dataProvider getAdminGroups
     */
    public function testGroupMenuProviderWithoutChecker(array $adminGroups)
    {
        $provider = new GroupMenuProvider($this->factory, $this->pool);

        $this->pool->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->will($this->returnValue($this->getAdminMock()));

        $menu = $provider->get(
            'providerFoo',
            array(
                'name' => 'foo',
                'group' => $adminGroups,
            )
        );

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertSame('foo', $menu->getName());

        $children = $menu->getChildren();

        $this->assertCount(2, $children);
        $this->assertArrayHasKey('foo_admin_label', $children);
        $this->assertArrayHasKey('route_label', $children);
        $this->assertInstanceOf('Knp\Menu\MenuItem', $menu['foo_admin_label']);
        $this->assertSame('foo_admin_label', $menu['foo_admin_label']->getLabel());
    }

    /**
     * NEXT_MAJOR: Remove this test when bumping requirements to >=Symfony 2.6.
     *
     * @group legacy
     */
    public function testGroupMenuProviderThrowsExceptionWithInvalidArgument()
    {
        $this->expectException('InvalidArgumentException');
        new GroupMenuProvider($this->factory, $this->pool, 'foo');
    }

    /**
     * @param array $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderWithCheckerGrantedGroupRoles(array $adminGroups)
    {
        $this->pool->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->will($this->returnValue($this->getAdminMock()));

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(false);

        $menu = $this->provider->get(
            'providerFoo',
            array(
                'name' => 'foo',
                'group' => $adminGroups,
            )
        );

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertSame('foo', $menu->getName());

        $children = $menu->getChildren();

        $this->assertCount(1, $children);
        $this->assertArrayHasKey('foo_admin_label', $children);
        $this->assertArrayNotHasKey('route_label', $children);
        $this->assertInstanceOf('Knp\Menu\MenuItem', $menu['foo_admin_label']);
        $this->assertSame('foo_admin_label', $menu['foo_admin_label']->getLabel());
    }

    /**
     * @param array $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderWithAdmin(array $adminGroups)
    {
        $this->pool->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->will($this->returnValue($this->getAdminMock()));

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $menu = $this->provider->get(
            'providerFoo',
            array(
                'name' => 'foo',
                'group' => $adminGroups,
            )
        );

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertSame('foo', $menu->getName());

        $children = $menu->getChildren();

        $this->assertCount(2, $children);
        $this->assertArrayHasKey('foo_admin_label', $children);
        $this->assertArrayHasKey('route_label', $children);
        $this->assertInstanceOf('Knp\Menu\MenuItem', $menu['foo_admin_label']);
        $this->assertSame('foo_admin_label', $menu['foo_admin_label']->getLabel());
    }

    /**
     * @param array $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetKnpMenuWithListRoute(array $adminGroups)
    {
        $this->pool->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->will($this->returnValue($this->getAdminMock(false)));

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $menu = $this->provider->get(
            'providerFoo',
            array(
                'name' => 'foo',
                'group' => $adminGroups,
            )
        );

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertArrayNotHasKey('foo_admin_label', $menu->getChildren());
        $this->assertArrayHasKey('route_label', $menu->getChildren());
        $this->assertCount(1, $menu->getChildren());
    }

    /**
     * @param array $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetKnpMenuWithGrantedList(array $adminGroups)
    {
        $this->pool->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->will($this->returnValue($this->getAdminMock(true, false)));

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $menu = $this->provider->get(
            'providerFoo',
            array(
                'name' => 'foo',
                'group' => $adminGroups,
            )
        );

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertArrayNotHasKey('foo_admin_label', $menu->getChildren());
        $this->assertArrayHasKey('route_label', $menu->getChildren());
        $this->assertCount(1, $menu->getChildren());
    }

    /**
     * @param array $adminGroupsOnTopOption
     *
     * @dataProvider getAdminGroupsWithOnTopOption
     */
    public function testGetMenuProviderOnTopOptions(array $adminGroupsOnTopOption)
    {
        $this->pool->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->will($this->returnValue($this->getAdminMock(true, false)));

        $menu = $this->provider->get(
            'providerFoo',
            array(
                'name' => 'foo',
                'group' => $adminGroupsOnTopOption,
            )
        );

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertCount(0, $menu->getChildren());
    }

    /**
     * @param array $adminGroups
     *
     * @dataProvider getAdminGroups
     */
    public function testGetMenuProviderKeepOpenOption(array $adminGroups)
    {
        $this->pool->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo('sonata_admin_foo_service'))
            ->will($this->returnValue($this->getAdminMock()));

        $this->checker->expects($this->any())
            ->method('isGranted')
            ->willReturn(true);

        $adminGroups['keep_open'] = true;

        $menu = $this->provider->get(
            'providerFoo',
            array(
                'name' => 'foo',
                'group' => $adminGroups,
            )
        );

        $this->assertInstanceOf('Knp\Menu\ItemInterface', $menu);
        $this->assertSame('keep-open', $menu->getAttribute('class'));
        $this->assertTrue($menu->getExtra('keep_open'));
    }

    /**
     * @return array
     */
    public function getAdminGroups()
    {
        return array(
            array(
                'bar' => array(
                    'label' => 'foo',
                    'icon' => '<i class="fa fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'items' => array(
                        array(
                            'admin' => 'sonata_admin_foo_service',
                            'label' => 'fooLabel',
                            'route_absolute' => true,
                        ),
                        array(
                            'admin' => '',
                            'label' => 'route_label',
                            'route' => 'FooRoute',
                            'route_params' => array('foo' => 'bar'),
                            'route_absolute' => true,
                            'roles' => array(),
                        ),
                    ),
                    'item_adds' => array(),
                    'roles' => array('foo'),
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getAdminGroupsWithOnTopOption()
    {
        return array(
            array(
                'foo' => array(
                    'label' => 'foo_on_top',
                    'icon' => '<i class="fa fa-edit"></i>',
                    'label_catalogue' => 'SonataAdminBundle',
                    'on_top' => true,
                    'items' => array(
                        array(
                            'admin' => 'sonata_admin_foo_service',
                            'label' => 'fooLabel',
                            'route_absolute' => true,
                            'route_params' => array(),
                        ),
                    ),
                    'item_adds' => array(),
                    'roles' => array(),
                ),
            ),
        );
    }

    /**
     * @param bool $hasRoute
     * @param bool $isGranted
     *
     * @return MockObject|AdminInterface
     */
    private function getAdminMock($hasRoute = true, $isGranted = true)
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AbstractAdmin');
        $admin->expects($this->once())
            ->method('hasRoute')
            ->with($this->equalTo('list'))
            ->will($this->returnValue($hasRoute));

        $admin->expects($this->any())
            ->method('hasAccess')
            ->with($this->equalTo('list'))
            ->will($this->returnValue($isGranted));

        $admin->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('foo_admin_label'));

        $admin->expects($this->any())
            ->method('generateMenuUrl')
            ->will($this->returnValue(array()));

        return $admin;
    }
}
