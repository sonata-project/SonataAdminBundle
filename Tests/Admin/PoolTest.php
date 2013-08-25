<?php

/*
 * This file is part of the Sonata package.
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
     * @var Sonata\AdminBundle\Admin\Pool
     */
    private $pool = null;

    public function setUp()
    {
        $this->pool = new Pool($this->getContainer(), 'Sonata Admin', '/path/to/pic.png', array('foo'=>'bar'));
    }

    public function testGetGroups()
    {
        $this->pool->setAdminGroups(array(
            'adminGroup1' => array('sonata.user.admin.group1' => array())
        ));

        $expectedOutput = array(
            'adminGroup1' => array(
                'sonata.user.admin.group1' => 'adminUserClass'
            )
        );

        $this->assertEquals($expectedOutput, $this->pool->getGroups());
    }

    public function testHasGroup()
    {
        $this->pool->setAdminGroups(array(
                'adminGroup1' => array()
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

        $pool->setAdminGroups(array(
            'adminGroup1' => array(
                'items' => array('itemKey' => 'sonata.user.admin.group1')
            ),
            'adminGroup2' => array(
                'items' => array('itemKey' => 'sonata.user.admin.group2')
            ),
            'adminGroup3' => array(
                'items' => array('itemKey' => 'sonata.user.admin.group3')
            ),
        ));

        $groups = $pool->getDashboardGroups();

        $this->assertCount(1, $groups);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetAdminsByGroupWhenGroupNotSet()
    {
        $this->pool->setAdminGroups(array(
                'adminGroup1' => array()
            ));

        $this->pool->getAdminsByGroup('adminGroup2');
    }

    public function testGetAdminsByGroupWhenGroupIsEmpty()
    {
        $this->pool->setAdminGroups(array(
                'adminGroup1' => array()
            ));

        $this->assertEquals(array(), $this->pool->getAdminsByGroup('adminGroup1'));
    }

    public function testGetAdminsByGroup()
    {
        $this->pool->setAdminGroups(array(
            'adminGroup1' => array('items' => array('sonata.admin1', 'sonata.admin2')),
            'adminGroup2' => array('items' => array('sonata.admin3'))
        ));

        $this->assertCount(2, $this->pool->getAdminsByGroup('adminGroup1'));
        $this->assertCount(1, $this->pool->getAdminsByGroup('adminGroup2'));
    }

    public function testGetAdminForClassWhenAdminClassIsNotSet()
    {
        $this->pool->setAdminClasses(array('someclass' => 'sonata.user.admin.group1'));
        $this->assertFalse($this->pool->hasAdminByClass('notexists'));
        $this->assertNull($this->pool->getAdminByClass('notexists'));
    }

    public function testGetAdminForClassWhenAdminClassIsSet()
    {
        $this->pool->setAdminClasses(array('someclass' => 'sonata.user.admin.group1'));
        $this->assertTrue($this->pool->hasAdminByClass('someclass'));
        $this->assertEquals('adminUserClass', $this->pool->getAdminByClass('someclass'));
    }

    public function testGetAdminByAdminCode()
    {
        $this->assertEquals('adminUserClass', $this->pool->getAdminByAdminCode('sonata.news.admin.post'));
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

        $this->assertEquals('commentAdminClass', $this->pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.comment'));
    }

    public function testGetAdminClasses()
    {
        $this->pool->setAdminClasses(array('someclass' => 'sonata.user.admin.group1'));
        $this->assertEquals(array('someclass' => 'sonata.user.admin.group1'), $this->pool->getAdminClasses());
    }

    public function testGetAdminGroups()
    {
        $this->pool->setAdminGroups(array('adminGroup1' => 'sonata.user.admin.group1'));
        $this->assertEquals(array('adminGroup1' => 'sonata.user.admin.group1'), $this->pool->getAdminGroups());
    }

    public function testGetAdminServiceIds()
    {
        $this->pool->setAdminServiceIds(array('sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'));
        $this->assertEquals(array('sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'), $this->pool->getAdminServiceIds());
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
        $this->assertEquals('Foo.html.twig', $this->pool->getTemplate('ajax'));
    }

    public function testGetTitleLogo()
    {
        $this->assertEquals('/path/to/pic.png', $this->pool->getTitleLogo());
    }

    public function testGetTitle()
    {
        $this->assertEquals('Sonata Admin', $this->pool->getTitle());
    }

    public function testGetOption()
    {
        $this->assertEquals('bar', $this->pool->getOption('foo'));

        $this->assertEquals(null, $this->pool->getOption('non_existent_option'));
    }

    /**
     * @return Symfony\Component\DependencyInjection\ContainerInterface - the mock of container interface
     */
    private function getContainer()
    {
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $containerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue('adminUserClass'));

        return $containerMock;
    }
}
