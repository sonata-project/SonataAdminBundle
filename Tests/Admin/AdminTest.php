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

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Sonata\AdminBundle\Route\RoutesCache;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentWithCustomRouteAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\FieldDescription;
use Sonata\AdminBundle\Tests\Fixtures\Admin\FilteredAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\ModelAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostWithCustomRouteAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Post;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Tag;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooToString;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooToStringNull;
use Sonata\CoreBundle\Model\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AdminTest extends \PHPUnit_Framework_TestCase
{
    protected $cacheTempFolder;

    public function setUp()
    {
        $this->cacheTempFolder = sys_get_temp_dir().'/sonata_test_route';

        exec('rm -rf '.$this->cacheTempFolder);
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::__construct
     */
    public function testConstructor()
    {
        $class = 'Application\Sonata\NewsBundle\Entity\Post';
        $baseControllerName = 'SonataNewsBundle:PostAdmin';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $this->assertInstanceOf('Sonata\AdminBundle\Admin\AbstractAdmin', $admin);
        $this->assertSame($class, $admin->getClass());
        $this->assertSame($baseControllerName, $admin->getBaseControllerName());
    }

    public function testGetClass()
    {
        $class = 'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Post';
        $baseControllerName = 'SonataNewsBundle:PostAdmin';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);

        $admin->setSubject(new \Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost());
        $this->assertSame(
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost',
            $admin->getClass()
        );

        $admin->setSubClasses(array('foo'));
        $this->assertSame(
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost',
            $admin->getClass()
        );

        $admin->setSubject(null);
        $admin->setSubClasses(array());
        $this->assertSame($class, $admin->getClass());

        $admin->setSubClasses(array('foo' => 'bar'));
        $admin->setRequest(new Request(array('subclass' => 'foo')));
        $this->assertSame('bar', $admin->getClass());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Feature not implemented: an embedded admin cannot have subclass
     */
    public function testGetClassException()
    {
        $class = 'Application\Sonata\NewsBundle\Entity\Post';
        $baseControllerName = 'SonataNewsBundle:PostAdmin';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $admin->setParentFieldDescription(new FieldDescription());
        $admin->setSubClasses(array('foo' => 'bar'));
        $admin->setRequest(new Request(array('subclass' => 'foo')));
        $admin->getClass();
    }

    public function testCheckAccessThrowsExceptionOnMadeUpAction()
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'SonataNewsBundle:PostAdmin'
        );
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Action "made-up" could not be found'
        );
        $admin->checkAccess('made-up');
    }

    public function testCheckAccessThrowsAccessDeniedException()
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'SonataNewsBundle:PostAdmin'
        );
        $securityHandler = $this->prophesize(
            'Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface'
        );
        $securityHandler->isGranted($admin, 'CUSTOM_ROLE', $admin)->willReturn(true);
        $securityHandler->isGranted($admin, 'EXTRA_CUSTOM_ROLE', $admin)->willReturn(false);
        $customExtension = $this->prophesize(
            'Sonata\AdminBundle\Admin\AbstractAdminExtension'
        );
        $customExtension->getAccessMapping($admin)->willReturn(
            array('custom_action' => array('CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE'))
        );
        $admin->addExtension($customExtension->reveal());
        $admin->setSecurityHandler($securityHandler->reveal());
        $this->setExpectedException(
            'Symfony\Component\Security\Core\Exception\AccessDeniedException',
            'Access Denied to the action custom_action and role EXTRA_CUSTOM_ROLE'
        );
        $admin->checkAccess('custom_action');
    }

    public function testHasAccessOnMadeUpAction()
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'SonataNewsBundle:PostAdmin'
        );

        $this->assertFalse($admin->hasAccess('made-up'));
    }

    public function testHasAccess()
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'SonataNewsBundle:PostAdmin'
        );
        $securityHandler = $this->prophesize(
            'Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface'
        );
        $securityHandler->isGranted($admin, 'CUSTOM_ROLE', $admin)->willReturn(true);
        $securityHandler->isGranted($admin, 'EXTRA_CUSTOM_ROLE', $admin)->willReturn(false);
        $customExtension = $this->prophesize(
            'Sonata\AdminBundle\Admin\AbstractAdminExtension'
        );
        $customExtension->getAccessMapping($admin)->willReturn(
            array('custom_action' => array('CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE'))
        );
        $admin->addExtension($customExtension->reveal());
        $admin->setSecurityHandler($securityHandler->reveal());

        $this->assertFalse($admin->hasAccess('custom_action'));
    }

    public function testHasAccessAllowsAccess()
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'SonataNewsBundle:PostAdmin'
        );
        $securityHandler = $this->prophesize(
            'Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface'
        );
        $securityHandler->isGranted($admin, 'CUSTOM_ROLE', $admin)->willReturn(true);
        $securityHandler->isGranted($admin, 'EXTRA_CUSTOM_ROLE', $admin)->willReturn(true);
        $customExtension = $this->prophesize(
            'Sonata\AdminBundle\Admin\AbstractAdminExtension'
        );
        $customExtension->getAccessMapping($admin)->willReturn(
            array('custom_action' => array('CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE'))
        );
        $admin->addExtension($customExtension->reveal());
        $admin->setSecurityHandler($securityHandler->reveal());

        $this->assertTrue($admin->hasAccess('custom_action'));
    }

    public function testHasAccessAllowsAccessEditAction()
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'SonataNewsBundle:PostAdmin'
        );
        $securityHandler = $this->prophesize(
            'Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface'
        );
        $securityHandler->isGranted($admin, 'EDIT_ROLE', $admin)->willReturn(true);
        $customExtension = $this->prophesize(
            'Sonata\AdminBundle\Admin\AbstractAdminExtension'
        );
        $customExtension->getAccessMapping($admin)->willReturn(
            array('edit_action' => array('EDIT_ROLE'))
        );
        $admin->addExtension($customExtension->reveal());
        $admin->setSecurityHandler($securityHandler->reveal());

        $this->assertTrue($admin->hasAccess('edit_action'));
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::hasChild
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::addChild
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::getChild
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::isChild
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::hasChildren
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::getChildren
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

        $this->assertSame('sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getCode());
        $this->assertSame('sonata.post.admin.post|sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getBaseCodeRoute());
        $this->assertSame($postAdmin, $postAdmin->getChild('sonata.post.admin.comment')->getParent());

        $this->assertFalse($postAdmin->isChild());
        $this->assertTrue($commentAdmin->isChild());

        $this->assertSame(array('sonata.post.admin.comment' => $commentAdmin), $postAdmin->getChildren());
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::configure
     */
    public function testConfigure()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $this->assertNotNull($admin->getUniqid());

        $admin->initialize();
        $this->assertNotNull($admin->getUniqid());
        $this->assertSame('Post', $admin->getClassnameLabel());

        $admin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentAdmin');
        $admin->setClassnameLabel('postcomment');

        $admin->initialize();
        $this->assertSame('postcomment', $admin->getClassnameLabel());
    }

    public function testConfigureWithValidParentAssociationMapping()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setParentAssociationMapping('Category');

        $admin->initialize();
        $this->assertSame('Category', $admin->getParentAssociationMapping());
    }

    public function provideGetBaseRoutePattern()
    {
        return array(
            array(
                'Application\Sonata\NewsBundle\Entity\Post',
                '/sonata/news/post',
            ),
            array(
                'Application\Sonata\NewsBundle\Document\Post',
                '/sonata/news/post',
            ),
            array(
                'MyApplication\MyBundle\Entity\Post',
                '/myapplication/my/post',
            ),
            array(
                'MyApplication\MyBundle\Entity\Post\Category',
                '/myapplication/my/post-category',
            ),
            array(
                'MyApplication\MyBundle\Entity\Product\Category',
                '/myapplication/my/product-category',
            ),
            array(
                'MyApplication\MyBundle\Entity\Other\Product\Category',
                '/myapplication/my/other-product-category',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Document\Menu',
                '/cmf/foo/menu',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Phpcr\Menu',
                '/cmf/foo/menu',
            ),
            array(
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu',
                '/symfony/barbar/menu',
            ),
            array(
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu\Item',
                '/symfony/barbar/menu-item',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Orm\Menu',
                '/cmf/foo/menu',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\MongoDB\Menu',
                '/cmf/foo/menu',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\CouchDB\Menu',
                '/cmf/foo/menu',
            ),
            array(
                'AppBundle\Entity\User',
                '/app/user',
            ),
            array(
                'App\Entity\User',
                '/app/user',
            ),
        );
    }

    /**
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePattern($objFqn, $expected)
    {
        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'SonataNewsBundle:PostAdmin');
        $this->assertSame($expected, $admin->getBaseRoutePattern());
    }

    /**
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePatternWithChildAdmin($objFqn, $expected)
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', $objFqn, 'SonataNewsBundle:PostAdmin');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentAdmin');
        $commentAdmin->setParent($postAdmin);

        $this->assertSame($expected.'/{id}/comment', $commentAdmin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithSpecifedPattern()
    {
        $postAdmin = new PostWithCustomRouteAdmin('sonata.post.admin.post_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostWithCustomRouteAdmin');

        $this->assertSame('/post-custom', $postAdmin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithChildAdminAndWithSpecifedPattern()
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $commentAdmin = new CommentWithCustomRouteAdmin('sonata.post.admin.comment_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentWithCustomRouteAdmin');
        $commentAdmin->setParent($postAdmin);

        $this->assertSame('/sonata/news/post/{id}/comment-custom', $commentAdmin->getBaseRoutePattern());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetBaseRoutePatternWithUnreconizedClassname()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'News\Thing\Post', 'SonataNewsBundle:PostAdmin');
        $admin->getBaseRoutePattern();
    }

    public function provideGetBaseRouteName()
    {
        return array(
            array(
                'Application\Sonata\NewsBundle\Entity\Post',
                'admin_sonata_news_post',
            ),
            array(
                'Application\Sonata\NewsBundle\Document\Post',
                'admin_sonata_news_post',
            ),
            array(
                'MyApplication\MyBundle\Entity\Post',
                'admin_myapplication_my_post',
            ),
            array(
                'MyApplication\MyBundle\Entity\Post\Category',
                'admin_myapplication_my_post_category',
            ),
            array(
                'MyApplication\MyBundle\Entity\Product\Category',
                'admin_myapplication_my_product_category',
            ),
            array(
                'MyApplication\MyBundle\Entity\Other\Product\Category',
                'admin_myapplication_my_other_product_category',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Document\Menu',
                'admin_cmf_foo_menu',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Phpcr\Menu',
                'admin_cmf_foo_menu',
            ),
            array(
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu',
                'admin_symfony_barbar_menu',
            ),
            array(
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu\Item',
                'admin_symfony_barbar_menu_item',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Orm\Menu',
                'admin_cmf_foo_menu',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\MongoDB\Menu',
                'admin_cmf_foo_menu',
            ),
            array(
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\CouchDB\Menu',
                'admin_cmf_foo_menu',
            ),
            array(
                'AppBundle\Entity\User',
                'admin_app_user',
            ),
            array(
                'App\Entity\User',
                'admin_app_user',
            ),
        );
    }

    /**
     * @dataProvider provideGetBaseRouteName
     */
    public function testGetBaseRouteName($objFqn, $expected)
    {
        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'SonataNewsBundle:PostAdmin');

        $this->assertSame($expected, $admin->getBaseRouteName());
    }

    /**
     * @dataProvider provideGetBaseRouteName
     */
    public function testGetBaseRouteNameWithChildAdmin($objFqn, $expected)
    {
        $routeGenerator = new DefaultRouteGenerator(
            $this->getMock('Symfony\Component\Routing\RouterInterface'),
            new RoutesCache($this->cacheTempFolder, true)
        );

        $container = new Container();
        $pool = new Pool($container, 'Sonata Admin', '/path/to/pic.png');

        $pathInfo = new \Sonata\AdminBundle\Route\PathInfoBuilder($this->getMock('Sonata\AdminBundle\Model\AuditManagerInterface'));
        $postAdmin = new PostAdmin('sonata.post.admin.post', $objFqn, 'SonataNewsBundle:PostAdmin');
        $container->set('sonata.post.admin.post', $postAdmin);
        $postAdmin->setConfigurationPool($pool);
        $postAdmin->setRouteBuilder($pathInfo);
        $postAdmin->setRouteGenerator($routeGenerator);
        $postAdmin->initialize();

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentAdmin');
        $container->set('sonata.post.admin.comment', $commentAdmin);
        $commentAdmin->setConfigurationPool($pool);
        $commentAdmin->setRouteBuilder($pathInfo);
        $commentAdmin->setRouteGenerator($routeGenerator);
        $commentAdmin->initialize();

        $postAdmin->addChild($commentAdmin);
        $pool->setAdminServiceIds(array('sonata.post.admin.post', 'sonata.post.admin.comment'));

        $this->assertSame($expected.'_comment', $commentAdmin->getBaseRouteName());

        $this->assertTrue($postAdmin->hasRoute('show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.post.show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment.show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.comment.list'));
        $this->assertFalse($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment.edit'));
        $this->assertFalse($commentAdmin->hasRoute('edit'));

        /*
         * Test the route name from request
         */
        $postListRequest = new Request(
            array(),
            array(),
            array(
                '_route' => $postAdmin->getBaseRouteName().'_list',
            )
        );

        $postAdmin->setRequest($postListRequest);
        $commentAdmin->setRequest($postListRequest);

        $this->assertTrue($postAdmin->isCurrentRoute('list'));
        $this->assertFalse($postAdmin->isCurrentRoute('create'));
        $this->assertFalse($commentAdmin->isCurrentRoute('list'));
        $this->assertTrue($commentAdmin->isCurrentRoute('list', 'sonata.post.admin.post'));
        $this->assertFalse($commentAdmin->isCurrentRoute('edit', 'sonata.post.admin.post'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetBaseRouteNameWithUnreconizedClassname()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'News\Thing\Post', 'SonataNewsBundle:PostAdmin');
        $admin->getBaseRouteName();
    }

    public function testGetBaseRouteNameWithSpecifiedName()
    {
        $postAdmin = new PostWithCustomRouteAdmin('sonata.post.admin.post_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame('post_custom', $postAdmin->getBaseRouteName());
    }

    public function testGetBaseRouteNameWithChildAdminAndWithSpecifiedName()
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $commentAdmin = new CommentWithCustomRouteAdmin('sonata.post.admin.comment_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentWithCustomRouteAdmin');
        $commentAdmin->setParent($postAdmin);

        $this->assertSame('admin_sonata_news_post_comment_custom', $commentAdmin->getBaseRouteName());
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::setUniqid
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::getUniqid
     */
    public function testUniqid()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $uniqid = uniqid();
        $admin->setUniqid($uniqid);

        $this->assertSame($uniqid, $admin->getUniqid());
    }

    public function testToString()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $s = new \stdClass();

        $this->assertNotEmpty($admin->toString($s));

        $s = new FooToString();
        $this->assertSame('salut', $admin->toString($s));

        // To string method is implemented, but returns null
        $s = new FooToStringNull();
        $this->assertNotEmpty($admin->toString($s));

        $this->assertSame('', $admin->toString(false));
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
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::getSubClasses
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::getSubClass
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::setSubClasses
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::hasSubClass
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::hasActiveSubClass
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::getActiveSubClass
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::getActiveSubclassCode
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::getClass
     */
    public function testSubClass()
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Post',
            'SonataNewsBundle:PostAdmin'
        );
        $this->assertFalse($admin->hasSubClass('test'));
        $this->assertFalse($admin->hasActiveSubClass());
        $this->assertCount(0, $admin->getSubClasses());
        $this->assertNull($admin->getActiveSubClass());
        $this->assertNull($admin->getActiveSubclassCode());
        $this->assertSame(
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Post',
            $admin->getClass()
        );

        // Just for the record, if there is no inheritance set, the getSubject is not used
        // the getSubject can also lead to some issue
        $admin->setSubject(new \Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost());
        $this->assertSame(
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost',
            $admin->getClass()
        );

        $admin->setSubClasses(array('extended1' => 'NewsBundle\Entity\PostExtended1', 'extended2' => 'NewsBundle\Entity\PostExtended2'));
        $this->assertFalse($admin->hasSubClass('test'));
        $this->assertTrue($admin->hasSubClass('extended1'));
        $this->assertFalse($admin->hasActiveSubClass());
        $this->assertCount(2, $admin->getSubClasses());
        $this->assertNull($admin->getActiveSubClass());
        $this->assertNull($admin->getActiveSubclassCode());
        $this->assertSame(
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost',
            $admin->getClass()
        );

        $request = new \Symfony\Component\HttpFoundation\Request(array('subclass' => 'extended1'));
        $admin->setRequest($request);
        $this->assertFalse($admin->hasSubClass('test'));
        $this->assertTrue($admin->hasSubClass('extended1'));
        $this->assertTrue($admin->hasActiveSubClass());
        $this->assertCount(2, $admin->getSubClasses());
        $this->assertSame(
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost',
            $admin->getActiveSubClass()
        );
        $this->assertSame('extended1', $admin->getActiveSubclassCode());
        $this->assertSame(
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost',
            $admin->getClass()
        );

        $request->query->set('subclass', 'inject');
        $this->assertNull($admin->getActiveSubclassCode());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNonExistantSubclass()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setRequest(new \Symfony\Component\HttpFoundation\Request(array('subclass' => 'inject')));

        $admin->setSubClasses(array('extended1' => 'NewsBundle\Entity\PostExtended1', 'extended2' => 'NewsBundle\Entity\PostExtended2'));

        $this->assertTrue($admin->hasActiveSubClass());

        $admin->getActiveSubClass();
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::hasActiveSubClass
     */
    public function testOnlyOneSubclassNeededToBeActive()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setSubClasses(array('extended1' => 'NewsBundle\Entity\PostExtended1'));
        $request = new \Symfony\Component\HttpFoundation\Request(array('subclass' => 'extended1'));
        $admin->setRequest($request);
        $this->assertTrue($admin->hasActiveSubClass());
    }

    public function testGetPerPageOptions()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(array(16, 32, 64, 128, 192), $admin->getPerPageOptions());
        $admin->setPerPageOptions(array(500, 1000));
        $this->assertSame(array(500, 1000), $admin->getPerPageOptions());
    }

    public function testGetLabelTranslatorStrategy()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getLabelTranslatorStrategy());

        $labelTranslatorStrategy = $this->getMock('Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface');
        $admin->setLabelTranslatorStrategy($labelTranslatorStrategy);
        $this->assertSame($labelTranslatorStrategy, $admin->getLabelTranslatorStrategy());
    }

    public function testGetRouteBuilder()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getRouteBuilder());

        $routeBuilder = $this->getMock('Sonata\AdminBundle\Builder\RouteBuilderInterface');
        $admin->setRouteBuilder($routeBuilder);
        $this->assertSame($routeBuilder, $admin->getRouteBuilder());
    }

    public function testGetMenuFactory()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getMenuFactory());

        $menuFactory = $this->getMock('Knp\Menu\FactoryInterface');
        $admin->setMenuFactory($menuFactory);
        $this->assertSame($menuFactory, $admin->getMenuFactory());
    }

    public function testGetExtensions()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(array(), $admin->getExtensions());

        $adminExtension1 = $this->getMock('Sonata\AdminBundle\Admin\AdminExtensionInterface');
        $adminExtension2 = $this->getMock('Sonata\AdminBundle\Admin\AdminExtensionInterface');

        $admin->addExtension($adminExtension1);
        $admin->addExtension($adminExtension2);
        $this->assertSame(array($adminExtension1, $adminExtension2), $admin->getExtensions());
    }

    public function testGetFilterTheme()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(array(), $admin->getFilterTheme());

        $admin->setFilterTheme(array('FooTheme'));
        $this->assertSame(array('FooTheme'), $admin->getFilterTheme());
    }

    public function testGetFormTheme()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(array(), $admin->getFormTheme());

        $admin->setFormTheme(array('FooTheme'));

        $this->assertSame(array('FooTheme'), $admin->getFormTheme());
    }

    public function testGetValidator()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getValidator());

        $validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $admin->setValidator($validator);
        $this->assertSame($validator, $admin->getValidator());
    }

    public function testGetSecurityHandler()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getSecurityHandler());

        $securityHandler = $this->getMock('Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface');
        $admin->setSecurityHandler($securityHandler);
        $this->assertSame($securityHandler, $admin->getSecurityHandler());
    }

    public function testGetSecurityInformation()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(array(), $admin->getSecurityInformation());

        $securityInformation = array('ROLE_FOO', 'ROLE_BAR');

        $admin->setSecurityInformation($securityInformation);
        $this->assertSame($securityInformation, $admin->getSecurityInformation());
    }

    public function testGetManagerType()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getManagerType());

        $admin->setManagerType('foo_orm');
        $this->assertSame('foo_orm', $admin->getManagerType());
    }

    public function testGetModelManager()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getModelManager());

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $admin->setModelManager($modelManager);
        $this->assertSame($modelManager, $admin->getModelManager());
    }

    public function testGetBaseCodeRoute()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame('', $admin->getBaseCodeRoute());

        $admin->setBaseCodeRoute('foo');
        $this->assertSame('foo', $admin->getBaseCodeRoute());
    }

    public function testGetRouteGenerator()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getRouteGenerator());

        $routeGenerator = $this->getMock('Sonata\AdminBundle\Route\RouteGeneratorInterface');

        $admin->setRouteGenerator($routeGenerator);
        $this->assertSame($routeGenerator, $admin->getRouteGenerator());
    }

    public function testGetConfigurationPool()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getConfigurationPool());

        $pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
            ->disableOriginalConstructor()
            ->getMock();

        $admin->setConfigurationPool($pool);
        $this->assertSame($pool, $admin->getConfigurationPool());
    }

    public function testGetShowBuilder()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getShowBuilder());

        $showBuilder = $this->getMock('Sonata\AdminBundle\Builder\ShowBuilderInterface');

        $admin->setShowBuilder($showBuilder);
        $this->assertSame($showBuilder, $admin->getShowBuilder());
    }

    public function testGetListBuilder()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getListBuilder());

        $listBuilder = $this->getMock('Sonata\AdminBundle\Builder\ListBuilderInterface');

        $admin->setListBuilder($listBuilder);
        $this->assertSame($listBuilder, $admin->getListBuilder());
    }

    public function testGetDatagridBuilder()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getDatagridBuilder());

        $datagridBuilder = $this->getMock('Sonata\AdminBundle\Builder\DatagridBuilderInterface');

        $admin->setDatagridBuilder($datagridBuilder);
        $this->assertSame($datagridBuilder, $admin->getDatagridBuilder());
    }

    public function testGetFormContractor()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getFormContractor());

        $formContractor = $this->getMock('Sonata\AdminBundle\Builder\FormContractorInterface');

        $admin->setFormContractor($formContractor);
        $this->assertSame($formContractor, $admin->getFormContractor());
    }

    public function testGetRequest()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertFalse($admin->hasRequest());

        $request = new Request();

        $admin->setRequest($request);
        $this->assertSame($request, $admin->getRequest());
        $this->assertTrue($admin->hasRequest());
    }

    public function testGetRequestWithException()
    {
        $this->setExpectedException('RuntimeException', 'The Request object has not been set');

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->getRequest();
    }

    public function testGetTranslationDomain()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame('messages', $admin->getTranslationDomain());

        $admin->setTranslationDomain('foo');
        $this->assertSame('foo', $admin->getTranslationDomain());
    }

    public function testGetTranslator()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getTranslator());

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $admin->setTranslator($translator);
        $this->assertSame($translator, $admin->getTranslator());
    }

    public function testGetShowGroups()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(false, $admin->getShowGroups());

        $groups = array('foo', 'bar', 'baz');

        $admin->setShowGroups($groups);
        $this->assertSame($groups, $admin->getShowGroups());
    }

    public function testGetFormGroups()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(false, $admin->getFormGroups());

        $groups = array('foo', 'bar', 'baz');

        $admin->setFormGroups($groups);
        $this->assertSame($groups, $admin->getFormGroups());
    }

    public function testGetMaxPageLinks()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(25, $admin->getMaxPageLinks());

        $admin->setMaxPageLinks(14);
        $this->assertSame(14, $admin->getMaxPageLinks());
    }

    public function testGetMaxPerPage()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(32, $admin->getMaxPerPage());

        $admin->setMaxPerPage(94);
        $this->assertSame(94, $admin->getMaxPerPage());
    }

    public function testGetLabel()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getLabel());

        $admin->setLabel('FooLabel');
        $this->assertSame('FooLabel', $admin->getLabel());
    }

    public function testGetBaseController()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame('SonataNewsBundle:PostAdmin', $admin->getBaseControllerName());

        $admin->setBaseControllerName('SonataNewsBundle:FooAdmin');
        $this->assertSame('SonataNewsBundle:FooAdmin', $admin->getBaseControllerName());
    }

    public function testGetTemplates()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(array(), $admin->getTemplates());

        $templates = array(
            'list' => 'FooAdminBundle:CRUD:list.html.twig',
            'show' => 'FooAdminBundle:CRUD:show.html.twig',
            'edit' => 'FooAdminBundle:CRUD:edit.html.twig',
        );

        $admin->setTemplates($templates);
        $this->assertSame($templates, $admin->getTemplates());
    }

    public function testGetTemplate1()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getTemplate('edit'));

        $admin->setTemplate('edit', 'FooAdminBundle:CRUD:edit.html.twig');
        $admin->setTemplate('show', 'FooAdminBundle:CRUD:show.html.twig');

        $this->assertSame('FooAdminBundle:CRUD:edit.html.twig', $admin->getTemplate('edit'));
        $this->assertSame('FooAdminBundle:CRUD:show.html.twig', $admin->getTemplate('show'));
    }

    public function testGetTemplate2()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertNull($admin->getTemplate('edit'));

        $templates = array(
            'list' => 'FooAdminBundle:CRUD:list.html.twig',
            'show' => 'FooAdminBundle:CRUD:show.html.twig',
            'edit' => 'FooAdminBundle:CRUD:edit.html.twig',
        );

        $admin->setTemplates($templates);

        $this->assertSame('FooAdminBundle:CRUD:edit.html.twig', $admin->getTemplate('edit'));
        $this->assertSame('FooAdminBundle:CRUD:show.html.twig', $admin->getTemplate('show'));
    }

    public function testGetIdParameter()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame('id', $admin->getIdParameter());
        $this->assertFalse($admin->isChild());

        $parentAdmin = new PostAdmin('sonata.post.admin.post_parent', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostParentAdmin');
        $admin->setParent($parentAdmin);

        $this->assertTrue($admin->isChild());
        $this->assertSame('childId', $admin->getIdParameter());
    }

    public function testGetExportFormats()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(array('json', 'xml', 'csv', 'xls'), $admin->getExportFormats());
    }

    public function testGetUrlsafeIdentifier()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $entity = new \stdClass();

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())
            ->method('getUrlsafeIdentifier')
            ->with($this->equalTo($entity))
            ->will($this->returnValue('foo'));
        $admin->setModelManager($modelManager);

        $this->assertSame('foo', $admin->getUrlsafeIdentifier($entity));
    }

    public function testDeterminedPerPageValue()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertFalse($admin->determinedPerPageValue('foo'));
        $this->assertFalse($admin->determinedPerPageValue(123));
        $this->assertTrue($admin->determinedPerPageValue(16));
        $this->assertTrue($admin->determinedPerPageValue(32));
        $this->assertTrue($admin->determinedPerPageValue(64));
        $this->assertTrue($admin->determinedPerPageValue(128));
        $this->assertTrue($admin->determinedPerPageValue(192));

        $admin->setPerPageOptions(array(101, 102, 103));
        $this->assertFalse($admin->determinedPerPageValue(15));
        $this->assertFalse($admin->determinedPerPageValue(25));
        $this->assertFalse($admin->determinedPerPageValue(200));
        $this->assertTrue($admin->determinedPerPageValue(101));
        $this->assertTrue($admin->determinedPerPageValue(102));
        $this->assertTrue($admin->determinedPerPageValue(103));
    }

    public function testIsGranted()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $entity = new \stdClass();

        $securityHandler = $this->getMock('Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface');
        $securityHandler->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function (AdminInterface $adminIn, $attributes, $object = null) use ($admin, $entity) {
                if ($admin == $adminIn && $attributes == 'FOO') {
                    if (($object == $admin) || ($object == $entity)) {
                        return true;
                    }
                }

                return false;
            }));

        $admin->setSecurityHandler($securityHandler);

        $this->assertTrue($admin->isGranted('FOO'));
        $this->assertTrue($admin->isGranted('FOO', $entity));
        $this->assertFalse($admin->isGranted('BAR'));
        $this->assertFalse($admin->isGranted('BAR', $entity));
    }

    public function testSupportsPreviewMode()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertFalse($admin->supportsPreviewMode());
    }

    public function testGetPermissionsShow()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame(array('LIST'), $admin->getPermissionsShow(AbstractAdmin::CONTEXT_DASHBOARD));
        $this->assertSame(array('LIST'), $admin->getPermissionsShow(AbstractAdmin::CONTEXT_MENU));
        $this->assertSame(array('LIST'), $admin->getPermissionsShow('foo'));
    }

    public function testShowIn()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $securityHandler = $this->getMock('Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface');
        $securityHandler->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function (AdminInterface $adminIn, $attributes, $object = null) use ($admin) {
                if ($admin == $adminIn && $attributes == array('LIST')) {
                    return true;
                }

                return false;
            }));

        $admin->setSecurityHandler($securityHandler);

        $this->assertTrue($admin->showIn(AbstractAdmin::CONTEXT_DASHBOARD));
        $this->assertTrue($admin->showIn(AbstractAdmin::CONTEXT_MENU));
        $this->assertTrue($admin->showIn('foo'));
    }

    public function testGetObjectIdentifier()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame('sonata.post.admin.post', $admin->getObjectIdentifier());
    }

    public function testTrans()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setTranslationDomain('fooMessageDomain');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $admin->setTranslator($translator);

        $translator->expects($this->once())
            ->method('trans')
            ->with($this->equalTo('foo'), $this->equalTo(array()), $this->equalTo('fooMessageDomain'))
            ->will($this->returnValue('fooTranslated'));

        $this->assertSame('fooTranslated', $admin->trans('foo'));
    }

    /**
     * @group Legacy
     */
    public function testTransWithMessageDomain()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $admin->setTranslator($translator);

        $translator->expects($this->once())
            ->method('trans')
            ->with($this->equalTo('foo'), $this->equalTo(array('name' => 'Andrej')), $this->equalTo('fooMessageDomain'))
            ->will($this->returnValue('fooTranslated'));

        $this->assertSame('fooTranslated', $admin->trans('foo', array('name' => 'Andrej'), 'fooMessageDomain'));
    }

    public function testTransChoice()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setTranslationDomain('fooMessageDomain');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $admin->setTranslator($translator);

        $translator->expects($this->once())
            ->method('transChoice')
            ->with($this->equalTo('foo'), $this->equalTo(2), $this->equalTo(array()), $this->equalTo('fooMessageDomain'))
            ->will($this->returnValue('fooTranslated'));

        $this->assertSame('fooTranslated', $admin->transChoice('foo', 2));
    }

    /**
     * @group Legacy
     */
    public function testTransChoiceWithMessageDomain()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $admin->setTranslator($translator);

        $translator->expects($this->once())
            ->method('transChoice')
            ->with($this->equalTo('foo'), $this->equalTo(2), $this->equalTo(array('name' => 'Andrej')), $this->equalTo('fooMessageDomain'))
            ->will($this->returnValue('fooTranslated'));

        $this->assertSame('fooTranslated', $admin->transChoice('foo', 2, array('name' => 'Andrej'), 'fooMessageDomain'));
    }

    public function testSetPersistFilters()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertAttributeSame(false, 'persistFilters', $admin);
        $admin->setPersistFilters(true);
        $this->assertAttributeSame(true, 'persistFilters', $admin);
    }

    public function testGetRootCode()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame('sonata.post.admin.post', $admin->getRootCode());

        $parentAdmin = new PostAdmin('sonata.post.admin.post.parent', 'NewsBundle\Entity\PostParent', 'SonataNewsBundle:PostParentAdmin');
        $parentFieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $parentFieldDescription->expects($this->once())
            ->method('getAdmin')
            ->will($this->returnValue($parentAdmin));

        $this->assertNull($admin->getParentFieldDescription());
        $admin->setParentFieldDescription($parentFieldDescription);
        $this->assertSame($parentFieldDescription, $admin->getParentFieldDescription());
        $this->assertSame('sonata.post.admin.post.parent', $admin->getRootCode());
    }

    public function testGetRoot()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertSame($admin, $admin->getRoot());

        $parentAdmin = new PostAdmin('sonata.post.admin.post.parent', 'NewsBundle\Entity\PostParent', 'SonataNewsBundle:PostParentAdmin');
        $parentFieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $parentFieldDescription->expects($this->once())
            ->method('getAdmin')
            ->will($this->returnValue($parentAdmin));

        $this->assertNull($admin->getParentFieldDescription());
        $admin->setParentFieldDescription($parentFieldDescription);
        $this->assertSame($parentFieldDescription, $admin->getParentFieldDescription());
        $this->assertSame($parentAdmin, $admin->getRoot());
    }

    public function testGetExportFields()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())
            ->method('getExportFields')
            ->with($this->equalTo('NewsBundle\Entity\Post'))
            ->will($this->returnValue(array('foo', 'bar')));

        $admin->setModelManager($modelManager);
        $this->assertSame(array('foo', 'bar'), $admin->getExportFields());
    }

    public function testGetPersistentParametersWithNoExtension()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $this->assertEmpty($admin->getPersistentParameters());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetPersistentParametersWithInvalidExtension()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $extension = $this->getMock('Sonata\AdminBundle\Admin\AdminExtensionInterface');
        $extension->expects($this->once())->method('getPersistentParameters')->will($this->returnValue(null));

        $admin->addExtension($extension);

        $admin->getPersistentParameters();
    }

    public function testGetPersistentParametersWithValidExtension()
    {
        $expected = array(
            'context' => 'foobar',
        );

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $extension = $this->getMock('Sonata\AdminBundle\Admin\AdminExtensionInterface');
        $extension->expects($this->once())->method('getPersistentParameters')->will($this->returnValue($expected));

        $admin->addExtension($extension);

        $this->assertSame($expected, $admin->getPersistentParameters());
    }

    public function testGetFormWithNonCollectionParentValue()
    {
        $post = new Post();
        $tagAdmin = $this->createTagAdmin($post);
        $tag = $tagAdmin->getSubject();

        $tag->setPosts(null);
        $tagAdmin->getForm();
        $this->assertSame($post, $tag->getPosts());
    }

    public function testGetFormWithCollectionParentValue()
    {
        $post = new Post();
        $tagAdmin = $this->createTagAdmin($post);
        $tag = $tagAdmin->getSubject();

        // Case of a doctrine collection
        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $tag->getPosts());
        $this->assertCount(0, $tag->getPosts());

        $tag->addPost(new Post());

        $this->assertCount(1, $tag->getPosts());

        $tagAdmin->getForm();

        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $tag->getPosts());
        $this->assertCount(2, $tag->getPosts());
        $this->assertContains($post, $tag->getPosts());

        // Case of an array
        $tag->setPosts(array());
        $this->assertCount(0, $tag->getPosts());

        $tag->addPost(new Post());

        $this->assertCount(1, $tag->getPosts());

        $tagAdmin->getForm();

        $this->assertInternalType('array', $tag->getPosts());
        $this->assertCount(2, $tag->getPosts());
        $this->assertContains($post, $tag->getPosts());
    }

    public function testRemoveFieldFromFormGroup()
    {
        $formGroups = array(
            'foobar' => array(
                'fields' => array(
                    'foo' => 'foo',
                    'bar' => 'bar',
                ),
            ),
        );

        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setFormGroups($formGroups);

        $admin->removeFieldFromFormGroup('foo');
        $this->assertSame($admin->getFormGroups(), array(
            'foobar' => array(
                'fields' => array(
                    'bar' => 'bar',
                ),
            ),
        ));

        $admin->removeFieldFromFormGroup('bar');
        $this->assertSame($admin->getFormGroups(), array());
    }

    public function testGetFilterParameters()
    {
        $authorId = uniqid();

        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'SonataNewsBundle:CommentAdmin');
        $commentAdmin->setParentAssociationMapping('post.author');
        $commentAdmin->setParent($postAdmin);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request', array('get'));
        $request->expects($this->any())
            ->method('get')
            ->will($this->returnValue($authorId));

        $commentAdmin->setRequest($request);

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->any())
            ->method('getDefaultSortValues')
            ->will($this->returnValue(array()));

        $commentAdmin->setModelManager($modelManager);

        $parameters = $commentAdmin->getFilterParameters();

        $this->assertTrue(isset($parameters['post__author']));
        $this->assertSame(array('value' => $authorId), $parameters['post__author']);
    }

    public function testGetFilterFieldDescription()
    {
        $modelAdmin = new ModelAdmin('sonata.post.admin.model', 'Application\Sonata\FooBundle\Entity\Model', 'SonataFooBundle:ModelAdmin');

        $fooFieldDescription = new FieldDescription();
        $barFieldDescription = new FieldDescription();
        $bazFieldDescription = new FieldDescription();

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->exactly(3))
            ->method('getNewFieldDescriptionInstance')
            ->will($this->returnCallback(function ($adminClass, $name, $filterOptions) use ($fooFieldDescription, $barFieldDescription, $bazFieldDescription) {
                switch ($name) {
                    case 'foo':
                        $fieldDescription = $fooFieldDescription;
                        break;

                    case 'bar':
                        $fieldDescription = $barFieldDescription;
                        break;

                    case 'baz':
                        $fieldDescription = $bazFieldDescription;
                        break;

                    default:
                        throw new \RuntimeException(sprintf('Unknown filter name "%s"', $name));
                        break;
                }

                $fieldDescription->setName($name);

                return $fieldDescription;
            }));

        $modelAdmin->setModelManager($modelManager);

        $pager = $this->getMock('Sonata\AdminBundle\Datagrid\PagerInterface');

        $datagrid = $this->getMock('Sonata\AdminBundle\Datagrid\DatagridInterface');
        $datagrid->expects($this->once())
            ->method('getPager')
            ->will($this->returnValue($pager));

        $datagridBuilder = $this->getMock('Sonata\AdminBundle\Builder\DatagridBuilderInterface');
        $datagridBuilder->expects($this->once())
            ->method('getBaseDatagrid')
            ->with($this->identicalTo($modelAdmin), array())
            ->will($this->returnValue($datagrid));

        $datagridBuilder->expects($this->exactly(3))
            ->method('addFilter')
            ->will($this->returnCallback(function ($datagrid, $type, $fieldDescription, AdminInterface $admin) {
                $admin->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);
                $fieldDescription->mergeOption('field_options', array('required' => false));
            }));

        $modelAdmin->setDatagridBuilder($datagridBuilder);

        $this->assertSame(array('foo' => $fooFieldDescription, 'bar' => $barFieldDescription, 'baz' => $bazFieldDescription), $modelAdmin->getFilterFieldDescriptions());
        $this->assertFalse($modelAdmin->hasFilterFieldDescription('fooBar'));
        $this->assertTrue($modelAdmin->hasFilterFieldDescription('foo'));
        $this->assertTrue($modelAdmin->hasFilterFieldDescription('bar'));
        $this->assertTrue($modelAdmin->hasFilterFieldDescription('baz'));
        $this->assertSame($fooFieldDescription, $modelAdmin->getFilterFieldDescription('foo'));
        $this->assertSame($barFieldDescription, $modelAdmin->getFilterFieldDescription('bar'));
        $this->assertSame($bazFieldDescription, $modelAdmin->getFilterFieldDescription('baz'));
    }

    public function testGetSubjectNoRequest()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager
            ->expects($this->never())
            ->method('find');

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setModelManager($modelManager);

        $this->assertNull($admin->getSubject());
    }

    public function testGetSideMenu()
    {
        $item = $this->getMock('Knp\Menu\ItemInterface');
        $item
            ->expects($this->once())
            ->method('setChildrenAttribute')
            ->with('class', 'nav navbar-nav');
        $item
            ->expects($this->once())
            ->method('setExtra')
            ->with('translation_domain', 'foo_bar_baz');

        $menuFactory = $this->getMock('Knp\Menu\FactoryInterface');
        $menuFactory
            ->expects($this->once())
            ->method('createItem')
            ->will($this->returnValue($item));

        $modelAdmin = new ModelAdmin('sonata.post.admin.model', 'Application\Sonata\FooBundle\Entity\Model', 'SonataFooBundle:ModelAdmin');
        $modelAdmin->setMenuFactory($menuFactory);
        $modelAdmin->setTranslationDomain('foo_bar_baz');

        $modelAdmin->getSideMenu('foo');
    }

    /**
     * @return array
     */
    public function provideGetSubject()
    {
        return array(
            array(23),
            array('azerty'),
            array('4f69bbb5f14a13347f000092'),
            array('0779ca8d-e2be-11e4-ac58-0242ac11000b'),
            array('123'.AdapterInterface::ID_SEPARATOR.'my_type'), // composite keys are supported
        );
    }

    /**
     * @dataProvider provideGetSubject
     */
    public function testGetSubjectFailed($id)
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager
            ->expects($this->once())
            ->method('find')
            ->with('NewsBundle\Entity\Post', $id)
            ->will($this->returnValue(null)); // entity not found

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setModelManager($modelManager);

        $admin->setRequest(new Request(array('id' => $id)));
        $this->assertNull($admin->getSubject());
    }

    /**
     * @dataProvider provideGetSubject
     */
    public function testGetSubject($id)
    {
        $entity = new Post();

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager
            ->expects($this->once())
            ->method('find')
            ->with('NewsBundle\Entity\Post', $id)
            ->will($this->returnValue($entity));

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $admin->setModelManager($modelManager);

        $admin->setRequest(new Request(array('id' => $id)));
        $this->assertSame($entity, $admin->getSubject());
        $this->assertSame($entity, $admin->getSubject()); // model manager must be used only once
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::configureActionButtons
     */
    public function testGetActionButtonsList()
    {
        $expected = array(
            'create' => array(
                'template' => 'Foo.html.twig',
            ),
        );

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $securityHandler = $this->getMock('Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface');
        $securityHandler
            ->expects($this->once())
            ->method('isGranted')
            ->with($admin, 'CREATE', $admin)
            ->will($this->returnValue(true));
        $admin->setSecurityHandler($securityHandler);

        $routeGenerator = $this->getMock('Sonata\AdminBundle\Route\RouteGeneratorInterface');
        $routeGenerator
            ->expects($this->once())
            ->method('hasAdminRoute')
            ->with($admin, 'create')
            ->will($this->returnValue(true));
        $admin->setRouteGenerator($routeGenerator);

        $admin->setTemplate('button_create', 'Foo.html.twig');

        $this->assertSame($expected, $admin->getActionButtons('list', null));
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::configureActionButtons
     */
    public function testGetActionButtonsListCreateDisabled()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');

        $securityHandler = $this->getMock('Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface');
        $securityHandler
            ->expects($this->once())
            ->method('isGranted')
            ->with($admin, 'CREATE', $admin)
            ->will($this->returnValue(false));
        $admin->setSecurityHandler($securityHandler);

        $this->assertSame(array(), $admin->getActionButtons('list', null));
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::configureBatchActions
     */
    public function getBatchActions()
    {
        $expected = array(
            'action' => array(
                'label' => 'action_delete',
                'translation_domain' => 'SonataAdminBundle',
                'ask_confirmation' => true, // by default always true
            ),
            'foo' => array(
                'label' => 'action_foo',
            ),
        );

        $modelAdmin = new PostAdmin('sonata.post.admin.model', 'Application\Sonata\FooBundle\Entity\Model', 'SonataFooBundle:ModelAdmin');

        $this->assertSame($expected, $modelAdmin->getBatchActions());
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::showMosaicButton
     */
    public function testShowMosaicButton()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $listModes = $admin->getListModes();

        $admin->showMosaicButton(true);

        $this->assertSame($listModes, $admin->getListModes());
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::showMosaicButton
     */
    public function testShowMosaicButtonHideMosaic()
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle:PostAdmin');
        $listModes = $admin->getListModes();
        $expected['list'] = $listModes['list'];

        $admin->showMosaicButton(false);

        $this->assertSame($expected, $admin->getListModes());
    }

    /**
     * @covers Sonata\AdminBundle\Admin\AbstractAdmin::getDashboardActions
     * @dataProvider provideGetBaseRouteName
     */
    public function testDefaultDashboardActionsArePresent($objFqn, $expected)
    {
        $pathInfo = new \Sonata\AdminBundle\Route\PathInfoBuilder($this->getMock('Sonata\AdminBundle\Model\AuditManagerInterface'));

        $routeGenerator = new DefaultRouteGenerator(
            $this->getMock('Symfony\Component\Routing\RouterInterface'),
            new RoutesCache($this->cacheTempFolder, true)
        );

        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'SonataNewsBundle:PostAdmin');
        $admin->setRouteBuilder($pathInfo);
        $admin->setRouteGenerator($routeGenerator);
        $admin->initialize();

        $securityHandler = $this->getMock('Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface');
        $securityHandler->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function (AdminInterface $adminIn, $attributes, $object = null) use ($admin) {
                if ($admin == $adminIn && ($attributes == 'CREATE' || $attributes == 'LIST')) {
                    return true;
                }

                return false;
            }));

        $admin->setSecurityHandler($securityHandler);

        $this->assertArrayHasKey('list', $admin->getDashboardActions());
        $this->assertArrayHasKey('create', $admin->getDashboardActions());
    }

    public function testDefaultFilters()
    {
        $admin = new FilteredAdmin('sonata.post.admin.model', 'Application\Sonata\FooBundle\Entity\Model', 'SonataFooBundle:ModelAdmin');

        $subjectId = uniqid();

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request', array('get'));
        $request->query->set('filter', array(
            'a' => array(
                'value' => 'b',
            ),
            'foo' => array(
                'type' => '1',
                'value' => 'bar',
            ),
            'baz' => array(
                'type' => '5',
                'value' => 'test',
            ),
        ));

        $request->expects($this->any())
            ->method('get')
            ->will($this->returnValue($subjectId));

        $admin->setRequest($request);

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->any())
            ->method('getDefaultSortValues')
            ->will($this->returnValue(array()));

        $admin->setModelManager($modelManager);

        $this->assertEquals(array(
            'foo' => array(
                'type' => '1',
                'value' => 'bar',
            ),
            'baz' => array(
                'type' => '5',
                'value' => 'test',
            ),
            '_page' => 1,
            '_per_page' => 32,
            'a' => array(
                'value' => 'b',
            ),
        ), $admin->getFilterParameters());

        $this->assertTrue($admin->isDefaultFilter('foo'));
        $this->assertFalse($admin->isDefaultFilter('bar'));
        $this->assertFalse($admin->isDefaultFilter('a'));
    }

    /**
     * @group legacy
     */
    public function testDefaultBreadcrumbsBuilder()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('getParameter')
            ->with('sonata.admin.configuration.breadcrumbs')
            ->will($this->returnValue(array()));

        $pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
            ->disableOriginalConstructor()
            ->getMock();
        $pool->expects($this->once())
            ->method('getContainer')
            ->will($this->returnValue($container));

        $admin = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\AbstractAdmin', array(
            'admin.my_code', 'My\Class', 'MyBundle:ClassAdmin',
        ), '', true, true, true, array('getConfigurationPool'));
        $admin->expects($this->once())
            ->method('getConfigurationPool')
            ->will($this->returnValue($pool));

        $this->assertInstanceOf(
            'Sonata\AdminBundle\Admin\BreadcrumbsBuilder',
            $admin->getBreadcrumbsBuilder()
        );
    }

    /**
     * @group legacy
     */
    public function testBreadcrumbsBuilderSetter()
    {
        $admin = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\AbstractAdmin', array(
            'admin.my_code', 'My\Class', 'MyBundle:ClassAdmin',
        ));
        $this->assertSame($admin, $admin->setBreadcrumbsBuilder($builder = $this->getMock(
            'Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface'
        )));
        $this->assertSame($builder, $admin->getBreadcrumbsBuilder());
    }

    /**
     * @group legacy
     */
    public function testGetBreadcrumbs()
    {
        $admin = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\AbstractAdmin', array(
            'admin.my_code', 'My\Class', 'MyBundle:ClassAdmin',
        ));
        $builder = $this->prophesize(
            'Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface'
        );
        $action = 'myaction';
        $builder->getBreadcrumbs($admin, $action)->shouldBeCalled();
        $admin->setBreadcrumbsBuilder($builder->reveal())->getBreadcrumbs($action);
    }

    /**
     * @group legacy
     */
    public function testBuildBreadcrumbs()
    {
        $admin = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\AbstractAdmin', array(
            'admin.my_code', 'My\Class', 'MyBundle:ClassAdmin',
        ));
        $builder = $this->prophesize(
            'Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface'
        );
        $action = 'myaction';
        $menu = $this->getMock('Knp\Menu\ItemInterface');
        $builder->buildBreadcrumbs($admin, $action, $menu)
            ->shouldBeCalledTimes(1)
            ->willReturn($menu);
        $admin->setBreadcrumbsBuilder($builder->reveal());

        /* check the called is proxied only once */
        $this->assertSame($menu, $admin->buildBreadcrumbs($action, $menu));
        $this->assertSame($menu, $admin->buildBreadcrumbs($action, $menu));
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @group legacy
     */
    public function testCreateQueryLegacyCallWorks()
    {
        $admin = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\AbstractAdmin', array(
            'admin.my_code', 'My\Class', 'MyBundle:ClassAdmin',
        ));
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())
            ->method('createQuery')
            ->with('My\Class')
            ->willReturn('a query');

        $admin->setModelManager($modelManager);
        $this->assertSame('a query', $admin->createQuery('list'));
    }

    public function testGetDataSourceIterator()
    {
        $datagrid = $this->getMock('Sonata\AdminBundle\Datagrid\DatagridInterface');
        $datagrid->method('buildPager');

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->method('getExportFields')->will($this->returnValue(array(
            'field',
            'foo',
            'bar',
        )));
        $modelManager->expects($this->once())->method('getDataSourceIterator')
            ->with($this->equalTo($datagrid), $this->equalTo(array(
                'Feld' => 'field',
                1 => 'foo',
                2 => 'bar',
            )));

        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\AbstractAdmin')
            ->disableOriginalConstructor()
            ->setMethods(array('getDatagrid', 'getTranslationLabel', 'trans'))
            ->getMockForAbstractClass();
        $admin->method('getDatagrid')->will($this->returnValue($datagrid));
        $admin->setModelManager($modelManager);

        $admin->expects($this->any())
            ->method('getTranslationLabel')
            ->will($this->returnCallback(function ($label, $context = '', $type = '') {
                return $context.'.'.$type.'_'.$label;
            }));
        $admin->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(function ($label) {
                if ($label == 'export.label_field') {
                    return 'Feld';
                }

                return $label;
            }));

        $admin->getDataSourceIterator();
    }

    private function createTagAdmin(Post $post)
    {
        $postAdmin = $this->getMockBuilder('Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin')
            ->disableOriginalConstructor()
            ->getMock();

        $postAdmin->expects($this->any())->method('getObject')->will($this->returnValue($post));

        $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $formBuilder->expects($this->any())->method('getForm')->will($this->returnValue(null));

        $tagAdmin = $this->getMockBuilder('Sonata\AdminBundle\Tests\Fixtures\Admin\TagAdmin')
            ->setConstructorArgs(array(
                'admin.tag',
                'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Tag',
                'MyBundle:MyController',
            ))
            ->setMethods(array('getFormBuilder'))
            ->getMock();

        $tagAdmin->expects($this->any())->method('getFormBuilder')->will($this->returnValue($formBuilder));
        $tagAdmin->setParent($postAdmin);

        $tag = new Tag();
        $tagAdmin->setSubject($tag);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $tagAdmin->setRequest($request);

        $configurationPool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
            ->disableOriginalConstructor()
            ->getMock();

        $configurationPool->expects($this->any())->method('getPropertyAccessor')->will($this->returnValue(PropertyAccess::createPropertyAccessor()));

        $tagAdmin->setConfigurationPool($configurationPool);

        return $tagAdmin;
    }
}
