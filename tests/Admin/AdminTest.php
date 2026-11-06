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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
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
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use Sonata\Doctrine\Adapter\AdapterInterface;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Mapping\MemberMetadata;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminTest extends TestCase
{
    use ExpectDeprecationTrait;

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
        static::assertInstanceOf(AbstractAdmin::class, $admin);
        static::assertSame($class, $admin->getClass());
        static::assertSame($baseControllerName, $admin->getBaseControllerName());
    }

    public function testGetClass(): void
    {
        $class = Post::class;
        $baseControllerName = 'Sonata\NewsBundle\Controller\PostAdminController';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);

        $admin->setModelManager($this->getMockForAbstractClass(ModelManagerInterface::class));

        $admin->setSubject(new BlogPost());
        static::assertSame(BlogPost::class, $admin->getClass());

        $admin->setSubClasses(['foo']);
        static::assertSame(BlogPost::class, $admin->getClass());

        $admin->setSubject(null);
        $admin->setSubClasses([]);
        static::assertSame($class, $admin->getClass());

        $admin->setSubClasses(['foo' => 'bar']);
        $admin->setRequest(new Request(['subclass' => 'foo']));
        static::assertSame('bar', $admin->getClass());
    }

    public function testGetClassException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Feature not implemented: an embedded admin cannot have subclass');

        $class = 'Application\Sonata\NewsBundle\Entity\Post';
        $baseControllerName = 'Sonata\NewsBundle\Controller\PostAdminController';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $admin->setParentFieldDescription(new FieldDescription('name'));
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
        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler->method('isGranted')->willReturnMap([
            [$admin, 'CUSTOM_ROLE', $admin, true],
            [$admin, 'EXTRA_CUSTOM_ROLE', $admin, false],
        ]);
        $customExtension = $this->createStub(AbstractAdminExtension::class);
        $customExtension->method('getAccessMapping')->with($admin)->willReturn(
            ['custom_action' => ['CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE']]
        );
        $admin->addExtension($customExtension);
        $admin->setSecurityHandler($securityHandler);
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

        static::assertFalse($admin->hasAccess('made-up'));
    }

    public function testHasAccess(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler->method('isGranted')->willReturnMap([
            [$admin, 'CUSTOM_ROLE', $admin, true],
            [$admin, 'EXTRA_CUSTOM_ROLE', $admin, false],
        ]);
        $customExtension = $this->createStub(AbstractAdminExtension::class);
        $customExtension->method('getAccessMapping')->with($admin)->willReturn(
            ['custom_action' => ['CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE']]
        );
        $admin->addExtension($customExtension);
        $admin->setSecurityHandler($securityHandler);

        static::assertFalse($admin->hasAccess('custom_action'));
    }

    public function testHasAccessAllowsAccess(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler->method('isGranted')->willReturnMap([
            [$admin, 'CUSTOM_ROLE', $admin, true],
            [$admin, 'EXTRA_CUSTOM_ROLE', $admin, true],
        ]);
        $customExtension = $this->createStub(AbstractAdminExtension::class);
        $customExtension->method('getAccessMapping')->with($admin)->willReturn(
            ['custom_action' => ['CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE']]
        );
        $admin->addExtension($customExtension);
        $admin->setSecurityHandler($securityHandler);

        static::assertTrue($admin->hasAccess('custom_action'));
    }

    public function testHasAccessAllowsAccessEditAction(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            'Application\Sonata\NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler->method('isGranted')->with($admin, 'EDIT_ROLE', $admin)->willReturn(true);
        $customExtension = $this->createStub(AbstractAdminExtension::class);
        $customExtension->method('getAccessMapping')->with($admin)->willReturn(
            ['edit_action' => ['EDIT_ROLE']]
        );
        $admin->addExtension($customExtension);
        $admin->setSecurityHandler($securityHandler);

        static::assertTrue($admin->hasAccess('edit_action'));
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
        static::assertFalse($postAdmin->hasChildren());
        static::assertFalse($postAdmin->hasChild('comment'));

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $postAdmin->addChild($commentAdmin, 'post');

        static::assertTrue($postAdmin->hasChildren());
        static::assertTrue($postAdmin->hasChild('sonata.post.admin.comment'));

        static::assertSame('sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getCode());
        static::assertSame('sonata.post.admin.post|sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getBaseCodeRoute());
        static::assertSame($postAdmin, $postAdmin->getChild('sonata.post.admin.comment')->getParent());
        static::assertSame('post', $commentAdmin->getParentAssociationMapping());

        static::assertFalse($postAdmin->isChild());
        static::assertTrue($commentAdmin->isChild());

        static::assertSame(['sonata.post.admin.comment' => $commentAdmin], $postAdmin->getChildren());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getParent
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::setParent
     */
    public function testParent(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        static::assertFalse($commentAdmin->isChild());
        static::assertFalse($commentAdmin->hasParentFieldDescription());

        $commentAdmin->setParent($postAdmin, 'post');

        static::assertSame($postAdmin, $commentAdmin->getParent());
        static::assertSame('post', $commentAdmin->getParentAssociationMapping());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::configure
     */
    public function testConfigure(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        static::assertNotNull($admin->getUniqid());

        $admin->initialize();
        static::assertNotNull($admin->getUniqid());
        static::assertSame('Post', $admin->getClassnameLabel());

        $admin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $admin->setClassnameLabel('postcomment');

        $admin->initialize();
        static::assertSame('postcomment', $admin->getClassnameLabel());
    }

    public function testConfigureWithValidParentAssociationMapping(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $comment = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');
        $comment->addChild($admin, 'comment');

        $admin->initialize();

        static::assertSame('comment', $admin->getParentAssociationMapping());
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
        static::assertSame($expected, $admin->getBaseRoutePattern());
    }

    /**
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePatternWithChildAdmin(string $objFqn, string $expected): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setParent($postAdmin, 'post');

        static::assertSame(sprintf('%s/{id}/comment', $expected), $commentAdmin->getBaseRoutePattern());
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
        $commentAdmin->setParent($postAdmin, 'post');
        $commentVoteAdmin->setParent($commentAdmin, 'comment');

        static::assertSame(sprintf('%s/{id}/comment/{childId}/commentvote', $expected), $commentVoteAdmin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithSpecifedPattern(): void
    {
        $postAdmin = new PostWithCustomRouteAdmin('sonata.post.admin.post_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostWithCustomRouteAdminController');

        static::assertSame('/post-custom', $postAdmin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithChildAdminAndWithSpecifedPattern(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentWithCustomRouteAdmin('sonata.post.admin.comment_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentWithCustomRouteAdminController');
        $commentAdmin->setParent($postAdmin, 'post');

        static::assertSame('/sonata/news/post/{id}/comment-custom', $commentAdmin->getBaseRoutePattern());
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

        static::assertSame($expected, $admin->getBaseRouteName());
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
        $pool = new Pool($container);

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

        static::assertSame(sprintf('%s_comment', $expected), $commentAdmin->getBaseRouteName());

        static::assertTrue($postAdmin->hasRoute('show'));
        static::assertTrue($postAdmin->hasRoute('sonata.post.admin.post.show'));
        static::assertTrue($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment.show'));
        static::assertTrue($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment|sonata.post.admin.comment_vote.show'));
        static::assertTrue($postAdmin->hasRoute('sonata.post.admin.comment.list'));
        static::assertTrue($postAdmin->hasRoute('sonata.post.admin.comment|sonata.post.admin.comment_vote.list'));
        static::assertFalse($postAdmin->hasRoute('sonata.post.admin.post|sonata.post.admin.comment.edit'));
        static::assertFalse($commentAdmin->hasRoute('edit'));
        static::assertSame('post', $commentAdmin->getParentAssociationMapping());

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

        static::assertTrue($postAdmin->isCurrentRoute('list'));
        static::assertFalse($postAdmin->isCurrentRoute('create'));
        static::assertFalse($commentAdmin->isCurrentRoute('list'));
        static::assertFalse($commentVoteAdmin->isCurrentRoute('list'));
        static::assertTrue($commentAdmin->isCurrentRoute('list', 'sonata.post.admin.post'));
        static::assertFalse($commentAdmin->isCurrentRoute('edit', 'sonata.post.admin.post'));
        static::assertTrue($commentVoteAdmin->isCurrentRoute('list', 'sonata.post.admin.post'));
        static::assertFalse($commentVoteAdmin->isCurrentRoute('edit', 'sonata.post.admin.post'));
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

        static::assertSame('post_custom', $postAdmin->getBaseRouteName());
    }

    public function testGetBaseRouteNameWithChildAdminAndWithSpecifiedName(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentWithCustomRouteAdmin('sonata.post.admin.comment_with_custom_route', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentWithCustomRouteAdminController');
        $commentAdmin->setParent($postAdmin, 'post');

        static::assertSame('admin_sonata_news_post_comment_custom', $commentAdmin->getBaseRouteName());
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
        $commentAdmin->setParent($postAdmin, 'post');
        $commentVoteAdmin->setParent($commentAdmin, 'comment');

        static::assertSame('admin_sonata_news_post_comment_custom_commentvote', $commentVoteAdmin->getBaseRouteName());
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

        static::assertSame($uniqid, $admin->getUniqid());
    }

    public function testToString(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $s = new \stdClass();

        static::assertNotEmpty($admin->toString($s));

        $s = new FooToString();
        static::assertSame('salut', $admin->toString($s));
    }

    public function testToStringNull(): void
    {
        if (\PHP_VERSION_ID >= 80000) {
            static::markTestSkipped('PHP 8.0 does not allow __toString() method to return null');
        }

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        // To string method is implemented, but returns null
        $s = new FooToStringNull();
        static::assertNotEmpty($admin->toString($s));
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
        static::assertSame('', $admin->toString(false));
    }

    public function testIsAclEnabled(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $postAdmin->setSecurityHandler($this->createMock(SecurityHandlerInterface::class));
        static::assertFalse($postAdmin->isAclEnabled());

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setSecurityHandler($this->createMock(AclSecurityHandlerInterface::class));
        static::assertTrue($commentAdmin->isAclEnabled());
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
        static::assertFalse($admin->hasSubClass('test'));
        static::assertFalse($admin->hasActiveSubClass());
        static::assertCount(0, $admin->getSubClasses());
        static::assertNull($admin->getActiveSubClass());
        static::assertNull($admin->getActiveSubclassCode());
        static::assertSame(Post::class, $admin->getClass());

        // Just for the record, if there is no inheritance set, the getSubject is not used
        // the getSubject can also lead to some issue
        $admin->setSubject(new BlogPost());
        static::assertSame(BlogPost::class, $admin->getClass());

        $admin->setSubClasses([
            'extended1' => 'NewsBundle\Entity\PostExtended1',
            'extended2' => 'NewsBundle\Entity\PostExtended2',
        ]);
        static::assertFalse($admin->hasSubClass('test'));
        static::assertTrue($admin->hasSubClass('extended1'));
        static::assertFalse($admin->hasActiveSubClass());
        static::assertCount(2, $admin->getSubClasses());
        // NEXT_MAJOR: remove the following 2 `assertNull()` assertions
        static::assertNull($admin->getActiveSubClass());
        static::assertNull($admin->getActiveSubclassCode());
        static::assertSame(
            BlogPost::class,
            $admin->getClass(),
            'When there is no subclass in the query the class parameter should be returned'
        );

        $request = new Request(['subclass' => 'extended1']);
        $admin->setRequest($request);
        static::assertFalse($admin->hasSubClass('test'));
        static::assertTrue($admin->hasSubClass('extended1'));
        static::assertTrue($admin->hasActiveSubClass());
        static::assertCount(2, $admin->getSubClasses());
        static::assertSame(
            'NewsBundle\Entity\PostExtended1',
            $admin->getActiveSubClass(),
            'It should return the curently active sub class.'
        );
        static::assertSame('extended1', $admin->getActiveSubclassCode());
        static::assertSame(
            'NewsBundle\Entity\PostExtended1',
            $admin->getClass(),
            'getClass() should return the name of the sub class when passed through a request query parameter.'
        );

        $request->query->set('subclass', 'inject');

        static::assertNull($admin->getActiveSubclassCode());
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

        static::assertTrue($admin->hasActiveSubClass());

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
        static::assertTrue($admin->hasActiveSubClass());
    }

    /**
     * @group legacy
     * @expectedDeprecation Method "Sonata\AdminBundle\Admin\AbstractAdmin::addSubClass" is deprecated since sonata-project/admin-bundle 3.30 and will be removed in 4.0.
     *
     * @doesNotPerformAssertions
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
            static::assertSame(0, $perPage % 4);
        }

        $admin->setPerPageOptions([500, 1000]);
        static::assertSame([500, 1000], $admin->getPerPageOptions());
    }

    public function testGetLabelTranslatorStrategy(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $labelTranslatorStrategy = $this->createMock(LabelTranslatorStrategyInterface::class);
        $admin->setLabelTranslatorStrategy($labelTranslatorStrategy);
        static::assertSame($labelTranslatorStrategy, $admin->getLabelTranslatorStrategy());
    }

    public function testGetRouteBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $routeBuilder = $this->createMock(RouteBuilderInterface::class);
        $admin->setRouteBuilder($routeBuilder);
        static::assertSame($routeBuilder, $admin->getRouteBuilder());
    }

    public function testGetMenuFactory(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $menuFactory = $this->createMock(FactoryInterface::class);
        $admin->setMenuFactory($menuFactory);
        static::assertSame($menuFactory, $admin->getMenuFactory());
    }

    public function testGetExtensions(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame([], $admin->getExtensions());

        $adminExtension1 = $this->createMock(AdminExtensionInterface::class);
        $adminExtension2 = $this->createMock(AdminExtensionInterface::class);

        $admin->addExtension($adminExtension1);
        $admin->addExtension($adminExtension2);
        static::assertSame([$adminExtension1, $adminExtension2], $admin->getExtensions());

        $admin->removeExtension($adminExtension2);
        static::assertSame([$adminExtension1], $admin->getExtensions());
    }

    public function testRemovingNonExistingExtensions(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame([], $admin->getExtensions());

        $adminExtension1 = $this->createMock(AdminExtensionInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $admin->removeExtension($adminExtension1);
    }

    public function testGetFilterTheme(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame([], $admin->getFilterTheme());

        $admin->setFilterTheme(['FooTheme']);
        static::assertSame(['FooTheme'], $admin->getFilterTheme());
    }

    public function testGetFormTheme(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame([], $admin->getFormTheme());

        $admin->setFormTheme(['FooTheme']);

        static::assertSame(['FooTheme'], $admin->getFormTheme());
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @group legacy
     */
    public function testGetValidator(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $validator = $this->getMockForAbstractClass(ValidatorInterface::class);

        $admin->setValidator($validator);

        $this->expectDeprecation('The Sonata\AdminBundle\DependencyInjection\Admin\AbstractTaggedAdmin::getValidator method is deprecated since version 3.83 and will be removed in 4.0.');

        static::assertSame($validator, $admin->getValidator());
    }

    public function testGetSecurityHandler(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $admin->setSecurityHandler($securityHandler);
        static::assertSame($securityHandler, $admin->getSecurityHandler());
    }

    public function testGetSecurityInformation(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame([], $admin->getSecurityInformation());

        $securityInformation = [
            'GUEST' => ['VIEW', 'LIST'],
            'STAFF' => ['EDIT', 'LIST', 'CREATE'],
        ];

        $admin->setSecurityInformation($securityInformation);
        static::assertSame($securityInformation, $admin->getSecurityInformation());
    }

    public function testGetManagerType(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $admin->setManagerType('foo_orm');
        static::assertSame('foo_orm', $admin->getManagerType());
    }

    public function testGetModelManager(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $admin->setModelManager($modelManager);
        static::assertSame($modelManager, $admin->getModelManager());
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @group legacy
     */
    public function testGetBaseCodeRoute(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame('', $admin->getBaseCodeRoute());

        $admin->setBaseCodeRoute('foo');
        static::assertSame('foo', $admin->getBaseCodeRoute());
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

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);

        $admin->setRouteGenerator($routeGenerator);
        static::assertSame($routeGenerator, $admin->getRouteGenerator());
    }

    public function testGetConfigurationPool(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $pool = new Pool(new Container());

        $admin->setConfigurationPool($pool);
        static::assertSame($pool, $admin->getConfigurationPool());
    }

    public function testGetShowBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $showBuilder = $this->createMock(ShowBuilderInterface::class);

        $admin->setShowBuilder($showBuilder);
        static::assertSame($showBuilder, $admin->getShowBuilder());
    }

    public function testGetListBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $listBuilder = $this->createMock(ListBuilderInterface::class);

        $admin->setListBuilder($listBuilder);
        static::assertSame($listBuilder, $admin->getListBuilder());
    }

    public function testGetDatagridBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        $admin->setDatagridBuilder($datagridBuilder);
        static::assertSame($datagridBuilder, $admin->getDatagridBuilder());
    }

    public function testGetFormContractor(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $formContractor = $this->createMock(FormContractorInterface::class);

        $admin->setFormContractor($formContractor);
        static::assertSame($formContractor, $admin->getFormContractor());
    }

    public function testGetRequest(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertFalse($admin->hasRequest());

        $request = new Request();

        $admin->setRequest($request);
        static::assertSame($request, $admin->getRequest());
        static::assertTrue($admin->hasRequest());
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

        static::assertSame('messages', $admin->getTranslationDomain());

        $admin->setTranslationDomain('foo');
        static::assertSame('foo', $admin->getTranslationDomain());
    }

    public function testGetTranslator(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $translator = $this->createMock(TranslatorInterface::class);

        $admin->setTranslator($translator);
        static::assertSame($translator, $admin->getTranslator());
    }

    public function testGetShowGroups(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        static::assertFalse($admin->getShowGroups('sonata_deprecation_mute'));

        $groups = ['foo', 'bar', 'baz'];

        $admin->setShowGroups($groups);
        static::assertSame($groups, $admin->getShowGroups());
    }

    public function testGetFormGroups(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        static::assertFalse($admin->getFormGroups('sonata_deprecation_mute'));

        $groups = ['foo', 'bar', 'baz'];

        $admin->setFormGroups($groups);
        static::assertSame($groups, $admin->getFormGroups());
    }

    public function testGetMaxPageLinks(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame(25, $admin->getMaxPageLinks());

        $admin->setMaxPageLinks(14);
        static::assertSame(14, $admin->getMaxPageLinks());
    }

    /**
     * @group legacy
     */
    public function testGetMaxPerPage(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame(32, $admin->getMaxPerPage());

        $admin->setMaxPerPage(94);
        static::assertSame(94, $admin->getMaxPerPage());
    }

    public function testGetLabel(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertNull($admin->getLabel());

        $admin->setLabel('FooLabel');
        static::assertSame('FooLabel', $admin->getLabel());
    }

    public function testGetBaseController(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame('Sonata\NewsBundle\Controller\PostAdminController', $admin->getBaseControllerName());

        $admin->setBaseControllerName('Sonata\NewsBundle\Controller\FooAdminController');
        static::assertSame('Sonata\NewsBundle\Controller\FooAdminController', $admin->getBaseControllerName());
    }

    public function testGetTemplates(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $templates = [
            'list' => '@FooAdmin/CRUD/list.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $templateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $templateRegistry->expects(static::once())->method('getTemplates')->willReturn($templates);

        $admin->setTemplateRegistry($templateRegistry);

        static::assertSame($templates, $admin->getTemplates());
    }

    public function testGetTemplate1(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $templateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $templateRegistry->expects(static::exactly(2))->method('getTemplate')->willReturnMap([
            ['edit', '@FooAdmin/CRUD/edit.html.twig'],
            ['show', '@FooAdmin/CRUD/show.html.twig'],
        ]);

        $admin->setTemplateRegistry($templateRegistry);

        static::assertSame('@FooAdmin/CRUD/edit.html.twig', $admin->getTemplate('edit'));
        static::assertSame('@FooAdmin/CRUD/show.html.twig', $admin->getTemplate('show'));
    }

    public function testGetIdParameter(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            'NewsBundle\Entity\Post',
            'Sonata\NewsBundle\Controller\PostAdminController'
        );

        static::assertSame('id', $postAdmin->getIdParameter());
        static::assertFalse($postAdmin->isChild());

        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentAdmin->setParent($postAdmin, 'post');

        static::assertTrue($commentAdmin->isChild());
        static::assertSame('childId', $commentAdmin->getIdParameter());

        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            'Application\Sonata\NewsBundle\Entity\CommentVote',
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );
        $commentVoteAdmin->setParent($commentAdmin, 'comment');

        static::assertTrue($commentVoteAdmin->isChild());
        static::assertSame('childChildId', $commentVoteAdmin->getIdParameter());
    }

    public function testGetExportFormats(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame(['json', 'xml', 'csv', 'xls'], $admin->getExportFormats());
    }

    public function testGetUrlsafeIdentifier(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $model = new \stdClass();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects(static::once())
            ->method('getUrlSafeIdentifier')
            ->with(static::equalTo($model))
            ->willReturn('foo');
        $admin->setModelManager($modelManager);

        static::assertSame('foo', $admin->getUrlSafeIdentifier($model));
    }

    public function testDeterminedPerPageValue(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertFalse($admin->determinedPerPageValue('foo'));
        static::assertFalse($admin->determinedPerPageValue(123));
        static::assertTrue($admin->determinedPerPageValue(16));
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
            ->expects(static::exactly(6))
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

        static::assertTrue($admin->isGranted('FOO'));
        static::assertTrue($admin->isGranted('FOO'));
        static::assertTrue($admin->isGranted('FOO', $entity1));
        static::assertTrue($admin->isGranted('FOO', $entity1));
        static::assertFalse($admin->isGranted('BAR'));
        static::assertFalse($admin->isGranted('BAR'));
        static::assertFalse($admin->isGranted('BAR', $entity1));
        static::assertFalse($admin->isGranted('BAR', $entity1));

        $entity2 = new \stdClass();
        $entity2->id = '2';

        static::assertFalse($admin->isGranted('BAR', $entity2));
        static::assertFalse($admin->isGranted('BAR', $entity2));

        $entity3 = new \stdClass();
        $entity3->id = '3';

        static::assertFalse($admin->isGranted('BAR', $entity3));
        static::assertFalse($admin->isGranted('BAR', $entity3));
    }

    public function testSupportsPreviewMode(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertFalse($admin->supportsPreviewMode());
    }

    public function testGetPermissionsShow(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame(['LIST'], $admin->getPermissionsShow(AbstractAdmin::CONTEXT_DASHBOARD));
        static::assertSame(['LIST'], $admin->getPermissionsShow(AbstractAdmin::CONTEXT_MENU));
        static::assertSame(['LIST'], $admin->getPermissionsShow('foo'));
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

        static::assertTrue($admin->showIn(AbstractAdmin::CONTEXT_DASHBOARD));
        static::assertTrue($admin->showIn(AbstractAdmin::CONTEXT_MENU));
        static::assertTrue($admin->showIn('foo'));
    }

    public function testGetObjectIdentifier(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'Acme\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame('sonata.post.admin.post', $admin->getObjectIdentifier());
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

        $translator->expects(static::once())
            ->method('trans')
            ->with(static::equalTo('foo'), static::equalTo([]), static::equalTo('fooMessageDomain'))
            ->willReturn('fooTranslated');

        static::assertSame('fooTranslated', $admin->trans('foo'));
    }

    /**
     * @group legacy
     */
    public function testTransWithMessageDomain(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $translator = $this->createMock(TranslatorInterface::class);
        $admin->setTranslator($translator);

        $translator->expects(static::once())
            ->method('trans')
            ->with(static::equalTo('foo'), static::equalTo(['name' => 'Andrej']), static::equalTo('fooMessageDomain'))
            ->willReturn('fooTranslated');

        static::assertSame('fooTranslated', $admin->trans('foo', ['name' => 'Andrej'], 'fooMessageDomain'));
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

        $translator->expects(static::once())
            ->method('transChoice')
            ->with(static::equalTo('foo'), static::equalTo(2), static::equalTo([]), static::equalTo('fooMessageDomain'))
            ->willReturn('fooTranslated');

        static::assertSame('fooTranslated', $admin->transChoice('foo', 2));
    }

    /**
     * @group legacy
     */
    public function testTransChoiceWithMessageDomain(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $translator = $this->createMock(TranslatorInterface::class);
        $admin->setTranslator($translator);

        $translator->expects(static::once())
            ->method('transChoice')
            ->with(static::equalTo('foo'), static::equalTo(2), static::equalTo(['name' => 'Andrej']), static::equalTo('fooMessageDomain'))
            ->willReturn('fooTranslated');

        static::assertSame('fooTranslated', $admin->transChoice('foo', 2, ['name' => 'Andrej'], 'fooMessageDomain'));
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
        static::assertTrue($admin->persistFilters());
    }

    public function testGetRootCode(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame('sonata.post.admin.post', $admin->getRootCode());

        $parentAdmin = new PostAdmin('sonata.post.admin.post.parent', 'NewsBundle\Entity\PostParent', 'Sonata\NewsBundle\Controller\PostParentAdminController');
        $parentFieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $parentFieldDescription->expects(static::once())
            ->method('getAdmin')
            ->willReturn($parentAdmin);

        static::assertFalse($admin->hasParentFieldDescription());
        $admin->setParentFieldDescription($parentFieldDescription);
        static::assertSame($parentFieldDescription, $admin->getParentFieldDescription());
        static::assertSame('sonata.post.admin.post.parent', $admin->getRootCode());
    }

    public function testGetRoot(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertSame($admin, $admin->getRoot());

        $parentAdmin = new PostAdmin('sonata.post.admin.post.parent', 'NewsBundle\Entity\PostParent', 'Sonata\NewsBundle\Controller\PostParentAdminController');
        $parentFieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $parentFieldDescription->expects(static::once())
            ->method('getAdmin')
            ->willReturn($parentAdmin);

        static::assertFalse($admin->hasParentFieldDescription());
        $admin->setParentFieldDescription($parentFieldDescription);
        static::assertSame($parentFieldDescription, $admin->getParentFieldDescription());
        static::assertSame($parentAdmin, $admin->getRoot());
    }

    public function testGetExportFields(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects(static::once())
            ->method('getExportFields')
            ->with(static::equalTo('NewsBundle\Entity\Post'))
            ->willReturn(['foo', 'bar']);

        $admin->setModelManager($modelManager);
        static::assertSame(['foo', 'bar'], $admin->getExportFields());
    }

    public function testGetPersistentParametersWithNoExtension(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertEmpty($admin->getPersistentParameters());
    }

    public function testGetPersistentParametersWithInvalidExtension(): void
    {
        $this->expectException(\RuntimeException::class);

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $extension = $this->createMock(AdminExtensionInterface::class);
        $extension->expects(static::once())->method('getPersistentParameters')->willReturn(null);

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
        $extension->expects(static::once())->method('getPersistentParameters')->willReturn($expected);

        $admin->addExtension($extension);

        static::assertSame($expected, $admin->getPersistentParameters());
    }

    public function testGetNewInstanceForChildAdminWithParentValue(): void
    {
        $post = new Post();

        $postAdmin = $this->getMockBuilder(PostAdmin::class)->setConstructorArgs([
            'post',
            Post::class,
            CRUDController::class,
        ])->getMock();

        // NEXT_MAJOR: Remove this 2 lines.
        $postAdmin->method('getCode')->willReturn('post');
        $postAdmin->method('getObject')->willReturn($post);

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('find')->willReturn($post);
        $postAdmin->setModelManager($modelManager);

        $postAdmin->method('getIdParameter')->willReturn('parent_id');

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder->method('getForm')->willReturn(null);

        $tagAdmin = new TagAdmin('admin.tag', Tag::class, 'MyBundle\MyController');
        $tagAdmin->setParent($postAdmin, 'post');

        // NEXT_MAJOR: Remove these 3 lines.
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('getModelInstance')->willReturn(new Tag());
        $tagAdmin->setModelManager($modelManager);

        $request = $this->createMock(Request::class);
        $request->method('get')->with('parent_id')->willReturn(42);
        $tagAdmin->setRequest($request);

        $tag = $tagAdmin->getNewInstance();

        static::assertSame($post, $tag->getPost());
    }

    public function testGetNewInstanceForChildAdminWithCollectionParentValue(): void
    {
        $post = new Post();

        $postAdmin = $this->getMockBuilder(PostAdmin::class)->setConstructorArgs([
            'post',
            Post::class,
            CRUDController::class,
        ])->getMock();

        // NEXT_MAJOR: Remove these 2 lines.
        $postAdmin->method('getCode')->willReturn('post');
        $postAdmin->method('getObject')->willReturn($post);

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->method('find')->willReturn($post);
        $postAdmin->setModelManager($modelManager);

        $postAdmin->method('getIdParameter')->willReturn('parent_id');

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder->method('getForm')->willReturn(null);

        $postCategoryAdmin = new PostCategoryAdmin('admin.post_category', PostCategory::class, 'MyBundle\MyController');
        $postCategoryAdmin->setParent($postAdmin, 'posts');

        // NEXT_MAJOR: Remove these 3 lines.
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('getModelInstance')->willReturn(new PostCategory());
        $postCategoryAdmin->setModelManager($modelManager);

        $request = $this->createMock(Request::class);
        $request->method('get')->with('parent_id')->willReturn(42);
        $postCategoryAdmin->setRequest($request);

        $postCategory = $postCategoryAdmin->getNewInstance();

        static::assertInstanceOf(Collection::class, $postCategory->getPosts());
        static::assertCount(1, $postCategory->getPosts());
        static::assertContains($post, $postCategory->getPosts());
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

        $tagAdmin = new TagAdmin('admin.tag', Tag::class, 'MyBundle\MyController');

        // NEXT_MAJOR: Remove next three lines related to model manager.
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('getModelInstance')->willReturn(new Tag());
        $tagAdmin->setModelManager($modelManager);

        $tagAdmin->setParentFieldDescription($parentField);

        $request = $this->createStub(Request::class);
        $tagAdmin->setRequest($request);

        $tag = $tagAdmin->getNewInstance();

        static::assertSame($post, $tag->getPost());
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

        $fieldDescriptionFactory = $this->createStub(FieldDescriptionFactoryInterface::class);
        $fieldDescriptionFactory
            ->method('create')
            ->willReturn(new FieldDescription('name'));
        $modelAdmin->setFieldDescriptionFactory($fieldDescriptionFactory);

        // a Admin class to test that preValidate is called
        $testAdminPreValidate = $this->createMock(AbstractAdmin::class);
        $testAdminPreValidate->expects(static::once())
                ->method('preValidate')
                ->with(static::identicalTo($object));

        $event = $this->createMock(FormEvent::class);
        $event
                ->method('getData')
                ->willReturn($object);

        $formBuild = $this->createMock(FormBuilder::class);
        $formBuild->expects(static::once())
                ->method('addEventListener')
                ->with(
                    static::identicalTo(FormEvents::POST_SUBMIT),
                    static::callback(static function ($callback) use ($testAdminPreValidate, $event): bool {
                        if (\is_callable($callback)) {
                            $closure = $callback->bindTo($testAdminPreValidate);
                            $closure($event);

                            return true;
                        }

                        return false;
                    }),
                    static::greaterThan(0)
                );

        $form = $this->createMock(FormInterface::class);
        $formBuild->expects(static::once())
            ->method('getForm')
            ->willReturn($form);

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

        $fieldDescriptionFactory = $this->createStub(FieldDescriptionFactoryInterface::class);
        $fieldDescriptionFactory
            ->method('create')
            ->willReturn(new FieldDescription('name'));
        $modelAdmin->setFieldDescriptionFactory($fieldDescriptionFactory);

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
        static::assertSame($admin->getFormGroups(), [
            'foobar' => [
                'fields' => [
                    'bar' => 'bar',
                ],
            ],
        ]);

        $admin->removeFieldFromFormGroup('bar');
        static::assertSame($admin->getFormGroups(), []);
    }

    public function testGetFilterParameters(): void
    {
        $authorId = uniqid();

        $postAdmin = new PostAdmin('sonata.post.admin.post', 'Application\Sonata\NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', 'Application\Sonata\NewsBundle\Entity\Comment', 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setParent($postAdmin, 'post.author');

        $request = $this->createMock(Request::class);
        $query = $this->createMock(ParameterBag::class);
        $query
            ->method('get')
            ->willReturn([
                'filter' => [
                    DatagridInterface::PAGE => '1',
                    DatagridInterface::PER_PAGE => '32',
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

        static::assertTrue(isset($parameters['post__author']));
        static::assertSame(['value' => $authorId], $parameters['post__author']);
    }

    public function testGetFilterParametersWithoutRequest(): void
    {
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager
            ->method('getDefaultSortValues')
            ->willReturn([
                DatagridInterface::SORT_BY => 'id',
                DatagridInterface::SORT_ORDER => 'ASC',
            ]);

        $commentAdmin->setModelManager($modelManager);

        $parameters = $commentAdmin->getFilterParameters();

        static::assertArrayHasKey(DatagridInterface::SORT_BY, $parameters);
        static::assertSame('id', $parameters[DatagridInterface::SORT_BY]);
        static::assertArrayHasKey(DatagridInterface::SORT_ORDER, $parameters);
        static::assertSame('ASC', $parameters[DatagridInterface::SORT_ORDER]);
    }

    public function testGetFilterFieldDescription(): void
    {
        $modelAdmin = new ModelAdmin('sonata.post.admin.model', 'Application\Sonata\FooBundle\Entity\Model', 'Sonata\FooBundle\Controller\ModelAdminController');
        $modelAdmin->setLabelTranslatorStrategy(new NoopLabelTranslatorStrategy());

        $fooFieldDescription = new FieldDescription('foo');
        $barFieldDescription = new FieldDescription('bar');
        $bazFieldDescription = new FieldDescription('baz');

        $fieldDescriptionFactory = $this->createMock(FieldDescriptionFactoryInterface::class);
        $fieldDescriptionFactory
            ->expects(static::exactly(3))
            ->method('create')
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

        $modelAdmin->setFieldDescriptionFactory($fieldDescriptionFactory);

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager
            ->method('getDefaultSortValues')
            ->willReturn([]);

        $modelAdmin->setModelManager($modelManager);

        $pager = $this->createMock(PagerInterface::class);

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects(static::once())
            ->method('getPager')
            ->willReturn($pager);

        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);
        $datagridBuilder->expects(static::once())
            ->method('getBaseDatagrid')
            ->with(static::identicalTo($modelAdmin))
            ->willReturn($datagrid);

        $datagridBuilder->expects(static::exactly(3))
            ->method('addFilter')
            ->willReturnCallback(static function ($datagrid, $type, $fieldDescription, AdminInterface $admin): void {
                $admin->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);
                $fieldDescription->mergeOption('field_options', ['required' => false]);
            });

        $modelAdmin->setDatagridBuilder($datagridBuilder);

        static::assertSame(['foo' => $fooFieldDescription, 'bar' => $barFieldDescription, 'baz' => $bazFieldDescription], $modelAdmin->getFilterFieldDescriptions());
        static::assertFalse($modelAdmin->hasFilterFieldDescription('fooBar'));
        static::assertTrue($modelAdmin->hasFilterFieldDescription('foo'));
        static::assertTrue($modelAdmin->hasFilterFieldDescription('bar'));
        static::assertTrue($modelAdmin->hasFilterFieldDescription('baz'));
        static::assertSame($fooFieldDescription, $modelAdmin->getFilterFieldDescription('foo'));
        static::assertSame($barFieldDescription, $modelAdmin->getFilterFieldDescription('bar'));
        static::assertSame($bazFieldDescription, $modelAdmin->getFilterFieldDescription('baz'));
    }

    public function testGetSubjectNoRequest(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects(static::never())
            ->method('find');

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        static::assertFalse($admin->hasSubject());
    }

    public function testGetSideMenu(): void
    {
        $item = $this->createMock(ItemInterface::class);
        $item
            ->expects(static::once())
            ->method('setChildrenAttribute')
            ->with('class', 'nav navbar-nav');
        $item
            ->expects(static::once())
            ->method('setExtra')
            ->with('translation_domain', 'foo_bar_baz');

        $menuFactory = $this->createMock(FactoryInterface::class);
        $menuFactory
            ->expects(static::once())
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
            ->expects(static::once())
            ->method('find')
            ->with('NewsBundle\Entity\Post', $id)
            ->willReturn(null); // entity not found

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        $admin->setRequest(new Request(['id' => $id]));
        static::assertFalse($admin->hasSubject());
    }

    /**
     * @dataProvider provideGetSubject
     */
    public function testGetSubject($id): void
    {
        $model = new Post();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects(static::once())
            ->method('find')
            ->with('NewsBundle\Entity\Post', $id)
            ->willReturn($model);

        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        $admin->setRequest(new Request(['id' => $id]));
        static::assertTrue($admin->hasSubject());
        static::assertSame($model, $admin->getSubject());
        static::assertSame($model, $admin->getSubject()); // model manager must be used only once
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

        static::assertTrue($commentAdmin->hasSubject());
        static::assertSame($comment, $commentAdmin->getSubject());

        $commentAdmin->setSubject(null);
        $commentAdmin->setParentFieldDescription(new FieldDescription('name'));

        static::assertFalse($commentAdmin->hasSubject());
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

        $templateRegistry = $this->createStub(MutableTemplateRegistryInterface::class);
        $templateRegistry->method('getTemplate')->with('button_create')->willReturn('Foo.html.twig');

        $admin->setTemplateRegistry($templateRegistry);

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->expects(static::once())
            ->method('isGranted')
            ->with($admin, 'CREATE', $admin)
            ->willReturn(true);
        $admin->setSecurityHandler($securityHandler);

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routeGenerator
            ->expects(static::once())
            ->method('hasAdminRoute')
            ->with($admin, 'create')
            ->willReturn(true);
        $admin->setRouteGenerator($routeGenerator);

        static::assertSame($expected, $admin->getActionButtons('list', null));
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::configureActionButtons
     */
    public function testGetActionButtonsListCreateDisabled(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->expects(static::once())
            ->method('isGranted')
            ->with($admin, 'CREATE', $admin)
            ->willReturn(false);
        $admin->setSecurityHandler($securityHandler);

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routeGenerator
            ->expects(static::once())
            ->method('hasAdminRoute')
            ->with($admin, 'create')
            ->willReturn(true);
        $admin->setRouteGenerator($routeGenerator);

        static::assertSame([], $admin->getActionButtons('list', null));
    }

    public function testGetActionButtonsListWithoutExtraChecks(): void
    {
        $admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getActionButtons', 'configureActionButtons'])
            ->getMockForAbstractClass();

        $admin->method('isAclEnabled')->willReturn(true);
        $admin->method('getExtensions')->willReturn([]);

        $admin->expects(static::exactly(4))->method('hasRoute')->willReturn(false);
        $admin->expects(static::never())->method('hasAccess');
        $admin->expects(static::never())->method('getShow');

        static::assertSame([], $admin->getActionButtons('show'));
        static::assertSame([], $admin->getActionButtons('edit'));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testCantAccessObjectIfNullPassed(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');

        static::assertFalse($admin->canAccessObject('list', null));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testCantAccessObjectIfRandomObjectPassed(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $admin->setModelManager($modelManager);

        static::assertFalse($admin->canAccessObject('list', new \stdClass()));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
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

        static::assertTrue($admin->canAccessObject('list', new \stdClass()));
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
            ->expects(static::once())
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

        static::assertSame($expected, $admin->getBatchActions());
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @group legacy
     *
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::showMosaicButton
     */
    public function testShowMosaicButton(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $listModes = $admin->getListModes();

        $admin->showMosaicButton(true);

        static::assertSame($listModes, $admin->getListModes());
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @group legacy
     *
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::showMosaicButton
     */
    public function testShowMosaicButtonHideMosaic(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $listModes = $admin->getListModes();
        $expected['list'] = $listModes['list'];

        $admin->showMosaicButton(false);

        static::assertSame($expected, $admin->getListModes());
    }

    /**
     * @dataProvider getListModeProvider
     */
    public function testGetListMode(string $expected, ?Request $request = null): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', 'NewsBundle\Entity\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        if (null !== $request) {
            $admin->setRequest($request);
        }

        static::assertSame($expected, $admin->getListMode());
    }

    public function getListModeProvider(): iterable
    {
        yield ['list', null];

        yield ['list', new Request()];

        $request = new Request();
        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->with('sonata.post.admin.post.list_mode', 'list')
            ->willReturn('list');
        $request->setSession($session);
        yield ['list', $request];

        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->with('sonata.post.admin.post.list_mode', 'list')
            ->willReturn('some_list_mode');
        $request = new Request();
        $request->setSession($session);
        yield ['some_list_mode', $request];
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getDashboardActions
     * @dataProvider provideGetBaseRouteName
     */
    public function testDefaultDashboardActionsArePresent(string $objFqn, string $expected): void
    {
        $pathInfo = new PathInfoBuilder($this->createStub(AuditManagerInterface::class));

        $routeGenerator = new DefaultRouteGenerator(
            $this->createStub(RouterInterface::class),
            new RoutesCache($this->cacheTempFolder, true)
        );

        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setRouteBuilder($pathInfo);
        $admin->setRouteGenerator($routeGenerator);
        $admin->initialize();

        $templateRegistry = $this->createStub(MutableTemplateRegistryInterface::class);
        $templateRegistry->method('getTemplate')->with('action_create')->willReturn('Foo.html.twig');

        $admin->setTemplateRegistry($templateRegistry);

        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static function (AdminInterface $adminIn, string $attributes, $object = null) use ($admin): bool {
                return $admin === $adminIn && ('CREATE' === $attributes || 'LIST' === $attributes);
            });

        $admin->setSecurityHandler($securityHandler);

        static::assertArrayHasKey('list', $admin->getDashboardActions());
        static::assertArrayHasKey('create', $admin->getDashboardActions());
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
            ->with(static::equalTo('filter'))
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

        static::assertSame([
            DatagridInterface::PAGE => 1,
            DatagridInterface::PER_PAGE => 32,
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

        static::assertTrue($admin->isDefaultFilter('foo'));
        static::assertFalse($admin->isDefaultFilter('bar'));
        static::assertFalse($admin->isDefaultFilter('a'));
    }

    /**
     * @group legacy
     */
    public function testDefaultBreadcrumbsBuilder(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::once())
            ->method('getParameter')
            ->with('sonata.admin.configuration.breadcrumbs')
            ->willReturn([]);

        $pool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pool->expects(static::once())
            ->method('getContainer')
            ->willReturn($container);

        $admin = $this->getMockForAbstractClass(AbstractAdmin::class, [
            'admin.my_code', 'My\Class', 'MyBundle\ClassAdminController',
        ], '', true, true, true, ['getConfigurationPool']);
        $admin->expects(static::once())
            ->method('getConfigurationPool')
            ->willReturn($pool);

        static::assertInstanceOf(BreadcrumbsBuilder::class, $admin->getBreadcrumbsBuilder());
    }

    /**
     * @group legacy
     */
    public function testBreadcrumbsBuilderSetter(): void
    {
        $admin = $this->getMockForAbstractClass(AbstractAdmin::class, [
            'admin.my_code', 'My\Class', 'MyBundle\ClassAdminController',
        ]);
        static::assertSame($admin, $admin->setBreadcrumbsBuilder($builder = $this->createMock(
            BreadcrumbsBuilderInterface::class
        )));
        static::assertSame($builder, $admin->getBreadcrumbsBuilder());
    }

    /**
     * @group legacy
     */
    public function testGetBreadcrumbs(): void
    {
        $admin = $this->getMockForAbstractClass(AbstractAdmin::class, [
            'admin.my_code', 'My\Class', 'MyBundle\ClassAdminController',
        ]);
        $builder = $this->createMock(BreadcrumbsBuilderInterface::class);
        $action = 'myaction';
        $builder->expects(static::once())->method('getBreadcrumbs')->with($admin, $action);
        $admin->setBreadcrumbsBuilder($builder)->getBreadcrumbs($action);
    }

    /**
     * @group legacy
     */
    public function testBuildBreadcrumbs(): void
    {
        $admin = $this->getMockForAbstractClass(AbstractAdmin::class, [
            'admin.my_code', 'My\Class', 'MyBundle\ClassAdminController',
        ]);
        $builder = $this->createMock(BreadcrumbsBuilderInterface::class);
        $action = 'myaction';
        $menu = $this->createMock(ItemInterface::class);
        $builder->expects(static::once())->method('buildBreadcrumbs')->with($admin, $action, $menu)
            ->willReturn($menu);
        $admin->setBreadcrumbsBuilder($builder);

        /* check the called is proxied only once */
        static::assertSame($menu, $admin->buildBreadcrumbs($action, $menu));
        static::assertSame($menu, $admin->buildBreadcrumbs($action, $menu));
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
        $modelManager->expects(static::once())
            ->method('createQuery')
            ->with('My\Class')
            ->willReturn($query);

        $admin->setModelManager($modelManager);
        static::assertSame($query, $admin->createQuery('list'));
    }

    public function testGetDataSourceIterator(): void
    {
        $query = $this->createStub(ProxyQueryInterface::class);

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects(static::once())->method('buildPager');
        $datagrid->method('getQuery')->willReturn($query);

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('getExportFields')->willReturn([
            'field',
            'foo',
            'bar',
        ]);

        $dataSource = $this->createMock(DataSourceInterface::class);
        $dataSource->expects(static::once())->method('createIterator')->with($query, [
            'Feld' => 'field',
            1 => 'foo',
            2 => 'bar',
        ]);

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(static function (string $label): string {
                if ('export.label_field' === $label) {
                    return 'Feld';
                }

                return $label;
            });

        $admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDatagrid', 'getTranslationLabel'])
            ->getMockForAbstractClass();
        $admin->method('getDatagrid')->willReturn($datagrid);
        $admin->setModelManager($modelManager);
        $admin->setDataSource($dataSource);
        $admin->setTranslator($translator);

        $admin
            ->method('getTranslationLabel')
            ->willReturnCallback(static function (string $label, string $context = '', string $type = ''): string {
                return sprintf('%s.%s_%s', $context, $type, $label);
            });

        $admin->getDataSourceIterator();
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetDataSourceIteratorWithoutDataSourceSet(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects(static::once())->method('buildPager');

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('getExportFields')->willReturn([
            'field',
            'foo',
            'bar',
        ]);
        $modelManager->expects(static::once())->method('getDataSourceIterator')
            ->with(static::equalTo($datagrid), static::equalTo([
                'Feld' => 'field',
                1 => 'foo',
                2 => 'bar',
            ]));

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(static function (string $label): string {
                if ('export.label_field' === $label) {
                    return 'Feld';
                }

                return $label;
            });

        $admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDatagrid', 'getTranslationLabel'])
            ->getMockForAbstractClass();
        $admin->method('getDatagrid')->willReturn($datagrid);
        $admin->setModelManager($modelManager);
        $admin->setTranslator($translator);

        $admin
            ->method('getTranslationLabel')
            ->willReturnCallback(static function (string $label, string $context = '', string $type = ''): string {
                return sprintf('%s.%s_%s', $context, $type, $label);
            });

        $this->expectDeprecation('Using "Sonata\AdminBundle\Admin\AbstractAdmin::getDataSourceIterator()" without setting a "Sonata\AdminBundle\Exporter\DataSourceInterface" instance in the admin is deprecated since sonata-project/admin-bundle 3.79 and won\'t be possible in 4.0.');
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

        static::assertSame($postAdmin, $postAdmin->getRootAncestor());
        static::assertSame($commentAdmin, $commentAdmin->getRootAncestor());
        static::assertSame($commentVoteAdmin, $commentVoteAdmin->getRootAncestor());

        $postAdmin->addChild($commentAdmin, 'post');

        static::assertSame($postAdmin, $postAdmin->getRootAncestor());
        static::assertSame($postAdmin, $commentAdmin->getRootAncestor());
        static::assertSame($commentVoteAdmin, $commentVoteAdmin->getRootAncestor());

        $commentAdmin->addChild($commentVoteAdmin, 'comment');

        static::assertSame($postAdmin, $postAdmin->getRootAncestor());
        static::assertSame($postAdmin, $commentAdmin->getRootAncestor());
        static::assertSame($postAdmin, $commentVoteAdmin->getRootAncestor());
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

        static::assertSame(0, $postAdmin->getChildDepth());
        static::assertSame(0, $commentAdmin->getChildDepth());
        static::assertSame(0, $commentVoteAdmin->getChildDepth());

        $postAdmin->addChild($commentAdmin, 'post');

        static::assertSame(0, $postAdmin->getChildDepth());
        static::assertSame(1, $commentAdmin->getChildDepth());
        static::assertSame(0, $commentVoteAdmin->getChildDepth());

        $commentAdmin->addChild($commentVoteAdmin, 'comment');

        static::assertSame(0, $postAdmin->getChildDepth());
        static::assertSame(1, $commentAdmin->getChildDepth());
        static::assertSame(2, $commentVoteAdmin->getChildDepth());
    }

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

        static::assertNull($postAdmin->getCurrentLeafChildAdmin());
        static::assertNull($commentAdmin->getCurrentLeafChildAdmin());
        static::assertNull($commentVoteAdmin->getCurrentLeafChildAdmin());

        $commentAdmin->setCurrentChild(true);

        static::assertSame($commentAdmin, $postAdmin->getCurrentLeafChildAdmin());
        static::assertNull($commentAdmin->getCurrentLeafChildAdmin());
        static::assertNull($commentVoteAdmin->getCurrentLeafChildAdmin());

        $commentVoteAdmin->setCurrentChild(true);

        static::assertSame($commentVoteAdmin, $postAdmin->getCurrentLeafChildAdmin());
        static::assertSame($commentVoteAdmin, $commentAdmin->getCurrentLeafChildAdmin());
        static::assertNull($commentVoteAdmin->getCurrentLeafChildAdmin());
    }

    public function testAdminAvoidInifiniteLoop(): void
    {
        $this->expectNotToPerformAssertions();

        $registry = new FormRegistry([], new ResolvedFormTypeFactory());
        $formFactory = new FormFactory($registry);
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager
            ->method('getDefaultSortValues')
            ->willReturn([]);

        $admin = new AvoidInfiniteLoopAdmin('code', \stdClass::class, 'controller');
        $admin->setSubject(new \stdClass());

        $admin->setModelManager($modelManager);

        $admin->setFormContractor(new FormContractor($formFactory, $registry));

        $admin->setShowBuilder(new ShowBuilder());

        $admin->setListBuilder(new ListBuilder());

        $pager = $this->createStub(PagerInterface::class);
        $admin->setDatagridBuilder(new DatagridBuilder($formFactory, $pager));

        // NEXT_MAJOR: remove the following 3 lines
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
     * @expectedDeprecation  Passing other type than string as argument %d for method Sonata\AdminBundle\DependencyInjection\Admin\AbstractTaggedAdmin::__construct() is deprecated since sonata-project/admin-bundle 3.%s. It will accept only string in version 4.0.
     *
     * @doesNotPerformAssertions
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
