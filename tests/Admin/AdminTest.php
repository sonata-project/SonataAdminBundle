<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin;

use Doctrine\Common\Collections\Collection;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface;
use Sonata\AdminBundle\Model\AuditManagerInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Sonata\AdminBundle\Route\PathInfoBuilder;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Route\RoutesCache;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Tests\App\Builder\DatagridBuilder;
use Sonata\AdminBundle\Tests\App\Builder\FormContractor;
use Sonata\AdminBundle\Tests\App\Builder\ListBuilder;
use Sonata\AdminBundle\Tests\App\Builder\ShowBuilder;
use Sonata\AdminBundle\Tests\Fixtures\Admin\AvoidInfiniteLoopAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentVoteAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentWithCustomRouteAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\FieldDescription;
use Sonata\AdminBundle\Tests\Fixtures\Admin\FilteredAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\ModelAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostCategoryAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostWithCustomRouteAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\TagAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Comment;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Post;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\PostCategory;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Tag;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooToString;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooToStringNull;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\Doctrine\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Mapping\MemberMetadata;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminTest extends TestCase
{
    protected $cacheTempFolder;

    protected function setUp(): void
    {
        $this->cacheTempFolder = sprintf('%s/sonata_test_route', sys_get_temp_dir());
        $filesystem = new Filesystem();
        $filesystem->remove($this->cacheTempFolder);
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::__construct
     */
    public function testConstructor(): void
    {
        $class = 'Application\Sonata\NewsBundle\Entity\Post';
        $baseControllerName = 'Sonata\NewsBundle\Controller\PostAdminController';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $this->assertInstanceOf(AbstractAdmin::class, $admin);
        $this->assertSame($class, $admin->getClass());
        $this->assertSame($baseControllerName, $admin->getBaseControllerName());
    }

    public function testGetClass(): void
    {
        $class = Post::class;
        $baseControllerName = 'Sonata\NewsBundle\Controller\PostAdminController';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);

        $admin->setModelManager($this->getMockForAbstractClass(ModelManagerInterface::class));

        $admin->setSubject(new BlogPost());
        $this->assertSame(BlogPost::class, $admin->getClass());

        $admin->setSubClasses(['foo']);
        $this->assertSame(BlogPost::class, $admin->getClass());

        $admin->setSubject(null);
        $admin->setSubClasses([]);
        $this->assertSame($class, $admin->getClass());

        $admin->setSubClasses(['foo' => 'bar']);
        $admin->setRequest(new Request(['subclass' => 'foo']));
        $this->assertSame('bar', $admin->getClass());
    }

    public function testGetClassException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Feature not implemented: an embedded admin cannot have subclass');

        $class = 'Application\Sonata\NewsBundle\Entity\Post';
        $baseControllerName = 'Sonata\NewsBundle\Controller\PostAdminController';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $admin->setParentFieldDescription(new FieldDescription());
        $admin->setSubClasses(['foo' => 'bar']);
        $admin->setRequest(new Request(['subclass' => 'foo']));
        $admin->getClass();
    }

    public function testCheckAccessThrowsExceptionOnMadeUpAction(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'Action "made-up" could not be found'
        );
        $admin->checkAccess('made-up');
    }

    public function testCheckAccessThrowsAccessDeniedException(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->prophesize(SecurityHandlerInterface::class);
        $securityHandler->isGranted($admin, 'CUSTOM_ROLE', $admin)->willReturn(true);
        $securityHandler->isGranted($admin, 'EXTRA_CUSTOM_ROLE', $admin)->willReturn(false);
        $customExtension = $this->prophesize(AbstractAdminExtension::class);
        $customExtension->getAccessMapping($admin)->willReturn(
            ['custom_action' => ['CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE']]
        );
        $admin->addExtension($customExtension->reveal());
        $admin->setSecurityHandler($securityHandler->reveal());
        $this->expectException(
            AccessDeniedException::class
        );
        $this->expectExceptionMessage(
            'Access Denied to the action custom_action and role EXTRA_CUSTOM_ROLE'
        );
        $admin->checkAccess('custom_action');
    }

    public function testHasAccessOnMadeUpAction(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );

        $this->assertFalse($admin->hasAccess('made-up'));
    }

    public function testHasAccess(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->prophesize(SecurityHandlerInterface::class);
        $securityHandler->isGranted($admin, 'CUSTOM_ROLE', $admin)->willReturn(true);
        $securityHandler->isGranted($admin, 'EXTRA_CUSTOM_ROLE', $admin)->willReturn(false);
        $customExtension = $this->prophesize(AbstractAdminExtension::class);
        $customExtension->getAccessMapping($admin)->willReturn(
            ['custom_action' => ['CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE']]
        );
        $admin->addExtension($customExtension->reveal());
        $admin->setSecurityHandler($securityHandler->reveal());

        $this->assertFalse($admin->hasAccess('custom_action'));
    }

    public function testHasAccessAllowsAccess(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->prophesize(SecurityHandlerInterface::class);
        $securityHandler->isGranted($admin, 'CUSTOM_ROLE', $admin)->willReturn(true);
        $securityHandler->isGranted($admin, 'EXTRA_CUSTOM_ROLE', $admin)->willReturn(true);
        $customExtension = $this->prophesize(AbstractAdminExtension::class);
        $customExtension->getAccessMapping($admin)->willReturn(
            ['custom_action' => ['CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE']]
        );
        $admin->addExtension($customExtension->reveal());
        $admin->setSecurityHandler($securityHandler->reveal());

        $this->assertTrue($admin->hasAccess('custom_action'));
    }

    public function testHasAccessAllowsAccessEditAction(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->prophesize(SecurityHandlerInterface::class);
        $securityHandler->isGranted($admin, 'EDIT_ROLE', $admin)->willReturn(true);
        $customExtension = $this->prophesize(AbstractAdminExtension::class);
        $customExtension->getAccessMapping($admin)->willReturn(
            ['edit_action' => ['EDIT_ROLE']]
        );
        $admin->addExtension($customExtension->reveal());
        $admin->setSecurityHandler($securityHandler->reveal());

        $this->assertTrue($admin->hasAccess('edit_action'));
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::hasChild
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::addChild
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getChild
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::isChild
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::hasChildren
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getChildren
     */
    public function testChildren(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $this->assertFalse($postAdmin->hasChildren());
        $this->assertFalse($postAdmin->hasChild('comment'));

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $postAdmin->addChild($commentAdmin, 'post');

        $this->assertTrue($postAdmin->hasChildren());
        $this->assertTrue($postAdmin->hasChild('sonata.post.admin.comment'));

        $this->assertSame('sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getCode());
        $this->assertSame('sonata.post.admin.post|sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getBaseCodeRoute());
        $this->assertSame($postAdmin, $postAdmin->getChild('sonata.post.admin.comment')->getParent());
        $this->assertSame('post', $commentAdmin->getParentAssociationMapping());

        $this->assertFalse($postAdmin->isChild());
        $this->assertTrue($commentAdmin->isChild());

        $this->assertSame(['sonata.post.admin.comment' => $commentAdmin], $postAdmin->getChildren());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::configure
     */
    public function testConfigure(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $this->assertNotNull($admin->getUniqid());

        $admin->initialize();
        $this->assertNotNull($admin->getUniqid());
        $this->assertSame('Post', $admin->getClassnameLabel());

        $admin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $admin->setClassnameLabel('postcomment');

        $admin->initialize();
        $this->assertSame('postcomment', $admin->getClassnameLabel());
    }

    public function testConfigureWithValidParentAssociationMapping(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setParentAssociationMapping('Category');

        $admin->initialize();
        $this->assertSame('Category', $admin->getParentAssociationMapping());
    }

    public function provideGetBaseRoutePattern()
    {
        return [
            [
                'Application\Sonata\NewsBundle\Entity\Post',
                '/sonata/news/post',
            ],
            [
                'Application\Sonata\NewsBundle\Document\Post',
                '/sonata/news/post',
            ],
            [
                'MyApplication\MyBundle\Entity\Post',
                '/myapplication/my/post',
            ],
            [
                'MyApplication\MyBundle\Entity\Post\Category',
                '/myapplication/my/post-category',
            ],
            [
                'MyApplication\MyBundle\Entity\Product\Category',
                '/myapplication/my/product-category',
            ],
            [
                'MyApplication\MyBundle\Entity\Other\Product\Category',
                '/myapplication/my/other-product-category',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Document\Menu',
                '/cmf/foo/menu',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Phpcr\Menu',
                '/cmf/foo/menu',
            ],
            [
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu',
                '/symfony/barbar/menu',
            ],
            [
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu\Item',
                '/symfony/barbar/menu-item',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Orm\Menu',
                '/cmf/foo/menu',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\MongoDB\Menu',
                '/cmf/foo/menu',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\CouchDB\Menu',
                '/cmf/foo/menu',
            ],
            [
                'AppBundle\Entity\User',
                '/app/user',
            ],
            [
                'App\Entity\User',
                '/app/user',
            ],
        ];
    }

    /**
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePattern(string $objFqn, string $expected): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $this->assertSame($expected, $admin->getBaseRoutePattern());
    }

    /**
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePatternWithChildAdmin(string $objFqn, string $expected): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setParent($postAdmin);

        $this->assertSame(sprintf('%s/{id}/comment', $expected), $commentAdmin->getBaseRoutePattern());
    }

    /**
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePatternWithTwoNestedChildAdmin(string $objFqn, string $expected): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            'Application\Sonata\NewsBundle\Entity\CommentVote',
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );
        $commentAdmin->setParent($postAdmin);
        $commentVoteAdmin->setParent($commentAdmin);

        $this->assertSame(sprintf('%s/{id}/comment/{childId}/commentvote', $expected), $commentVoteAdmin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithSpecifedPattern(): void
    {
        $postAdmin = new PostWithCustomRouteAdmin('sonata.post.admin.post_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostWithCustomRouteAdminController');

        $this->assertSame('/post-custom', $postAdmin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithChildAdminAndWithSpecifedPattern(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentWithCustomRouteAdmin('sonata.post.admin.comment_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentWithCustomRouteAdminController');
        $commentAdmin->setParent($postAdmin);

        $this->assertSame('/sonata/news/post/{id}/comment-custom', $commentAdmin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithUnreconizedClassname(): void
    {
        $this->expectException(\RuntimeException::class);

        $admin = new PostAdmin('sonata.post.admin.post', 'News\Thing\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->getBaseRoutePattern();
    }

    public function provideGetBaseRouteName()
    {
        return [
            [
                'Application\Sonata\NewsBundle\Entity\Post',
                'admin_sonata_news_post',
            ],
            [
                'Application\Sonata\NewsBundle\Document\Post',
                'admin_sonata_news_post',
            ],
            [
                'MyApplication\MyBundle\Entity\Post',
                'admin_myapplication_my_post',
            ],
            [
                'MyApplication\MyBundle\Entity\Post\Category',
                'admin_myapplication_my_post_category',
            ],
            [
                'MyApplication\MyBundle\Entity\Product\Category',
                'admin_myapplication_my_product_category',
            ],
            [
                'MyApplication\MyBundle\Entity\Other\Product\Category',
                'admin_myapplication_my_other_product_category',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Document\Menu',
                'admin_cmf_foo_menu',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Phpcr\Menu',
                'admin_cmf_foo_menu',
            ],
            [
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu',
                'admin_symfony_barbar_menu',
            ],
            [
                'Symfony\Bundle\BarBarBundle\Doctrine\Phpcr\Menu\Item',
                'admin_symfony_barbar_menu_item',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\Orm\Menu',
                'admin_cmf_foo_menu',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\MongoDB\Menu',
                'admin_cmf_foo_menu',
            ],
            [
                'Symfony\Cmf\Bundle\FooBundle\Doctrine\CouchDB\Menu',
                'admin_cmf_foo_menu',
            ],
            [
                'AppBundle\Entity\User',
                'admin_app_user',
            ],
            [
                'App\Entity\User',
                'admin_app_user',
            ],
        ];
    }

    /**
     * @dataProvider provideGetBaseRouteName
     */
    public function testGetBaseRouteName(string $objFqn, string $expected): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame($expected, $admin->getBaseRouteName());
    }

    /**
     * @group legacy
     * @expectedDeprecation Calling "addChild" without second argument is deprecated since sonata-project/admin-bundle 3.35 and will not be allowed in 4.0.
     * @dataProvider provideGetBaseRouteName
     */
    public function testGetBaseRouteNameWithChildAdmin(string $objFqn, string $expected): void
    {
        $routeGenerator = new DefaultRouteGenerator(
            $this->createMock(RouterInterface::class),
            new RoutesCache($this->cacheTempFolder, true)
        );

        $container = new Container();
        $pool = new Pool($container, 'Sonata Admin', '/path/to/pic.png');

        $pathInfo = new PathInfoBuilder($this->createMock(AuditManagerInterface::class));
        $postAdmin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $container->set('sonata.post.admin.post', $postAdmin);
        $postAdmin->setConfigurationPool($pool);
        $postAdmin->setRouteBuilder($pathInfo);
        $postAdmin->setRouteGenerator($routeGenerator);
        $postAdmin->initialize();

        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $container->set('sonata.post.admin.comment', $commentAdmin);
        $commentAdmin->setConfigurationPool($pool);
        $commentAdmin->setRouteBuilder($pathInfo);
        $commentAdmin->setRouteGenerator($routeGenerator);
        $commentAdmin->initialize();

        $postAdmin->addChild($commentAdmin, 'post');

        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            'Application\Sonata\NewsBundle\Entity\CommentVote',
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );

        $container->set('sonata.post.admin.comment_vote', $commentVoteAdmin);
        $commentVoteAdmin->setConfigurationPool($pool);
        $commentVoteAdmin->setRouteBuilder($pathInfo);
        $commentVoteAdmin->setRouteGenerator($routeGenerator);
        $commentVoteAdmin->initialize();

        $commentAdmin->addChild($commentVoteAdmin);
        $pool->setAdminServiceIds([
            'sonata.post.admin.post',
            'sonata.post.admin.comment',
            'sonata.post.admin.comment_vote',
        ]);

        $this->assertSame(sprintf('%s_comment', $expected), $commentAdmin->getBaseRouteName());

        $this->assertTrue($postAdmin->hasRoute('show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.post.show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment.show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment|sonata.post.admin.comment_vote.show'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.comment.list'));
        $this->assertTrue($postAdmin->hasRoute('sonata.post.admin.comment|sonata.post.admin.comment_vote.list'));
        $this->assertFalse($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment.edit'));
        $this->assertFalse($commentAdmin->hasRoute('edit'));
        $this->assertSame('post', $commentAdmin->getParentAssociationMapping());

        /*
         * Test the route name from request
         */
        $postListRequest = new Request(
            [],
            [],
            [
                '_route' => sprintf('%s_list', $postAdmin->getBaseRouteName()),
            ]
        );

        $postAdmin->setRequest($postListRequest);
        $commentAdmin->setRequest($postListRequest);

        $this->assertTrue($postAdmin->isCurrentRoute('list'));
        $this->assertFalse($postAdmin->isCurrentRoute('create'));
        $this->assertFalse($commentAdmin->isCurrentRoute('list'));
        $this->assertFalse($commentVoteAdmin->isCurrentRoute('list'));
        $this->assertTrue($commentAdmin->isCurrentRoute('list', 'sonata.post.admin.post'));
        $this->assertFalse($commentAdmin->isCurrentRoute('edit', 'sonata.post.admin.post'));
        $this->assertTrue($commentVoteAdmin->isCurrentRoute('list', 'sonata.post.admin.post'));
        $this->assertFalse($commentVoteAdmin->isCurrentRoute('edit', 'sonata.post.admin.post'));
    }

    public function testGetBaseRouteNameWithUnreconizedClassname(): void
    {
        $this->expectException(\RuntimeException::class);

        $admin = new PostAdmin('sonata.post.admin.post', 'News\Thing\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->getBaseRouteName();
    }

    public function testGetBaseRouteNameWithSpecifiedName(): void
    {
        $postAdmin = new PostWithCustomRouteAdmin('sonata.post.admin.post_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame('post_custom', $postAdmin->getBaseRouteName());
    }

    public function testGetBaseRouteNameWithChildAdminAndWithSpecifiedName(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentWithCustomRouteAdmin('sonata.post.admin.comment_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentWithCustomRouteAdminController');
        $commentAdmin->setParent($postAdmin);

        $this->assertSame('admin_sonata_news_post_comment_custom', $commentAdmin->getBaseRouteName());
    }

    public function testGetBaseRouteNameWithTwoNestedChildAdminAndWithSpecifiedName(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentWithCustomRouteAdmin(
            'sonata.post.admin.comment_with_custom_route',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentWithCustomRouteAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            'Application\Sonata\NewsBundle\Entity\CommentVote',
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );
        $commentAdmin->setParent($postAdmin);
        $commentVoteAdmin->setParent($commentAdmin);

        $this->assertSame('admin_sonata_news_post_comment_custom_commentvote', $commentVoteAdmin->getBaseRouteName());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::setUniqid
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getUniqid
     */
    public function testSetUniqid(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $uniqid = uniqid();
        $admin->setUniqid($uniqid);

        $this->assertSame($uniqid, $admin->getUniqid());
    }

    public function testToString(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $s = new \stdClass();

        $this->assertNotEmpty($admin->toString($s));

        $s = new FooToString();
        $this->assertSame('salut', $admin->toString($s));
    }

    public function testToStringNull(): void
    {
        if (\PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('PHP 8.0 does not allow __toString() method to return null');
        }

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        // To string method is implemented, but returns null
        $s = new FooToStringNull();
        $this->assertNotEmpty($admin->toString($s));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     * @expectedDeprecation Passing boolean as argument 1 for Sonata\AdminBundle\Admin\AbstractAdmin::toString() is deprecated since sonata-project/admin-bundle 3.76. Only object will be allowed in version 4.0.
     */
    public function testToStringForNonObject(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $this->assertSame('', $admin->toString(false));
    }

    public function testIsAclEnabled(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertFalse($postAdmin->isAclEnabled());

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setSecurityHandler($this->createMock(AclSecurityHandlerInterface::class));
        $this->assertTrue($commentAdmin->isAclEnabled());
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Calling Sonata\AdminBundle\Admin\AbstractAdmin::getActiveSubclassCode() when there is no active subclass is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0. Use Sonata\AdminBundle\Admin\AbstractAdmin::hasActiveSubClass() to know if there is an active subclass.
     *
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getSubClasses
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getSubClass
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::setSubClasses
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::hasSubClass
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::hasActiveSubClass
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getActiveSubClass
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getActiveSubclassCode
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getClass
     */
    public function testSubClass(): void
    {
        // NEXT_MAJOR: Remove the "@group" and "@expectedDeprecation" annotations
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $this->assertFalse($admin->hasSubClass('test'));
        $this->assertFalse($admin->hasActiveSubClass());
        $this->assertCount(0, $admin->getSubClasses());
        $this->assertNull($admin->getActiveSubClass());
        $this->assertNull($admin->getActiveSubclassCode());
        $this->assertSame(Post::class, $admin->getClass());

        // Just for the record, if there is no inheritance set, the getSubject is not used
        // the getSubject can also lead to some issue
        $admin->setSubject(new BlogPost());
        $this->assertSame(BlogPost::class, $admin->getClass());

        $admin->setSubClasses([
            'extended1' => 'NewsBundle\Entity\PostExtended1',
            'extended2' => 'NewsBundle\Entity\PostExtended2',
        ]);
        $this->assertFalse($admin->hasSubClass('test'));
        $this->assertTrue($admin->hasSubClass('extended1'));
        $this->assertFalse($admin->hasActiveSubClass());
        $this->assertCount(2, $admin->getSubClasses());
        // NEXT_MAJOR: remove the following 2 `assertNull()` assertions
        $this->assertNull($admin->getActiveSubClass());
        $this->assertNull($admin->getActiveSubclassCode());
        $this->assertSame(
            BlogPost::class,
            $admin->getClass(),
            'When there is no subclass in the query the class parameter should be returned'
        );

        $request = new Request(['subclass' => 'extended1']);
        $admin->setRequest($request);
        $this->assertFalse($admin->hasSubClass('test'));
        $this->assertTrue($admin->hasSubClass('extended1'));
        $this->assertTrue($admin->hasActiveSubClass());
        $this->assertCount(2, $admin->getSubClasses());
        $this->assertSame(
            'NewsBundle\Entity\PostExtended1',
            $admin->getActiveSubClass(),
            'It should return the curently active sub class.'
        );
        $this->assertSame('extended1', $admin->getActiveSubclassCode());
        $this->assertSame(
            'NewsBundle\Entity\PostExtended1',
            $admin->getClass(),
            'getClass() should return the name of the sub class when passed through a request query parameter.'
        );

        $request->query->set('subclass', 'inject');

        $this->assertNull($admin->getActiveSubclassCode());
        // NEXT_MAJOR: remove the previous `assertNull()` assertion and uncomment the following lines
        // $this->expectException(\LogicException::class);
        // $this->expectExceptionMessage(sprintf('Admin "%s" has no active subclass.', PostAdmin::class));
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Calling Sonata\AdminBundle\Admin\AbstractAdmin::getActiveSubclassCode() when there is no active subclass is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0. Use Sonata\AdminBundle\Admin\AbstractAdmin::hasActiveSubClass() to know if there is an active subclass.
     */
    public function testNonExistantSubclass(): void
    {
        // NEXT_MAJOR: Remove the "@group" and "@expectedDeprecation" annotations
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($this->getMockForAbstractClass(ModelManagerInterface::class));

        $admin->setRequest(new Request(['subclass' => 'inject']));

        $admin->setSubClasses(['extended1' => 'NewsBundle\Entity\PostExtended1', 'extended2' => 'NewsBundle\Entity\PostExtended2']);

        $this->assertTrue($admin->hasActiveSubClass());

        $this->expectException(\RuntimeException::class);

        $admin->getActiveSubClass();
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::hasActiveSubClass
     */
    public function testOnlyOneSubclassNeededToBeActive(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setSubClasses(['extended1' => 'NewsBundle\Entity\PostExtended1']);
        $request = new Request(['subclass' => 'extended1']);
        $admin->setRequest($request);
        $this->assertTrue($admin->hasActiveSubClass());
    }

    /**
     * @group legacy
     * @expectedDeprecation Method "Sonata\AdminBundle\Admin\AbstractAdmin::addSubClass" is deprecated since sonata-project/admin-bundle 3.30 and will be removed in 4.0.
     */
    public function testAddSubClassIsDeprecated(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $admin->addSubClass('whatever');
    }

    /**
     * @group legacy
     */
    public function testGetPerPageOptions(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $perPageOptions = $admin->getPerPageOptions();

        foreach ($perPageOptions as $perPage) {
            $this->assertSame(0, $perPage % 4);
        }

        $admin->setPerPageOptions([500, 1000]);
        $this->assertSame([500, 1000], $admin->getPerPageOptions());
    }

    public function testGetLabelTranslatorStrategy(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getLabelTranslatorStrategy());

        $labelTranslatorStrategy = $this->createMock(LabelTranslatorStrategyInterface::class);
        $admin->setLabelTranslatorStrategy($labelTranslatorStrategy);
        $this->assertSame($labelTranslatorStrategy, $admin->getLabelTranslatorStrategy());
    }

    public function testGetRouteBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getRouteBuilder());

        $routeBuilder = $this->createMock(RouteBuilderInterface::class);
        $admin->setRouteBuilder($routeBuilder);
        $this->assertSame($routeBuilder, $admin->getRouteBuilder());
    }

    public function testGetMenuFactory(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getMenuFactory());

        $menuFactory = $this->createMock(FactoryInterface::class);
        $admin->setMenuFactory($menuFactory);
        $this->assertSame($menuFactory, $admin->getMenuFactory());
    }

    public function testGetExtensions(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame([], $admin->getExtensions());

        $adminExtension1 = $this->createMock(AdminExtensionInterface::class);
        $adminExtension2 = $this->createMock(AdminExtensionInterface::class);

        $admin->addExtension($adminExtension1);
        $admin->addExtension($adminExtension2);
        $this->assertSame([$adminExtension1, $adminExtension2], $admin->getExtensions());
    }

    public function testGetFilterTheme(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame([], $admin->getFilterTheme());

        $admin->setFilterTheme(['FooTheme']);
        $this->assertSame(['FooTheme'], $admin->getFilterTheme());
    }

    public function testGetFormTheme(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame([], $admin->getFormTheme());

        $admin->setFormTheme(['FooTheme']);

        $this->assertSame(['FooTheme'], $admin->getFormTheme());
    }

    public function testGetValidator(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getValidator());

        $validator = $this->getMockForAbstractClass(ValidatorInterface::class);

        $admin->setValidator($validator);
        $this->assertSame($validator, $admin->getValidator());
    }

    public function testGetSecurityHandler(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getSecurityHandler());

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $admin->setSecurityHandler($securityHandler);
        $this->assertSame($securityHandler, $admin->getSecurityHandler());
    }

    public function testGetSecurityInformation(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame([], $admin->getSecurityInformation());

        $securityInformation = [
            'GUEST' => ['VIEW', 'LIST'],
            'STAFF' => ['EDIT', 'LIST', 'CREATE'],
        ];

        $admin->setSecurityInformation($securityInformation);
        $this->assertSame($securityInformation, $admin->getSecurityInformation());
    }

    public function testGetManagerType(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getManagerType());

        $admin->setManagerType('foo_orm');
        $this->assertSame('foo_orm', $admin->getManagerType());
    }

    public function testGetModelManager(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getModelManager());

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $admin->setModelManager($modelManager);
        $this->assertSame($modelManager, $admin->getModelManager());
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @group legacy
     */
    public function testGetBaseCodeRoute(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame('', $admin->getBaseCodeRoute());

        $admin->setBaseCodeRoute('foo');
        $this->assertSame('foo', $admin->getBaseCodeRoute());
    }

    // NEXT_MAJOR: uncomment this method.
    // public function testGetBaseCodeRoute()
    // {
    //     $postAdmin = new PostAdmin(
    //         'sonata.post.admin.post',
    //         'NewsBundle\Entity\Post',
    //         'Sonata\NewsBundle\Controller\PostAdminController'
    //     );
    //     $commentAdmin = new CommentAdmin(
    //         'sonata.post.admin.comment',
    //         'Application\Sonata\NewsBundle\Entity\Comment',
    //         'Sonata\NewsBundle\Controller\CommentAdminController'
    //     );
    //
    //     $this->assertSame($postAdmin->getCode(), $postAdmin->getBaseCodeRoute());
    //
    //     $postAdmin->addChild($commentAdmin);
    //
    //     $this->assertSame(
    //         'sonata.post.admin.post|sonata.post.admin.comment',
    //         $commentAdmin->getBaseCodeRoute()
    //     );
    // }

    public function testGetRouteGenerator(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getRouteGenerator());

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);

        $admin->setRouteGenerator($routeGenerator);
        $this->assertSame($routeGenerator, $admin->getRouteGenerator());
    }

    public function testGetConfigurationPool(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getConfigurationPool());

        $pool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $admin->setConfigurationPool($pool);
        $this->assertSame($pool, $admin->getConfigurationPool());
    }

    public function testGetShowBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getShowBuilder());

        $showBuilder = $this->createMock(ShowBuilderInterface::class);

        $admin->setShowBuilder($showBuilder);
        $this->assertSame($showBuilder, $admin->getShowBuilder());
    }

    public function testGetListBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getListBuilder());

        $listBuilder = $this->createMock(ListBuilderInterface::class);

        $admin->setListBuilder($listBuilder);
        $this->assertSame($listBuilder, $admin->getListBuilder());
    }

    public function testGetDatagridBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getDatagridBuilder());

        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        $admin->setDatagridBuilder($datagridBuilder);
        $this->assertSame($datagridBuilder, $admin->getDatagridBuilder());
    }

    public function testGetFormContractor(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getFormContractor());

        $formContractor = $this->createMock(FormContractorInterface::class);

        $admin->setFormContractor($formContractor);
        $this->assertSame($formContractor, $admin->getFormContractor());
    }

    public function testGetRequest(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertFalse($admin->hasRequest());

        $request = new Request();

        $admin->setRequest($request);
        $this->assertSame($request, $admin->getRequest());
        $this->assertTrue($admin->hasRequest());
    }

    public function testGetRequestWithException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The Request object has not been set');

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->getRequest();
    }

    public function testGetTranslationDomain(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame('messages', $admin->getTranslationDomain());

        $admin->setTranslationDomain('foo');
        $this->assertSame('foo', $admin->getTranslationDomain());
    }

    /**
     * @group legacy
     */
    public function testGetTranslator(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getTranslator());

        $translator = $this->createMock(TranslatorInterface::class);

        $admin->setTranslator($translator);
        $this->assertSame($translator, $admin->getTranslator());
    }

    public function testGetShowGroups(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        $this->assertFalse($admin->getShowGroups('sonata_deprecation_mute'));

        $groups = ['foo', 'bar', 'baz'];

        $admin->setShowGroups($groups);
        $this->assertSame($groups, $admin->getShowGroups());
    }

    public function testGetFormGroups(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        $this->assertFalse($admin->getFormGroups('sonata_deprecation_mute'));

        $groups = ['foo', 'bar', 'baz'];

        $admin->setFormGroups($groups);
        $this->assertSame($groups, $admin->getFormGroups());
    }

    public function testGetMaxPageLinks(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame(25, $admin->getMaxPageLinks());

        $admin->setMaxPageLinks(14);
        $this->assertSame(14, $admin->getMaxPageLinks());
    }

    /**
     * @group legacy
     */
    public function testGetMaxPerPage(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame(32, $admin->getMaxPerPage());

        $admin->setMaxPerPage(94);
        $this->assertSame(94, $admin->getMaxPerPage());
    }

    public function testGetLabel(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertNull($admin->getLabel());

        $admin->setLabel('FooLabel');
        $this->assertSame('FooLabel', $admin->getLabel());
    }

    public function testGetBaseController(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame('Sonata\NewsBundle\Controller\PostAdminController', $admin->getBaseControllerName());

        $admin->setBaseControllerName('Sonata\NewsBundle\Controller\FooAdminController');
        $this->assertSame('Sonata\NewsBundle\Controller\FooAdminController', $admin->getBaseControllerName());
    }

    public function testGetTemplates(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $templates = [
            'list' => '@FooAdmin/CRUD/list.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $templateRegistry = $this->prophesize(MutableTemplateRegistryInterface::class);
        $templateRegistry->getTemplates()->shouldBeCalled()->willReturn($templates);

        $admin->setTemplateRegistry($templateRegistry->reveal());

        $this->assertSame($templates, $admin->getTemplates());
    }

    public function testGetTemplate1(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $templateRegistry = $this->prophesize(MutableTemplateRegistryInterface::class);
        $templateRegistry->getTemplate('edit')->shouldBeCalled()->willReturn('@FooAdmin/CRUD/edit.html.twig');
        $templateRegistry->getTemplate('show')->shouldBeCalled()->willReturn('@FooAdmin/CRUD/show.html.twig');

        $admin->setTemplateRegistry($templateRegistry->reveal());

        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $admin->getTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/show.html.twig', $admin->getTemplate('show'));
    }

    public function testGetIdParameter(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            'NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );

        $this->assertSame('id', $postAdmin->getIdParameter());
        $this->assertFalse($postAdmin->isChild());

        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentAdmin->setParent($postAdmin);

        $this->assertTrue($commentAdmin->isChild());
        $this->assertSame('childId', $commentAdmin->getIdParameter());

        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            'Application\Sonata\NewsBundle\Entity\CommentVote',
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );
        $commentVoteAdmin->setParent($commentAdmin);

        $this->assertTrue($commentVoteAdmin->isChild());
        $this->assertSame('childChildId', $commentVoteAdmin->getIdParameter());
    }

    public function testGetExportFormats(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame(['json', 'xml', 'csv', 'xls'], $admin->getExportFormats());
    }

    public function testGetUrlsafeIdentifier(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $model = new \stdClass();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects($this->once())
            ->method('getUrlSafeIdentifier')
            ->with($this->equalTo($model))
            ->willReturn('foo');
        $admin->setModelManager($modelManager);

        $this->assertSame('foo', $admin->getUrlSafeIdentifier($model));
    }

    public function testDeterminedPerPageValue(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertFalse($admin->determinedPerPageValue('foo'));
        $this->assertFalse($admin->determinedPerPageValue(123));
        $this->assertTrue($admin->determinedPerPageValue(16));
    }

    public function testIsGranted(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager
            ->method('getNormalizedIdentifier')
            ->willReturnCallback(static function (?object $model = null): ?string {
                return $model ? $model->id : null;
            });

        $admin->setModelManager($modelManager);

        $entity1 = new \stdClass();
        $entity1->id = '1';

        $securityHandler = $this->createMock(AclSecurityHandlerInterface::class);
        $securityHandler
            ->expects($this->exactly(6))
            ->method('isGranted')
            ->willReturnCallback(static function (
                AdminInterface $adminIn,
                string $attributes,
                ?object $object = null
            ) use (
                $admin,
                $entity1
            ): bool {
                return $admin === $adminIn && 'FOO' === $attributes &&
                    ($object === $admin || $object === $entity1);
            });

        $admin->setSecurityHandler($securityHandler);

        $this->assertTrue($admin->isGranted('FOO'));
        $this->assertTrue($admin->isGranted('FOO'));
        $this->assertTrue($admin->isGranted('FOO', $entity1));
        $this->assertTrue($admin->isGranted('FOO', $entity1));
        $this->assertFalse($admin->isGranted('BAR'));
        $this->assertFalse($admin->isGranted('BAR'));
        $this->assertFalse($admin->isGranted('BAR', $entity1));
        $this->assertFalse($admin->isGranted('BAR', $entity1));

        $entity2 = new \stdClass();
        $entity2->id = '2';

        $this->assertFalse($admin->isGranted('BAR', $entity2));
        $this->assertFalse($admin->isGranted('BAR', $entity2));

        $entity3 = new \stdClass();
        $entity3->id = '3';

        $this->assertFalse($admin->isGranted('BAR', $entity3));
        $this->assertFalse($admin->isGranted('BAR', $entity3));
    }

    public function testSupportsPreviewMode(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertFalse($admin->supportsPreviewMode());
    }

    public function testGetPermissionsShow(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame(['LIST'], $admin->getPermissionsShow(AbstractAdmin::CONTEXT_DASHBOARD));
        $this->assertSame(['LIST'], $admin->getPermissionsShow(AbstractAdmin::CONTEXT_MENU));
        $this->assertSame(['LIST'], $admin->getPermissionsShow('foo'));
    }

    public function testShowIn(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $securityHandler = $this->createMock(AclSecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static function (AdminInterface $adminIn, array $attributes, $object = null) use ($admin): bool {
                return $admin === $adminIn && $attributes === ['LIST'];
            });

        $admin->setSecurityHandler($securityHandler);

        $this->assertTrue($admin->showIn(AbstractAdmin::CONTEXT_DASHBOARD));
        $this->assertTrue($admin->showIn(AbstractAdmin::CONTEXT_MENU));
        $this->assertTrue($admin->showIn('foo'));
    }

    public function testGetObjectIdentifier(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame('sonata.post.admin.post', $admin->getObjectIdentifier());
    }

    /**
     * @group legacy
     */
    public function testTrans(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setTranslationDomain('fooMessageDomain');

        $translator = $this->createMock(TranslatorInterface::class);
        $admin->setTranslator($translator);

        $translator->expects($this->once())
            ->method('trans')
            ->with($this->equalTo('foo'), $this->equalTo([]), $this->equalTo('fooMessageDomain'))
            ->willReturn('fooTranslated');

        $this->assertSame('fooTranslated', $admin->trans('foo'));
    }

    /**
     * @group legacy
     */
    public function testTransWithMessageDomain(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $translator = $this->createMock(TranslatorInterface::class);
        $admin->setTranslator($translator);

        $translator->expects($this->once())
            ->method('trans')
            ->with($this->equalTo('foo'), $this->equalTo(['name' => 'Andrej']), $this->equalTo('fooMessageDomain'))
            ->willReturn('fooTranslated');

        $this->assertSame('fooTranslated', $admin->trans('foo', ['name' => 'Andrej'], 'fooMessageDomain'));
    }

    /**
     * @group legacy
     */
    public function testTransChoice(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setTranslationDomain('fooMessageDomain');

        $translator = $this->createMock(TranslatorInterface::class);
        $admin->setTranslator($translator);

        $translator->expects($this->once())
            ->method('transChoice')
            ->with($this->equalTo('foo'), $this->equalTo(2), $this->equalTo([]), $this->equalTo('fooMessageDomain'))
            ->willReturn('fooTranslated');

        $this->assertSame('fooTranslated', $admin->transChoice('foo', 2));
    }

    /**
     * @group legacy
     */
    public function testTransChoiceWithMessageDomain(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $translator = $this->createMock(TranslatorInterface::class);
        $admin->setTranslator($translator);

        $translator->expects($this->once())
            ->method('transChoice')
            ->with($this->equalTo('foo'), $this->equalTo(2), $this->equalTo(['name' => 'Andrej']), $this->equalTo('fooMessageDomain'))
            ->willReturn('fooTranslated');

        $this->assertSame('fooTranslated', $admin->transChoice('foo', 2, ['name' => 'Andrej'], 'fooMessageDomain'));
    }

    public function testSetFilterPersister(): void
    {
        $admin = new class('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'SonataNewsBundle\Controller\PostAdminController') extends PostAdmin {
            public function persistFilters(): bool
            {
                return $this->persistFilters;
            }
        };

        $filterPersister = $this->createMock(FilterPersisterInterface::class);

        $admin->setFilterPersister($filterPersister);
        $this->assertTrue($admin->persistFilters());
    }

    public function testGetRootCode(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame('sonata.post.admin.post', $admin->getRootCode());

        $parentAdmin = new PostAdmin('sonata.post.admin.post.parent', 'NewsBundle\Entity\PostParent', 'Sonata\NewsBundle\Controller\PostParentAdminController');
        $parentFieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $parentFieldDescription->expects($this->once())
            ->method('getAdmin')
            ->willReturn($parentAdmin);

        $this->assertFalse($admin->hasParentFieldDescription());
        $admin->setParentFieldDescription($parentFieldDescription);
        $this->assertSame($parentFieldDescription, $admin->getParentFieldDescription());
        $this->assertSame('sonata.post.admin.post.parent', $admin->getRootCode());
    }

    public function testGetRoot(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertSame($admin, $admin->getRoot());

        $parentAdmin = new PostAdmin('sonata.post.admin.post.parent', 'NewsBundle\Entity\PostParent', 'Sonata\NewsBundle\Controller\PostParentAdminController');
        $parentFieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $parentFieldDescription->expects($this->once())
            ->method('getAdmin')
            ->willReturn($parentAdmin);

        $this->assertFalse($admin->hasParentFieldDescription());
        $admin->setParentFieldDescription($parentFieldDescription);
        $this->assertSame($parentFieldDescription, $admin->getParentFieldDescription());
        $this->assertSame($parentAdmin, $admin->getRoot());
    }

    public function testGetExportFields(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects($this->once())
            ->method('getExportFields')
            ->with($this->equalTo('NewsBundle\Entity\Post'))
            ->willReturn(['foo', 'bar']);

        $admin->setModelManager($modelManager);
        $this->assertSame(['foo', 'bar'], $admin->getExportFields());
    }

    public function testGetPersistentParametersWithNoExtension(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertEmpty($admin->getPersistentParameters());
    }

    public function testGetPersistentParametersWithInvalidExtension(): void
    {
        $this->expectException(\RuntimeException::class);

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $extension = $this->createMock(AdminExtensionInterface::class);
        $extension->expects($this->once())->method('getPersistentParameters')->willReturn(null);

        $admin->addExtension($extension);

        $admin->getPersistentParameters();
    }

    public function testGetPersistentParametersWithValidExtension(): void
    {
        $expected = [
            'context' => 'foobar',
        ];

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $extension = $this->createMock(AdminExtensionInterface::class);
        $extension->expects($this->once())->method('getPersistentParameters')->willReturn($expected);

        $admin->addExtension($extension);

        $this->assertSame($expected, $admin->getPersistentParameters());
    }

    public function testGetNewInstanceForChildAdminWithParentValue(): void
    {
        $post = new Post();

        $postAdmin = $this->getMockBuilder(PostAdmin::class)->disableOriginalConstructor()->getMock();
        $postAdmin->method('getObject')->willReturn($post);
        $postAdmin->method('getIdParameter')->willReturn('parent_id');

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder->method('getForm')->willReturn(null);

        $tag = new Tag();

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('getModelInstance')->willReturn($tag);

        $tagAdmin = new TagAdmin('admin.tag', Tag::class, 'MyBundle\MyController');
        $tagAdmin->setModelManager($modelManager);
        $tagAdmin->setParent($postAdmin);

        $request = $this->createStub(Request::class);
        $tagAdmin->setRequest($request);

        $configurationPool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurationPool->method('getPropertyAccessor')->willReturn(PropertyAccess::createPropertyAccessor());

        $tagAdmin->setConfigurationPool($configurationPool);

        $tag = $tagAdmin->getNewInstance();

        $this->assertSame($post, $tag->getPost());
    }

    public function testGetNewInstanceForChildAdminWithCollectionParentValue(): void
    {
        $post = new Post();

        $postAdmin = $this->getMockBuilder(PostAdmin::class)->disableOriginalConstructor()->getMock();
        $postAdmin->method('getObject')->willReturn($post);
        $postAdmin->method('getIdParameter')->willReturn('parent_id');

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder->method('getForm')->willReturn(null);

        $postCategory = new PostCategory();

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('getModelInstance')->willReturn($postCategory);

        $postCategoryAdmin = new PostCategoryAdmin('admin.post_category', PostCategoryAdmin::class, 'MyBundle\MyController');
        $postCategoryAdmin->setModelManager($modelManager);
        $postCategoryAdmin->setParent($postAdmin);

        $request = $this->createStub(Request::class);
        $postCategoryAdmin->setRequest($request);

        $configurationPool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurationPool->method('getPropertyAccessor')->willReturn(PropertyAccess::createPropertyAccessor());

        $postCategoryAdmin->setConfigurationPool($configurationPool);

        $postCategory = $postCategoryAdmin->getNewInstance();

        $this->assertInstanceOf(Collection::class, $postCategory->getPosts());
        $this->assertCount(1, $postCategory->getPosts());
        $this->assertContains($post, $postCategory->getPosts());
    }

    public function testGetNewInstanceForEmbededAdminWithParentValue(): void
    {
        $post = new Post();

        $postAdmin = $this->getMockBuilder(PostAdmin::class)->disableOriginalConstructor()->getMock();
        $postAdmin->method('getObject')->willReturn($post);
        $postAdmin->method('getIdParameter')->willReturn('parent_id');

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder->method('getForm')->willReturn(null);

        $parentField = $this->createStub(FieldDescriptionInterface::class);
        $parentField->method('getAdmin')->willReturn($postAdmin);
        $parentField->method('getParentAssociationMappings')->willReturn([]);
        $parentField->method('getAssociationMapping')->willReturn(['fieldName' => 'tag', 'mappedBy' => 'post']);

        $tag = new Tag();

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('getModelInstance')->willReturn($tag);

        $tagAdmin = new TagAdmin('admin.tag', Tag::class, 'MyBundle\MyController');
        $tagAdmin->setModelManager($modelManager);
        $tagAdmin->setParentFieldDescription($parentField);

        $request = $this->createStub(Request::class);
        $tagAdmin->setRequest($request);

        $configurationPool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurationPool->method('getPropertyAccessor')->willReturn(PropertyAccess::createPropertyAccessor());

        $tagAdmin->setConfigurationPool($configurationPool);

        $tag = $tagAdmin->getNewInstance();

        $this->assertSame($post, $tag->getPost());
    }

    public function testFormAddPostSubmitEventForPreValidation(): void
    {
        $modelAdmin = new ModelAdmin('sonata.post.admin.model', \stdClass::class, 'Sonata\FooBundle\Controller\ModelAdminController');
        $object = new \stdClass();

        $labelTranslatorStrategy = $this->createMock(LabelTranslatorStrategyInterface::class);
        $modelAdmin->setLabelTranslatorStrategy($labelTranslatorStrategy);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
                ->method('getMetadataFor')
                ->willReturn($this->createMock(MemberMetadata::class));
        $modelAdmin->setValidator($validator);

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->method('getNewFieldDescriptionInstance')
            ->willReturn(new FieldDescription());
        $modelAdmin->setModelManager($modelManager);

        // a Admin class to test that preValidate is called
        $testAdminPreValidate = $this->createMock(AbstractAdmin::class);
        $testAdminPreValidate->expects($this->once())
                ->method('preValidate')
                ->with($this->identicalTo($object));

        $event = $this->createMock(FormEvent::class);
        $event
                ->method('getData')
                ->willReturn($object);

        $formBuild = $this->createMock(FormBuilder::class);
        $formBuild->expects($this->once())
                ->method('addEventListener')
                ->with(
                    $this->identicalTo(FormEvents::POST_SUBMIT),
                    $this->callback(static function ($callback) use ($testAdminPreValidate, $event): bool {
                        if (\is_callable($callback)) {
                            $closure = $callback->bindTo($testAdminPreValidate);
                            $closure($event);

                            return true;
                        }

                        return false;
                    }),
                    $this->greaterThan(0)
                );

        $form = $this->createMock(FormInterface::class);
        $formBuild->expects($this->once())
            ->method('getForm')
            ->willReturn($form)
        ;

        $formContractor = $this->createMock(FormContractorInterface::class);
        $formContractor
                ->method('getDefaultOptions')
                ->willReturn([]);
        $formContractor
                ->method('getFormBuilder')
                ->willReturn($formBuild);

        $modelAdmin->setFormContractor($formContractor);
        $modelAdmin->setSubject($object);
        $modelAdmin->defineFormBuilder($formBuild);
        $modelAdmin->getForm();
    }

    public function testCanAddInlineValidationOnlyForGenericMetadata(): void
    {
        $modelAdmin = new ModelAdmin('sonata.post.admin.model', \stdClass::class, 'Sonata\FooBundle\Controller\ModelAdminController');
        $object = new \stdClass();

        $labelTranslatorStrategy = $this->createStub(LabelTranslatorStrategyInterface::class);
        $modelAdmin->setLabelTranslatorStrategy($labelTranslatorStrategy);

        $validator = $this->createStub(ValidatorInterface::class);
        $metadata = $this->createStub(PropertyMetadataInterface::class);
        $validator
            ->method('getMetadataFor')
            ->willReturn($metadata);
        $modelAdmin->setValidator($validator);

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager
            ->method('getNewFieldDescriptionInstance')
            ->willReturn(new FieldDescription());
        $modelAdmin->setModelManager($modelManager);

        $event = $this->createStub(FormEvent::class);
        $event
            ->method('getData')
            ->willReturn($object);

        $formBuild = $this->createStub(FormBuilder::class);

        $formContractor = $this->createStub(FormContractorInterface::class);
        $formContractor
            ->method('getDefaultOptions')
            ->willReturn([]);
        $formContractor
            ->method('getFormBuilder')
            ->willReturn($formBuild);

        $modelAdmin->setFormContractor($formContractor);
        $modelAdmin->setSubject($object);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot add inline validator for stdClass because its metadata is an instance of %s instead of Symfony\Component\Validator\Mapping\GenericMetadata',
                \get_class($metadata)
            )
        );

        $modelAdmin->defineFormBuilder($formBuild);
    }

    public function testRemoveFieldFromFormGroup(): void
    {
        $formGroups = [
            'foobar' => [
                'fields' => [
                    'foo' => 'foo',
                    'bar' => 'bar',
                ],
            ],
        ];

        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setFormGroups($formGroups);

        $admin->removeFieldFromFormGroup('foo');
        $this->assertSame($admin->getFormGroups(), [
            'foobar' => [
                'fields' => [
                    'bar' => 'bar',
                ],
            ],
        ]);

        $admin->removeFieldFromFormGroup('bar');
        $this->assertSame($admin->getFormGroups(), []);
    }

    public function testGetFilterParameters(): void
    {
        $authorId = uniqid();

        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setParentAssociationMapping('post.author');
        $commentAdmin->setParent($postAdmin);

        $request = $this->createMock(Request::class);
        $query = $this->createMock(ParameterBag::class);
        $query
            ->method('get')
            ->willReturn([
                'filter' => [
                    '_page' => '1',
                    '_per_page' => '32',
                ],
            ]);

        $request->query = $query;
        $request
            ->method('get')
            ->willReturn($authorId);

        $commentAdmin->setRequest($request);

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->method('getDefaultSortValues')
            ->willReturn([]);

        $commentAdmin->setModelManager($modelManager);

        $parameters = $commentAdmin->getFilterParameters();

        $this->assertTrue(isset($parameters['post__author']));
        $this->assertSame(['value' => $authorId], $parameters['post__author']);
    }

    public function testGetFilterFieldDescription(): void
    {
        $modelAdmin = new ModelAdmin('sonata.post.admin.model', 'Application\Sonata\FooBundle\Entity\Model', 'Sonata\FooBundle\Controller\ModelAdminController');

        $fooFieldDescription = new FieldDescription();
        $barFieldDescription = new FieldDescription();
        $bazFieldDescription = new FieldDescription();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects($this->exactly(3))
            ->method('getNewFieldDescriptionInstance')
            ->willReturnCallback(static function ($adminClass, string $name, $filterOptions) use ($fooFieldDescription, $barFieldDescription, $bazFieldDescription) {
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
            });

        $modelAdmin->setModelManager($modelManager);

        $pager = $this->createMock(PagerInterface::class);

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->once())
            ->method('getPager')
            ->willReturn($pager);

        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);
        $datagridBuilder->expects($this->once())
            ->method('getBaseDatagrid')
            ->with($this->identicalTo($modelAdmin), [])
            ->willReturn($datagrid);

        $datagridBuilder->expects($this->exactly(3))
            ->method('addFilter')
            ->willReturnCallback(static function ($datagrid, $type, $fieldDescription, AdminInterface $admin): void {
                $admin->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);
                $fieldDescription->mergeOption('field_options', ['required' => false]);
            });

        $modelAdmin->setDatagridBuilder($datagridBuilder);

        $this->assertSame(['foo' => $fooFieldDescription, 'bar' => $barFieldDescription, 'baz' => $bazFieldDescription], $modelAdmin->getFilterFieldDescriptions());
        $this->assertFalse($modelAdmin->hasFilterFieldDescription('fooBar'));
        $this->assertTrue($modelAdmin->hasFilterFieldDescription('foo'));
        $this->assertTrue($modelAdmin->hasFilterFieldDescription('bar'));
        $this->assertTrue($modelAdmin->hasFilterFieldDescription('baz'));
        $this->assertSame($fooFieldDescription, $modelAdmin->getFilterFieldDescription('foo'));
        $this->assertSame($barFieldDescription, $modelAdmin->getFilterFieldDescription('bar'));
        $this->assertSame($bazFieldDescription, $modelAdmin->getFilterFieldDescription('baz'));
    }

    public function testGetSubjectNoRequest(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects($this->never())
            ->method('find');

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        $this->assertFalse($admin->hasSubject());
    }

    public function testGetSideMenu(): void
    {
        $item = $this->createMock(ItemInterface::class);
        $item
            ->expects($this->once())
            ->method('setChildrenAttribute')
            ->with('class', 'nav navbar-nav');
        $item
            ->expects($this->once())
            ->method('setExtra')
            ->with('translation_domain', 'foo_bar_baz');

        $menuFactory = $this->createMock(FactoryInterface::class);
        $menuFactory
            ->expects($this->once())
            ->method('createItem')
            ->willReturn($item);

        $modelAdmin = new ModelAdmin('sonata.post.admin.model', 'Application\Sonata\FooBundle\Entity\Model', 'Sonata\FooBundle\Controller\ModelAdminController');
        $modelAdmin->setMenuFactory($menuFactory);
        $modelAdmin->setTranslationDomain('foo_bar_baz');

        $modelAdmin->getSideMenu('foo');
    }

    /**
     * @return array
     */
    public function provideGetSubject()
    {
        return [
            [23],
            ['azerty'],
            ['4f69bbb5f14a13347f000092'],
            ['0779ca8d-e2be-11e4-ac58-0242ac11000b'],
            [sprintf('123%smy_type', AdapterInterface::ID_SEPARATOR)], // composite keys are supported
        ];
    }

    /**
     * @dataProvider provideGetSubject
     */
    public function testGetSubjectFailed($id): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects($this->once())
            ->method('find')
            ->with('NewsBundle\Entity\Post', $id)
            ->willReturn(null); // entity not found

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        $admin->setRequest(new Request(['id' => $id]));
        $this->assertFalse($admin->hasSubject());
    }

    /**
     * @dataProvider provideGetSubject
     */
    public function testGetSubject($id): void
    {
        $model = new Post();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects($this->once())
            ->method('find')
            ->with('NewsBundle\Entity\Post', $id)
            ->willReturn($model);

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        $admin->setRequest(new Request(['id' => $id]));
        $this->assertTrue($admin->hasSubject());
        $this->assertSame($model, $admin->getSubject());
        $this->assertSame($model, $admin->getSubject()); // model manager must be used only once
    }

    public function testGetSubjectWithParentDescription(): void
    {
        $adminId = 1;

        $comment = new Comment();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->method('find')
            ->with('NewsBundle\Entity\Comment', $adminId)
            ->willReturn($comment);

        $request = new Request(['id' => $adminId]);

        $postAdmin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $postAdmin->setRequest($request);

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setRequest($request);
        $commentAdmin->setModelManager($modelManager);

        $this->assertTrue($commentAdmin->hasSubject());
        $this->assertSame($comment, $commentAdmin->getSubject());

        $commentAdmin->setSubject(null);
        $commentAdmin->setParentFieldDescription(new FieldDescription());

        $this->assertFalse($commentAdmin->hasSubject());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::configureActionButtons
     */
    public function testGetActionButtonsList(): void
    {
        $expected = [
            'create' => [
                'template' => 'Foo.html.twig',
            ],
        ];

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $templateRegistry = $this->prophesize(MutableTemplateRegistryInterface::class);
        $templateRegistry->getTemplate('button_create')->willReturn('Foo.html.twig');

        $admin->setTemplateRegistry($templateRegistry->reveal());

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->expects($this->once())
            ->method('isGranted')
            ->with($admin, 'CREATE', $admin)
            ->willReturn(true);
        $admin->setSecurityHandler($securityHandler);

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routeGenerator
            ->expects($this->once())
            ->method('hasAdminRoute')
            ->with($admin, 'create')
            ->willReturn(true);
        $admin->setRouteGenerator($routeGenerator);

        $this->assertSame($expected, $admin->getActionButtons('list', null));
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::configureActionButtons
     */
    public function testGetActionButtonsListCreateDisabled(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->expects($this->once())
            ->method('isGranted')
            ->with($admin, 'CREATE', $admin)
            ->willReturn(false);
        $admin->setSecurityHandler($securityHandler);

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routeGenerator
            ->expects($this->once())
            ->method('hasAdminRoute')
            ->with($admin, 'create')
            ->willReturn(true);
        $admin->setRouteGenerator($routeGenerator);

        $this->assertSame([], $admin->getActionButtons('list', null));
    }

    public function testGetActionButtonsListWithoutExtraChecks(): void
    {
        $admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getActionButtons', 'configureActionButtons'])
            ->getMockForAbstractClass();

        $admin->method('isAclEnabled')->willReturn(true);
        $admin->method('getExtensions')->willReturn([]);

        $admin->expects($this->exactly(9))->method('hasRoute')->willReturn(false);
        $admin->expects($this->never())->method('hasAccess');
        $admin->expects($this->never())->method('getShow');

        $this->assertSame([], $admin->getActionButtons('show'));
        $this->assertSame([], $admin->getActionButtons('edit'));
    }

    public function testCantAccessObjectIfNullPassed(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->assertFalse($admin->canAccessObject('list', null));
    }

    public function testCantAccessObjectIfRandomObjectPassed(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $admin->setModelManager($modelManager);

        $this->assertFalse($admin->canAccessObject('list', new \stdClass()));
    }

    public function testCanAccessObject(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->method('getNormalizedIdentifier')
            ->willReturn('identifier');
        $admin->setModelManager($modelManager);
        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $admin->setSecurityHandler($securityHandler);

        $this->assertTrue($admin->canAccessObject('list', new \stdClass()));
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::configureBatchActions
     */
    public function testGetBatchActions(): void
    {
        $expected = [
            'delete' => [
                'label' => 'action_delete',
                'translation_domain' => 'SonataAdminBundle',
                'ask_confirmation' => true, // by default always true
            ],
            'foo' => [
                'label' => 'action_foo',
                'translation_domain' => 'SonataAdminBundle',
            ],
            'bar' => [
                'label' => 'batch.label_bar',
                'translation_domain' => 'SonataAdminBundle',
            ],
            'baz' => [
                'label' => 'action_baz',
                'translation_domain' => 'AcmeAdminBundle',
            ],
        ];

        $pathInfo = new PathInfoBuilder($this->createMock(AuditManagerInterface::class));

        $labelTranslatorStrategy = $this->createMock(LabelTranslatorStrategyInterface::class);
        $labelTranslatorStrategy
            ->method('getLabel')
            ->willReturnCallback(static function (string $label, string $context = '', string $type = ''): string {
                return sprintf('%s.%s_%s', $context, $type, $label);
            });

        $admin = new PostAdmin('sonata.post.admin.model', 'Application\Sonata\FooBundle\Entity\Model', 'Sonata\FooBundle\Controller\ModelAdminController');
        $admin->setRouteBuilder($pathInfo);
        $admin->setTranslationDomain('SonataAdminBundle');
        $admin->setLabelTranslatorStrategy($labelTranslatorStrategy);

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routeGenerator
            ->expects($this->once())
            ->method('hasAdminRoute')
            ->with($admin, 'delete')
            ->willReturn(true);
        $admin->setRouteGenerator($routeGenerator);

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static function (AdminInterface $adminIn, string $attributes, $object = null) use ($admin): bool {
                return $admin === $adminIn && 'DELETE' === $attributes;
            });
        $admin->setSecurityHandler($securityHandler);

        $this->assertSame($expected, $admin->getBatchActions());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::showMosaicButton
     */
    public function testShowMosaicButton(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $listModes = $admin->getListModes();

        $admin->showMosaicButton(true);

        $this->assertSame($listModes, $admin->getListModes());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::showMosaicButton
     */
    public function testShowMosaicButtonHideMosaic(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $listModes = $admin->getListModes();
        $expected['list'] = $listModes['list'];

        $admin->showMosaicButton(false);

        $this->assertSame($expected, $admin->getListModes());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getDashboardActions
     * @dataProvider provideGetBaseRouteName
     */
    public function testDefaultDashboardActionsArePresent(string $objFqn, string $expected): void
    {
        $pathInfo = new PathInfoBuilder($this->createMock(AuditManagerInterface::class));

        $routeGenerator = new DefaultRouteGenerator(
            $this->createMock(RouterInterface::class),
            new RoutesCache($this->cacheTempFolder, true)
        );

        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setRouteBuilder($pathInfo);
        $admin->setRouteGenerator($routeGenerator);
        $admin->initialize();

        $templateRegistry = $this->prophesize(MutableTemplateRegistryInterface::class);
        $templateRegistry->getTemplate('action_create')->willReturn('Foo.html.twig');

        $admin->setTemplateRegistry($templateRegistry->reveal());

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static function (AdminInterface $adminIn, string $attributes, $object = null) use ($admin): bool {
                return $admin === $adminIn && ('CREATE' === $attributes || 'LIST' === $attributes);
            });

        $admin->setSecurityHandler($securityHandler);

        $this->assertArrayHasKey('list', $admin->getDashboardActions());
        $this->assertArrayHasKey('create', $admin->getDashboardActions());
    }

    /**
     * NEXT_MAJOR: Remove the assertion about isDefaultFilter method and the legacy group.
     *
     * @group legacy
     *
     * @expectedDeprecation Method "Sonata\AdminBundle\Admin\AbstractAdmin::isDefaultFilter" is deprecated since sonata-project/admin-bundle 3.76.
     */
    public function testDefaultFilters(): void
    {
        $admin = new FilteredAdmin('sonata.post.admin.model', 'Application\Sonata\FooBundle\Entity\Model', 'Sonata\FooBundle\Controller\ModelAdminController');

        $subjectId = uniqid();

        $request = $this->createMock(Request::class);
        $query = $this->createMock(ParameterBag::class);
        $query
            ->method('get')
            ->with($this->equalTo('filter'))
            ->willReturn([
                'a' => [
                    'value' => 'b',
                ],
                'foo' => [
                    'type' => '1',
                    'value' => 'bar',
                ],
                'baz' => [
                    'type' => '5',
                    'value' => 'test',
                ],
            ]);
        $request->query = $query;

        $request
            ->method('get')
            ->willReturn($subjectId);

        $admin->setRequest($request);

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->method('getDefaultSortValues')
            ->willReturn([]);

        $admin->setModelManager($modelManager);

        $this->assertSame([
            '_page' => 1,
            '_per_page' => 32,
            'foo' => [
                'type' => '1',
                'value' => 'bar',
            ],
            'baz' => [
                'type' => '5',
                'value' => 'test',
            ],
            'a' => [
                'value' => 'b',
            ],
        ], $admin->getFilterParameters());

        $this->assertTrue($admin->isDefaultFilter('foo'));
        $this->assertFalse($admin->isDefaultFilter('bar'));
        $this->assertFalse($admin->isDefaultFilter('a'));
    }

    /**
     * @group legacy
     */
    public function testDefaultBreadcrumbsBuilder(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('getParameter')
            ->with('sonata.admin.configuration.breadcrumbs')
            ->willReturn([]);

        $pool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pool->expects($this->once())
            ->method('getContainer')
            ->willReturn($container);

        $admin = $this->getMockForAbstractClass(AbstractAdmin::class, [
            'admin.my_code', 'My\Class', 'MyBundle\ClassAdminController',
        ], '', true, true, true, ['getConfigurationPool']);
        $admin->expects($this->once())
            ->method('getConfigurationPool')
            ->willReturn($pool);

        $this->assertInstanceOf(BreadcrumbsBuilder::class, $admin->getBreadcrumbsBuilder());
    }

    /**
     * @group legacy
     */
    public function testBreadcrumbsBuilderSetter(): void
    {
        $admin = $this->getMockForAbstractClass(AbstractAdmin::class, [
            'admin.my_code', 'My\Class', 'MyBundle\ClassAdminController',
        ]);
        $this->assertSame($admin, $admin->setBreadcrumbsBuilder($builder = $this->createMock(
            BreadcrumbsBuilderInterface::class
        )));
        $this->assertSame($builder, $admin->getBreadcrumbsBuilder());
    }

    /**
     * @group legacy
     */
    public function testGetBreadcrumbs(): void
    {
        $admin = $this->getMockForAbstractClass(AbstractAdmin::class, [
            'admin.my_code', 'My\Class', 'MyBundle\ClassAdminController',
        ]);
        $builder = $this->prophesize(BreadcrumbsBuilderInterface::class);
        $action = 'myaction';
        $builder->getBreadcrumbs($admin, $action)->shouldBeCalled();
        $admin->setBreadcrumbsBuilder($builder->reveal())->getBreadcrumbs($action);
    }

    /**
     * @group legacy
     */
    public function testBuildBreadcrumbs(): void
    {
        $admin = $this->getMockForAbstractClass(AbstractAdmin::class, [
            'admin.my_code', 'My\Class', 'MyBundle\ClassAdminController',
        ]);
        $builder = $this->prophesize(BreadcrumbsBuilderInterface::class);
        $action = 'myaction';
        $menu = $this->createMock(ItemInterface::class);
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
    public function testCreateQueryLegacyCallWorks(): void
    {
        $admin = $this->getMockForAbstractClass(AbstractAdmin::class, [
            'admin.my_code', 'My\Class', 'MyBundle\ClassAdminController',
        ]);
        $query = $this->createMock(ProxyQueryInterface::class);
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects($this->once())
            ->method('createQuery')
            ->with('My\Class')
            ->willReturn($query);

        $admin->setModelManager($modelManager);
        $this->assertSame($query, $admin->createQuery('list'));
    }

    public function testGetDataSourceIterator(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->method('buildPager');

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->method('getExportFields')->willReturn([
            'field',
            'foo',
            'bar',
        ]);
        $modelManager->expects($this->once())->method('getDataSourceIterator')
            ->with($this->equalTo($datagrid), $this->equalTo([
                'Feld' => 'field',
                1 => 'foo',
                2 => 'bar',
            ]));

        $admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDatagrid', 'getTranslationLabel', 'trans'])
            ->getMockForAbstractClass();
        $admin->method('getDatagrid')->willReturn($datagrid);
        $admin->setModelManager($modelManager);

        $admin
            ->method('getTranslationLabel')
            ->willReturnCallback(static function (string $label, string $context = '', string $type = ''): string {
                return sprintf('%s.%s_%s', $context, $type, $label);
            });
        $admin
            ->method('trans')
            ->willReturnCallback(static function (string $label): string {
                if ('export.label_field' === $label) {
                    return 'Feld';
                }

                return $label;
            });

        $admin->getDataSourceIterator();
    }

    public function testCircularChildAdmin(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Circular reference detected! The child admin `sonata.post.admin.post` is already in the parent tree of the `sonata.post.admin.comment` admin.'
        );

        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $postAdmin->addChild($commentAdmin, 'post');
        $commentAdmin->addChild($postAdmin, 'comment');
    }

    public function testCircularChildAdminTripleLevel(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Circular reference detected! The child admin `sonata.post.admin.post` is already in the parent tree of the `sonata.post.admin.comment_vote` admin.'
        );

        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            'Application\Sonata\NewsBundle\Entity\CommentVote',
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );
        $postAdmin->addChild($commentAdmin, 'post');
        $commentAdmin->addChild($commentVoteAdmin, 'comment');
        $commentVoteAdmin->addChild($postAdmin, 'post');
    }

    public function testCircularChildAdminWithItself(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Circular reference detected! The child admin `sonata.post.admin.post` is already in the parent tree of the `sonata.post.admin.post` admin.'
        );

        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $postAdmin->addChild($postAdmin);
    }

    public function testGetRootAncestor(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            'Application\Sonata\NewsBundle\Entity\CommentVote',
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );

        $this->assertSame($postAdmin, $postAdmin->getRootAncestor());
        $this->assertSame($commentAdmin, $commentAdmin->getRootAncestor());
        $this->assertSame($commentVoteAdmin, $commentVoteAdmin->getRootAncestor());

        $postAdmin->addChild($commentAdmin, 'post');

        $this->assertSame($postAdmin, $postAdmin->getRootAncestor());
        $this->assertSame($postAdmin, $commentAdmin->getRootAncestor());
        $this->assertSame($commentVoteAdmin, $commentVoteAdmin->getRootAncestor());

        $commentAdmin->addChild($commentVoteAdmin, 'comment');

        $this->assertSame($postAdmin, $postAdmin->getRootAncestor());
        $this->assertSame($postAdmin, $commentAdmin->getRootAncestor());
        $this->assertSame($postAdmin, $commentVoteAdmin->getRootAncestor());
    }

    public function testGetChildDepth(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            'Application\Sonata\NewsBundle\Entity\CommentVote',
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );

        $this->assertSame(0, $postAdmin->getChildDepth());
        $this->assertSame(0, $commentAdmin->getChildDepth());
        $this->assertSame(0, $commentVoteAdmin->getChildDepth());

        $postAdmin->addChild($commentAdmin, 'post');

        $this->assertSame(0, $postAdmin->getChildDepth());
        $this->assertSame(1, $commentAdmin->getChildDepth());
        $this->assertSame(0, $commentVoteAdmin->getChildDepth());

        $commentAdmin->addChild($commentVoteAdmin, 'comment');

        $this->assertSame(0, $postAdmin->getChildDepth());
        $this->assertSame(1, $commentAdmin->getChildDepth());
        $this->assertSame(2, $commentVoteAdmin->getChildDepth());
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation The Sonata\AdminBundle\Admin\AbstractAdmin::getCurrentChild() method is deprecated since version 3.65 and will be removed in 4.0. Use Sonata\AdminBundle\Admin\AbstractAdmin::isCurrentChild() instead.
     */
    public function testGetCurrentLeafChildAdmin(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            'Application\Sonata\NewsBundle\Entity\CommentVote',
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );

        $postAdmin->addChild($commentAdmin, 'post');
        $commentAdmin->addChild($commentVoteAdmin, 'comment');

        $this->assertNull($postAdmin->getCurrentLeafChildAdmin());
        $this->assertNull($commentAdmin->getCurrentLeafChildAdmin());
        $this->assertNull($commentVoteAdmin->getCurrentLeafChildAdmin());

        $commentAdmin->setCurrentChild(true);

        $this->assertSame($commentAdmin, $postAdmin->getCurrentLeafChildAdmin());
        $this->assertNull($commentAdmin->getCurrentLeafChildAdmin());
        $this->assertNull($commentVoteAdmin->getCurrentLeafChildAdmin());

        $commentVoteAdmin->setCurrentChild(true);

        $this->assertSame($commentVoteAdmin, $postAdmin->getCurrentLeafChildAdmin());
        $this->assertSame($commentVoteAdmin, $commentAdmin->getCurrentLeafChildAdmin());
        $this->assertNull($commentVoteAdmin->getCurrentLeafChildAdmin());
    }

    public function testAdminWithoutControllerName(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', null);

        $this->assertNull($admin->getBaseControllerName());
    }

    public function testAdminAvoidInifiniteLoop(): void
    {
        $this->expectNotToPerformAssertions();

        $formFactory = new FormFactory(new FormRegistry([], new ResolvedFormTypeFactory()));

        $admin = new AvoidInfiniteLoopAdmin('code', \stdClass::class, null);
        $admin->setSubject(new \stdClass());

        $admin->setFormContractor(new FormContractor($formFactory));

        $admin->setShowBuilder(new ShowBuilder());

        $admin->setListBuilder(new ListBuilder());

        $pager = $this->createStub(PagerInterface::class);
        $admin->setDatagridBuilder(new DatagridBuilder($formFactory, $pager));

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('getMetadataFor')->willReturn($this->createStub(MemberMetadata::class));
        $admin->setValidator($validator);

        $routeGenerator = $this->createStub(RouteGeneratorInterface::class);
        $admin->setRouteGenerator($routeGenerator);

        $admin->getForm();
        $admin->getShow();
        $admin->getList();
        $admin->getDatagrid();
    }

    /**
     * NEXT_MAJOR: Remove this test and its data provider.
     *
     * @group legacy
     *
     * @dataProvider getDeprecatedAbstractAdminConstructorArgs
     *
     * @expectedDeprecation Passing other type than string%S as argument %d for method Sonata\AdminBundle\Admin\AbstractAdmin::__construct() is deprecated since sonata-project/admin-bundle 3.65. It will accept only string%S in version 4.0.
     */
    public function testDeprecatedAbstractAdminConstructorArgs($code, $class, $baseControllerName): void
    {
        new PostAdmin($code, $class, $baseControllerName);
    }

    public function getDeprecatedAbstractAdminConstructorArgs(): iterable
    {
        yield from [
            ['sonata.post.admin.post', null, null],
            [null, null, null],
            ['sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', false],
            ['sonata.post.admin.post', false, false],
            [false, false, false],
            [true, true, true],
            [new \stdClass(), new \stdClass(), new \stdClass()],
            [0, 0, 0],
            [1, 1, 1],
        ];
    }
}
