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

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

class FooTest_Admin
{
    public function __toString()
    {
        return 'salut';
    }
}

class FooTestNullToString_Admin
{
    // In case __toString returns an attribute not yet set
    public function __toString()
    {
        return null;
    }
}

class PostAdmin extends Admin
{
    protected $metadataClass = null;

    public function setParentAssociationMapping($associationMapping)
    {
        $this->parentAssociationMapping = $associationMapping;
    }

    public function setClassMetaData($classMetaData)
    {
        $this->classMetaData = $classMetaData;
    }

    public function getClassMetaData()
    {
        if ($this->classMetaData) {
            return $this->classMetaData;
        }

        return parent::getClassMetaData();
    }
}

class CommentAdmin extends Admin
{
    public function setClassnameLabel($label)
    {
        $this->classnameLabel = $label;
    }

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
    }
}

class BaseAdminTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Sonata\AdminBundle\Admin\Admin::__construct
     */
    public function testConstructor()
    {
        $class = 'Application\Sonata\NewsBundle\Entity\Post';
        $baseControllerName = 'SonataNewsBundle:PostAdmin';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $this->assertInstanceOf('Sonata\AdminBundle\Admin\Admin', $admin);
        $this->assertEquals($class, $admin->getClass());
        $this->assertEquals($baseControllerName, $admin->getBaseControllerName());
    }

    /**
     * @covers Sonata\AdminBundle\Admin\Admin::hasChild
     * @covers Sonata\AdminBundle\Admin\Admin::addChild
     * @covers Sonata\AdminBundle\Admin\Admin::getChild
     * @covers Sonata\AdminBundle\Admin\Admin::isChild
     * @covers Sonata\AdminBundle\Admin\Admin::hasChildren
     * @covers Sonata\AdminBundle\Admin\Admin::getChildren
     */
    public function testChildren()
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $this->assertFalse($postAdmin->hasChildren());
        $this->assertFalse($postAdmin->hasChild('comment'));

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentAdmin');
        $postAdmin->addChild($commentAdmin);
        $this->assertTrue($postAdmin->hasChildren());
        $this->assertTrue($postAdmin->hasChild('sonata.post.admin.comment'));

        $this->assertEquals('sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getCode());
        $this->assertEquals('sonata.post.admin.post|sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getBaseCodeRoute());
        $this->assertEquals($postAdmin, $postAdmin->getChild('sonata.post.admin.comment')->getParent());

        $this->assertFalse($postAdmin->isChild());
        $this->assertTrue($commentAdmin->isChild());

        $this->assertEquals(array('sonata.post.admin.comment' => $commentAdmin), $postAdmin->getChildren());
    }

    /**
     * @covers Sonata\AdminBundle\Admin\Admin::configure
     */
    public function testConfigure()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $this->assertTrue($admin->getUniqid() == "");

        $admin->initialize();
        $this->assertFalse($admin->getUniqid() == "");
        $this->assertEquals('Post', $admin->getClassnameLabel());

        $admin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentAdmin');
        $admin->setClassnameLabel('postcomment');

        $admin->initialize();
        $this->assertEquals('postcomment', $admin->getClassnameLabel());
    }

    public function testConfigureWithValidParentAssociationMapping()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setParentAssociationMapping('Category');

        $admin->initialize();
        $this->assertEquals('Category', $admin->getParentAssociationMapping());
    }

    public function provideGetBaseRoutePattern()
    {
        return array(
            array(
                'Application\Sonata\NewsBundle\Entity\Post', 
                '/sonata/news/post'
            ),
            array(
                'Application\Sonata\NewsBundle\Document\Post', 
                '/sonata/news/post'
            ),
            array(
                'MyApplication\MyBundle\Entity\Post', 
                '/myapplication/my/post'
            ),
            array(
                'MyApplication\MyBundle\Entity\Post\Category',
                '/myapplication/my/post-category'
            ),
            array(
                'MyApplication\MyBundle\Entity\Product\Category',
                '/myapplication/my/product-category'
            ),
            array(
                'MyApplication\MyBundle\Entity\Other\Product\Category',
                '/myapplication/my/other-product-category'
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Document\Menu', 
                '/cmf/foo/menu'
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Phpcr\Menu', 
                '/cmf/foo/menu'
            ),
            array(
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu', 
                '/symfony/barbar/menu'
            ),
            array(
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu\Item',
                '/symfony/barbar/menu-item'
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Orm\Menu', 
                '/cmf/foo/menu'
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\MongoDB\Menu', 
                '/cmf/foo/menu'
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\CouchDB\Menu', 
                '/cmf/foo/menu'
            ),
        );
    }

    /**
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePattern($objFqn, $expected)
    {
        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'SonataNewsBundle:PostAdmin');
        $this->assertEquals($expected, $admin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithChildAdmin()
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentAdmin');
        $commentAdmin->setParent($postAdmin);

        $this->assertEquals('/sonata/news/post/{id}/comment', $commentAdmin->getBaseRoutePattern());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetBaseRoutePatternWithUnreconizedClassname()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->getBaseRoutePattern();
    }

    public function provideGetBaseRouteName()
    {
        return array(
            array(
                'Application\Sonata\NewsBundle\Entity\Post', 
                'admin_sonata_news_post'
            ),
            array(
                'Application\Sonata\NewsBundle\Document\Post', 
                'admin_sonata_news_post'
            ),
            array(
                'MyApplication\MyBundle\Entity\Post', 
                'admin_myapplication_my_post'
            ),
            array(
                'MyApplication\MyBundle\Entity\Post\Category',
                'admin_myapplication_my_post_category'
            ),
            array(
                'MyApplication\MyBundle\Entity\Product\Category',
                'admin_myapplication_my_product_category'
            ),
            array(
                'MyApplication\MyBundle\Entity\Other\Product\Category',
                'admin_myapplication_my_other_product_category'
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Document\Menu', 
                'admin_cmf_foo_menu'
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Phpcr\Menu', 
                'admin_cmf_foo_menu'
            ),
            array(
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu', 
                'admin_symfony_barbar_menu'
            ),
            array(
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu\Item',
                'admin_symfony_barbar_menu_item'
            ),
	    array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Orm\Menu', 
                'admin_cmf_foo_menu'
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\MongoDB\Menu', 
                'admin_cmf_foo_menu'
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\CouchDB\Menu', 
                'admin_cmf_foo_menu'
            ),
        );
    }

    /**
     * @dataProvider provideGetBaseRouteName
     */
    public function testGetBaseRouteName($objFqn, $expected)
    {
        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'SonataNewsBundle:PostAdmin');

        $this->assertEquals($expected, $admin->getBaseRouteName());
    }

    public function testGetBaseRouteNameWithChildAdmin()
    {
        $pathInfo = new \Sonata\AdminBundle\Route\PathInfoBuilder($this->getMock('Sonata\AdminBundle\Model\AuditManagerInterface'));
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $postAdmin->setRouteBuilder($pathInfo);
        $postAdmin->initialize();
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentAdmin');
        $commentAdmin->setRouteBuilder($pathInfo);
        $commentAdmin->initialize();

        $postAdmin->addChild($commentAdmin);

        $this->assertEquals('admin_sonata_news_post_comment', $commentAdmin->getBaseRouteName());

        $this->assertTrue($postAdmin->hasRoute('show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.post.show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment.show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.comment.list'));

        $this->assertFalse($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment.edit'));
        $this->assertFalse($commentAdmin->hasRoute('edit'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetBaseRouteNameWithUnreconizedClassname()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->getBaseRouteName();
    }

    /**
     * @covers Sonata\AdminBundle\Admin\Admin::setUniqid
     * @covers Sonata\AdminBundle\Admin\Admin::getUniqid
     */
    public function testUniqid()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $uniqid = uniqid();
        $admin->setUniqid($uniqid);

        $this->assertEquals($uniqid, $admin->getUniqid());
    }

    public function testToString()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $s = new \stdClass();

        $this->assertNotEmpty($admin->toString($s));

        $s = new FooTest_Admin;
        $this->assertEquals('salut', $admin->toString($s));
        
        // To string method is implemented, but returns null
        $s = new FooTestNullToString_Admin;
        $this->assertNotEmpty($admin->toString($s));

        $this->assertEquals("", $admin->toString(false));
    }
    
    public function testIsAclEnabled()
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertFalse($postAdmin->isAclEnabled());

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentAdmin');
        $commentAdmin->setSecurityHandler($this->getMock('Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface'));
        $this->assertTrue($commentAdmin->isAclEnabled());
    }
    
    /**
     * @covers Sonata\AdminBundle\Admin\Admin::getSubClasses
     * @covers Sonata\AdminBundle\Admin\Admin::getSubClass
     * @covers Sonata\AdminBundle\Admin\Admin::setSubClasses
     * @covers Sonata\AdminBundle\Admin\Admin::hasSubClass
     * @covers Sonata\AdminBundle\Admin\Admin::hasActiveSubClass
     * @covers Sonata\AdminBundle\Admin\Admin::getActiveSubClass
     * @covers Sonata\AdminBundle\Admin\Admin::getActiveSubclassCode
     * @covers Sonata\AdminBundle\Admin\Admin::getClass
     */
    public function testSubClass()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $this->assertFalse($admin->hasSubClass('test'));
        $this->assertFalse($admin->hasActiveSubClass());
        $this->assertCount(0, $admin->getSubClasses());
        $this->assertNull($admin->getActiveSubClass());
        $this->assertNull($admin->getActiveSubclassCode());
        $this->assertEquals('NewsBundle\Entity\Post', $admin->getClass());

        // Just for the record, if there is no inheritance set, the getSubject is not used
        // the getSubject can also lead to some issue
         $admin->setSubject(new \stdClass());
         $this->assertEquals('stdClass', $admin->getClass());

        $admin->setSubClasses(array('extended1' => 'NewsBundle\Entity\PostExtended1', 'extended2' => 'NewsBundle\Entity\PostExtended2'));
        $this->assertFalse($admin->hasSubClass('test'));
        $this->assertTrue($admin->hasSubClass('extended1'));
        $this->assertFalse($admin->hasActiveSubClass());
        $this->assertCount(2, $admin->getSubClasses());
        $this->assertNull($admin->getActiveSubClass());
        $this->assertNull($admin->getActiveSubclassCode());
        $this->assertEquals('stdClass', $admin->getClass());

        $request = new \Symfony\Component\HttpFoundation\Request(array('subclass' => 'extended1'));
        $admin->setRequest($request);
        $this->assertFalse($admin->hasSubClass('test'));
        $this->assertTrue($admin->hasSubClass('extended1'));
        $this->assertTrue($admin->hasActiveSubClass());
        $this->assertCount(2, $admin->getSubClasses());
        $this->assertEquals('stdClass', $admin->getActiveSubClass());
        $this->assertEquals('extended1', $admin->getActiveSubclassCode());
        $this->assertEquals('stdClass', $admin->getClass());

        $request->query->set('subclass', 'inject');
        $this->assertNull($admin->getActiveSubclassCode());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testNonExistantSubclass()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setRequest(new \Symfony\Component\HttpFoundation\Request(array('subclass' => 'inject')));

        $admin->setSubClasses(array('extended1' => 'NewsBundle\Entity\PostExtended1', 'extended2' => 'NewsBundle\Entity\PostExtended2'));

        $this->assertTrue($admin->hasActiveSubClass());

        $admin->getActiveSubClass();
    }
}
