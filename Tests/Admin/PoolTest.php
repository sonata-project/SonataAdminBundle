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

use Sonata\AdminBundle\Admin\Pool;

class PoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pool
     */
    private $pool = null;

    public function setUp()
    {
        $this->pool = new Pool($this->getContainer(), 'Sonata Admin', '/path/to/pic.png', array('foo' => 'bar'));
    }

    public function testGetGroups()
    {
        $this->pool->setAdminServiceIds(array('sonata.user.admin.group1'));

        $this->pool->setAdminGroups(array(
            'adminGroup1' => array('sonata.user.admin.group1' => array()),
        ));

        $expectedOutput = array(
            'adminGroup1' => array(
                'sonata.user.admin.group1' => 'sonata_user_admin_group1_AdminClass',
            ),
        );

        $this->assertSame($expectedOutput, $this->pool->getGroups());
    }

    public function testHasGroup()
    {
        $this->pool->setAdminGroups(array(
                'adminGroup1' => array(),
            ));

        $this->assertTrue($this->pool->hasGroup('adminGroup1'));
        $this->assertFalse($this->pool->hasGroup('adminGroup2'));
    }

    public function testGetDashboardGroups()
    {
        $admin_group1 = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin_group1->expects($this->once())->method('showIn')->will($this->returnValue(true));

        $admin_group2 = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin_group2->expects($this->once())->method('showIn')->will($this->returnValue(false));

        $admin_group3 = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin_group3->expects($this->once())->method('showIn')->will($this->returnValue(false));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container->expects($this->any())->method('get')->will($this->onConsecutiveCalls(
            $admin_group1, $admin_group2, $admin_group3
        ));

        $pool = new Pool($container, 'Sonata Admin', '/path/to/pic.png');
        $pool->setAdminServiceIds(array('sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'));

        $pool->setAdminGroups(array(
            'adminGroup1' => array(
                'items' => array('itemKey' => $this->getItemArray('sonata.user.admin.group1')),
            ),
            'adminGroup2' => array(
                'items' => array('itemKey' => $this->getItemArray('sonata.user.admin.group2')),
            ),
            'adminGroup3' => array(
                'items' => array('itemKey' => $this->getItemArray('sonata.user.admin.group3')),
            ),
        ));

        $groups = $pool->getDashboardGroups();

        $this->assertCount(1, $groups);
        $this->assertSame($admin_group1, $groups['adminGroup1']['items']['itemKey']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetAdminsByGroupWhenGroupNotSet()
    {
        $this->pool->setAdminGroups(array(
                'adminGroup1' => array(),
            ));

        $this->pool->getAdminsByGroup('adminGroup2');
    }

    public function testGetAdminsByGroupWhenGroupIsEmpty()
    {
        $this->pool->setAdminGroups(array(
                'adminGroup1' => array(),
            ));

        $this->assertSame(array(), $this->pool->getAdminsByGroup('adminGroup1'));
    }

    public function testGetAdminsByGroup()
    {
        $this->pool->setAdminServiceIds(array('sonata.admin1', 'sonata.admin2', 'sonata.admin3'));
        $this->pool->setAdminGroups(array(
            'adminGroup1' => array(
                'items' => array(
                    $this->getItemArray('sonata.admin1'),
                    $this->getItemArray('sonata.admin2'),
                ),
            ),
            'adminGroup2' => array(
                'items' => array($this->getItemArray('sonata.admin3')),
            ),
        ));

        $this->assertEquals(array(
            'sonata_admin1_AdminClass',
            'sonata_admin2_AdminClass',
        ), $this->pool->getAdminsByGroup('adminGroup1'));
        $this->assertEquals(array('sonata_admin3_AdminClass'), $this->pool->getAdminsByGroup('adminGroup2'));
    }

    public function testGetAdminForClassWhenAdminClassIsNotSet()
    {
        $this->pool->setAdminClasses(array('someclass' => 'sonata.user.admin.group1'));
        $this->assertFalse($this->pool->hasAdminByClass('notexists'));
        $this->assertNull($this->pool->getAdminByClass('notexists'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetAdminForClassWithInvalidFormat()
    {
        $this->pool->setAdminClasses(array('someclass' => 'sonata.user.admin.group1'));
        $this->assertTrue($this->pool->hasAdminByClass('someclass'));

        $this->pool->getAdminByClass('someclass');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetAdminForClassWithTooManyRegisteredAdmin()
    {
        $this->pool->setAdminClasses(array(
            'someclass' => array('sonata.user.admin.group1', 'sonata.user.admin.group2'),
        ));

        $this->assertTrue($this->pool->hasAdminByClass('someclass'));
        $this->pool->getAdminByClass('someclass');
    }

    public function testGetAdminForClassWhenAdminClassIsSet()
    {
        $this->pool->setAdminServiceIds(array('sonata.user.admin.group1'));
        $this->pool->setAdminClasses(array(
            'someclass' => array('sonata.user.admin.group1'),
        ));

        $this->assertTrue($this->pool->hasAdminByClass('someclass'));
        $this->assertSame('sonata_user_admin_group1_AdminClass', $this->pool->getAdminByClass('someclass'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Admin service "sonata.news.admin.post" not found in admin pool.
     */
    public function testGetInstanceWithUndefinedServiceId()
    {
        $this->pool->getInstance('sonata.news.admin.post');
    }

    public function testGetAdminByAdminCode()
    {
        $this->pool->setAdminServiceIds(array('sonata.news.admin.post'));

        $this->assertSame('sonata_news_admin_post_AdminClass', $this->pool->getAdminByAdminCode('sonata.news.admin.post'));
    }

    public function testGetAdminByAdminCodeForChildClass()
    {
        $adminMock = $this->getMockBuilder('Sonata\AdminBundle\Admin\AdminInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $adminMock->expects($this->any())
            ->method('hasChild')
            ->will($this->returnValue(true));
        $adminMock->expects($this->once())
            ->method('getChild')
            ->with($this->equalTo('sonata.news.admin.comment'))
            ->will($this->returnValue('commentAdminClass'));

        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $containerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($adminMock));

        $this->pool = new Pool($containerMock, 'Sonata', '/path/to/logo.png');
        $this->pool->setAdminServiceIds(array('sonata.news.admin.post'));

        $this->assertSame('commentAdminClass', $this->pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.comment'));
    }

    public function testGetAdminClasses()
    {
        $this->pool->setAdminClasses(array('someclass' => 'sonata.user.admin.group1'));
        $this->assertSame(array('someclass' => 'sonata.user.admin.group1'), $this->pool->getAdminClasses());
    }

    public function testGetAdminGroups()
    {
        $this->pool->setAdminGroups(array('adminGroup1' => 'sonata.user.admin.group1'));
        $this->assertSame(array('adminGroup1' => 'sonata.user.admin.group1'), $this->pool->getAdminGroups());
    }

    public function testGetAdminServiceIds()
    {
        $this->pool->setAdminServiceIds(array('sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'));
        $this->assertSame(array('sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'), $this->pool->getAdminServiceIds());
    }

    public function testGetContainer()
    {
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $this->pool->getContainer());
    }

    public function testTemplates()
    {
        $this->assertInternalType('array', $this->pool->getTemplates());

        $this->pool->setTemplates(array('ajax' => 'Foo.html.twig'));

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

        $this->assertSame(null, $this->pool->getOption('non_existent_option'));
    }

    public function testOptionDefault()
    {
        $this->assertSame(array(), $this->pool->getOption('nonexistantarray', array()));
    }

    /**
     * @return Symfony\Component\DependencyInjection\ContainerInterface - the mock of container interface
     */
    private function getContainer()
    {
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $containerMock->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($serviceId) {
                return str_replace('.', '_', $serviceId).'_AdminClass';
            }));

        return $containerMock;
    }

    private function getItemArray($serviceId)
    {
        return array(
            'admin' => $serviceId,
            'label' => '',
            'route' => '',
            'route_params' => array(),
        );
    }
}
