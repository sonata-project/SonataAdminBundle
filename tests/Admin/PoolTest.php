<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PoolTest extends TestCase
{
    /**
     * @var Pool
     */
    private $pool = null;

    public function setUp()
    {
        $this->pool = new Pool($this->getContainer(), 'Sonata Admin', '/path/to/pic.png', ['foo' => 'bar']);
    }

    public function testGetGroups()
    {
        $this->pool->setAdminServiceIds(['sonata.user.admin.group1']);

        $this->pool->setAdminGroups([
            'adminGroup1' => ['sonata.user.admin.group1' => []],
        ]);

        $expectedOutput = [
            'adminGroup1' => [
                'sonata.user.admin.group1' => 'sonata_user_admin_group1_AdminClass',
            ],
        ];

        $this->assertSame($expectedOutput, $this->pool->getGroups());
    }

    public function testHasGroup()
    {
        $this->pool->setAdminGroups([
                'adminGroup1' => [],
            ]);

        $this->assertTrue($this->pool->hasGroup('adminGroup1'));
        $this->assertFalse($this->pool->hasGroup('adminGroup2'));
    }

    public function testGetDashboardGroups()
    {
        $admin_group1 = $this->createMock(AdminInterface::class);
        $admin_group1->expects($this->once())->method('showIn')->will($this->returnValue(true));

        $admin_group2 = $this->createMock(AdminInterface::class);
        $admin_group2->expects($this->once())->method('showIn')->will($this->returnValue(false));

        $admin_group3 = $this->createMock(AdminInterface::class);
        $admin_group3->expects($this->once())->method('showIn')->will($this->returnValue(false));

        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->any())->method('get')->will($this->onConsecutiveCalls(
            $admin_group1, $admin_group2, $admin_group3
        ));

        $pool = new Pool($container, 'Sonata Admin', '/path/to/pic.png');
        $pool->setAdminServiceIds(['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3']);

        $pool->setAdminGroups([
            'adminGroup1' => [
                'items' => ['itemKey' => $this->getItemArray('sonata.user.admin.group1')],
            ],
            'adminGroup2' => [
                'items' => ['itemKey' => $this->getItemArray('sonata.user.admin.group2')],
            ],
            'adminGroup3' => [
                'items' => ['itemKey' => $this->getItemArray('sonata.user.admin.group3')],
            ],
        ]);

        $groups = $pool->getDashboardGroups();

        $this->assertCount(1, $groups);
        $this->assertSame($admin_group1, $groups['adminGroup1']['items']['itemKey']);
    }

    public function testGetAdminsByGroupWhenGroupNotSet()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->pool->setAdminGroups([
                'adminGroup1' => [],
            ]);

        $this->pool->getAdminsByGroup('adminGroup2');
    }

    public function testGetAdminsByGroupWhenGroupIsEmpty()
    {
        $this->pool->setAdminGroups([
                'adminGroup1' => [],
            ]);

        $this->assertSame([], $this->pool->getAdminsByGroup('adminGroup1'));
    }

    public function testGetAdminsByGroup()
    {
        $this->pool->setAdminServiceIds(['sonata.admin1', 'sonata.admin2', 'sonata.admin3']);
        $this->pool->setAdminGroups([
            'adminGroup1' => [
                'items' => [
                    $this->getItemArray('sonata.admin1'),
                    $this->getItemArray('sonata.admin2'),
                ],
            ],
            'adminGroup2' => [
                'items' => [$this->getItemArray('sonata.admin3')],
            ],
        ]);

        $this->assertEquals([
            'sonata_admin1_AdminClass',
            'sonata_admin2_AdminClass',
        ], $this->pool->getAdminsByGroup('adminGroup1'));
        $this->assertEquals(['sonata_admin3_AdminClass'], $this->pool->getAdminsByGroup('adminGroup2'));
    }

    public function testGetAdminForClassWhenAdminClassIsNotSet()
    {
        $this->pool->setAdminClasses(['someclass' => 'sonata.user.admin.group1']);
        $this->assertFalse($this->pool->hasAdminByClass('notexists'));
        $this->assertNull($this->pool->getAdminByClass('notexists'));
    }

    public function testGetAdminForClassWithInvalidFormat()
    {
        $this->expectException(\RuntimeException::class);

        $this->pool->setAdminClasses(['someclass' => 'sonata.user.admin.group1']);
        $this->assertTrue($this->pool->hasAdminByClass('someclass'));

        $this->pool->getAdminByClass('someclass');
    }

    public function testGetAdminForClassWithTooManyRegisteredAdmin()
    {
        $this->expectException(\RuntimeException::class);

        $this->pool->setAdminClasses([
            'someclass' => ['sonata.user.admin.group1', 'sonata.user.admin.group2'],
        ]);

        $this->assertTrue($this->pool->hasAdminByClass('someclass'));
        $this->pool->getAdminByClass('someclass');
    }

    public function testGetAdminForClassWhenAdminClassIsSet()
    {
        $this->pool->setAdminServiceIds(['sonata.user.admin.group1']);
        $this->pool->setAdminClasses([
            'someclass' => ['sonata.user.admin.group1'],
        ]);

        $this->assertTrue($this->pool->hasAdminByClass('someclass'));
        $this->assertSame('sonata_user_admin_group1_AdminClass', $this->pool->getAdminByClass('someclass'));
    }

    public function testGetInstanceWithUndefinedServiceId()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Admin service "sonata.news.admin.post" not found in admin pool.');

        $this->pool->getInstance('sonata.news.admin.post');
    }

    public function testGetAdminByAdminCode()
    {
        $this->pool->setAdminServiceIds(['sonata.news.admin.post']);

        $this->assertSame('sonata_news_admin_post_AdminClass', $this->pool->getAdminByAdminCode('sonata.news.admin.post'));
    }

    public function testGetAdminByAdminCodeForChildClass()
    {
        $adminMock = $this->getMockBuilder(AdminInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminMock->expects($this->any())
            ->method('hasChild')
            ->will($this->returnValue(true));
        $adminMock->expects($this->once())
            ->method('getChild')
            ->with($this->equalTo('sonata.news.admin.comment'))
            ->will($this->returnValue('commentAdminClass'));

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($adminMock));

        $this->pool = new Pool($containerMock, 'Sonata', '/path/to/logo.png');
        $this->pool->setAdminServiceIds(['sonata.news.admin.post']);

        $this->assertSame('commentAdminClass', $this->pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.comment'));
    }

    public function testGetAdminByAdminCodeForChildInvalidClass()
    {
        $adminMock = $this->getMockBuilder(AdminInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminMock->expects($this->any())
            ->method('hasChild')
            ->will($this->returnValue(false));

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($adminMock));

        $this->pool = new Pool($containerMock, 'Sonata', '/path/to/logo.png');
        $this->pool->setAdminServiceIds(['sonata.news.admin.post']);

        $this->assertFalse($this->pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.invalid'));
    }

    public function testGetAdminClasses()
    {
        $this->pool->setAdminClasses(['someclass' => 'sonata.user.admin.group1']);
        $this->assertSame(['someclass' => 'sonata.user.admin.group1'], $this->pool->getAdminClasses());
    }

    public function testGetAdminGroups()
    {
        $this->pool->setAdminGroups(['adminGroup1' => 'sonata.user.admin.group1']);
        $this->assertSame(['adminGroup1' => 'sonata.user.admin.group1'], $this->pool->getAdminGroups());
    }

    public function testGetAdminServiceIds()
    {
        $this->pool->setAdminServiceIds(['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3']);
        $this->assertSame(['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'], $this->pool->getAdminServiceIds());
    }

    public function testGetContainer()
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->pool->getContainer());
    }

    public function testTemplates()
    {
        $this->assertInternalType('array', $this->pool->getTemplates());

        $this->pool->setTemplates(['ajax' => 'Foo.html.twig']);

        $this->assertNull($this->pool->getTemplate('bar'));
        $this->assertSame('Foo.html.twig', $this->pool->getTemplate('ajax'));
    }

    public function testGetTitleLogo()
    {
        $this->assertSame('/path/to/pic.png', $this->pool->getTitleLogo());
    }

    public function testGetTitle()
    {
        $this->assertSame('Sonata Admin', $this->pool->getTitle());
    }

    public function testGetOption()
    {
        $this->assertSame('bar', $this->pool->getOption('foo'));

        $this->assertNull($this->pool->getOption('non_existent_option'));
    }

    public function testOptionDefault()
    {
        $this->assertSame([], $this->pool->getOption('nonexistantarray', []));
    }

    /**
     * @return Symfony\Component\DependencyInjection\ContainerInterface - the mock of container interface
     */
    private function getContainer()
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($serviceId) {
                return str_replace('.', '_', $serviceId).'_AdminClass';
            }));

        return $containerMock;
    }

    private function getItemArray($serviceId)
    {
        return [
            'admin' => $serviceId,
            'label' => '',
            'route' => '',
            'route_params' => [],
        ];
    }
}
