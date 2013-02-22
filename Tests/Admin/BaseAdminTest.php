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

    public function testGetBaseRoutePattern()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $this->assertEquals('/sonata/news/post', $admin->getBaseRoutePattern());
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

    public function testGetBaseRouteName()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $this->assertEquals('admin_sonata_news_post', $admin->getBaseRouteName());
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
    }
}
