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
}

class AdminTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Sonata\AdminBundle\Admin\Admin::__construct
     */
    public function testConstructor()
    {
        $class = 'Application\Sonata\NewsBundle\Entity\Post';
        $baseControllerName = 'SonataNews:PostAdmin';

        $admin = new PostAdmin($class, $baseControllerName);
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
        $postAdmin = new PostAdmin('Application\Sonata\NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $postAdmin->setCode('post');
        $this->assertFalse($postAdmin->hasChildren());
        $this->assertFalse($postAdmin->hasChild('comment'));

        $commentAdmin = new CommentAdmin('Application\Sonata\NewsBundle\Entity\Comment', 'SonataNews:CommentAdmin');
        $postAdmin->addChild('comment', $commentAdmin);
        $this->assertTrue($postAdmin->hasChildren());
        $this->assertTrue($postAdmin->hasChild('comment'));

        $this->assertEquals('comment', $postAdmin->getChild('comment')->getCode());
        $this->assertEquals('post|comment', $postAdmin->getChild('comment')->getBaseCodeRoute());
        $this->assertEquals($postAdmin, $postAdmin->getChild('comment')->getParent());

        $this->assertFalse($postAdmin->isChild());
        $this->assertTrue($commentAdmin->isChild());

        $this->assertEquals(array('comment' => $commentAdmin), $postAdmin->getChildren());
    }

    /**
     * @covers Sonata\AdminBundle\Admin\Admin::configure
     */
    public function testConfigure()
    {
        $admin = new PostAdmin('Application\Sonata\NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $this->assertTrue($admin->getUniqid() == "");

        $admin->configure();
        $this->assertFalse($admin->getUniqid() == "");
        $this->assertEquals('post', $admin->getClassnameLabel());


        $admin = new CommentAdmin('Application\Sonata\NewsBundle\Entity\Comment', 'SonataNews:CommentAdmin');
        $admin->setClassnameLabel('postcomment');

        $admin->configure();
        $this->assertEquals('postcomment', $admin->getClassnameLabel());
    }

    public function testConfigureWithValidParentAssociationMapping()
    {
        $admin = new PostAdmin('Application\Sonata\NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $admin->setParentAssociationMapping('Category');

        $parentAssociationMapping = 'Application\Sonata\NewsBundle\Entity\Category';
        $metadataClass = new \stdClass();
        $metadataClass->associationMappings = array('Category' => $parentAssociationMapping);
        $admin->setClassMetaData($metadataClass);

        $admin->configure();
        $this->assertEquals($parentAssociationMapping, $admin->getParentAssociationMapping());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConfigureWithInvalidParentAssociationMapping()
    {
        $admin = new PostAdmin('Application\Sonata\NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $admin->setParentAssociationMapping('Category');
        $admin->setClassMetaData(new \stdClass());

        $admin->configure();
    }


    public function testGetBaseRoutePattern()
    {
        $admin = new PostAdmin('Application\Sonata\NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $this->assertEquals('/sonata/news/post', $admin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithChildAdmin()
    {
        $postAdmin = new PostAdmin('Application\Sonata\NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $commentAdmin = new CommentAdmin('Application\Sonata\NewsBundle\Entity\Comment', 'SonataNews:CommentAdmin');
        $commentAdmin->setParent($postAdmin);

        $this->assertEquals('/sonata/news/post/{id}/comment', $commentAdmin->getBaseRoutePattern());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetBaseRoutePatternWithUnreconizedClassname()
    {
        $admin = new PostAdmin('NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $admin->getBaseRoutePattern();
    }


    public function testGetBaseRouteName()
    {
        $admin = new PostAdmin('Application\Sonata\NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $this->assertEquals('admin_sonata_news_post', $admin->getBaseRouteName());
    }

    public function testGetBaseRouteNameWithChildAdmin()
    {
        $postAdmin = new PostAdmin('Application\Sonata\NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $commentAdmin = new CommentAdmin('Application\Sonata\NewsBundle\Entity\Comment', 'SonataNews:CommentAdmin');
        $commentAdmin->setParent($postAdmin);

        $this->assertEquals('admin_sonata_news_post_comment', $commentAdmin->getBaseRouteName());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetBaseRouteNameWithUnreconizedClassname()
    {
        $admin = new PostAdmin('NewsBundle\Entity\Post', 'SonataNews:PostAdmin');
        $admin->getBaseRouteName();
    }

    /**
     * @covers Sonata\AdminBundle\Admin\Admin::setUniqid
     * @covers Sonata\AdminBundle\Admin\Admin::getUniqid
     */
    public function testUniqid()
    {
        $admin = new PostAdmin('NewsBundle\Entity\Post', 'SonataNews:PostAdmin');

        $uniqid = uniqid();
        $admin->setUniqid($uniqid);

        $this->assertEquals($uniqid, $admin->getUniqid());
    }
}
