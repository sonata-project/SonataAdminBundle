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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
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
use Sonata\AdminBundle\Tests\App\Model\Foo;
use Sonata\AdminBundle\Tests\Fixtures\Admin\AvoidInfiniteLoopAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentVoteAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentWithCustomRouteAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\FilteredAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\ModelAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostCategoryAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostWithCustomRouteAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\TagAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\BlogPost;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Comment;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\CommentVote;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Post;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\PostCategory;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Tag;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooToString;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooToStringNull;
use Sonata\AdminBundle\Tests\Fixtures\FieldDescription\FieldDescription;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;
use Sonata\Doctrine\Adapter\AdapterInterface;
use Sonata\Exporter\Source\SourceIteratorInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminTest extends TestCase
{
    /**
     * @var string
     */
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
        $class = Post::class;
        $baseControllerName = 'Sonata\NewsBundle\Controller\PostAdminController';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        self::assertInstanceOf(AbstractAdmin::class, $admin);
        self::assertSame($class, $admin->getClass());
        self::assertSame($baseControllerName, $admin->getBaseControllerName());
    }

    public function testGetClass(): void
    {
        $class = Post::class;
        $baseControllerName = 'Sonata\NewsBundle\Controller\PostAdminController';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);

        $admin->setModelManager($this->createMock(ModelManagerInterface::class));

        $admin->setSubject(new BlogPost());
        self::assertSame(BlogPost::class, $admin->getClass());

        $admin->setSubClasses(['foo' => Foo::class]);
        self::assertSame(BlogPost::class, $admin->getClass());

        $admin->setSubject(null);
        $admin->setSubClasses([]);
        self::assertSame($class, $admin->getClass());

        $admin->setSubClasses(['foo' => Foo::class]);
        $admin->setRequest(new Request(['subclass' => 'foo']));
        self::assertSame(Foo::class, $admin->getClass());
    }

    public function testGetClassException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Feature not implemented: an embedded admin cannot have subclass');

        $class = Post::class;
        $baseControllerName = 'Sonata\NewsBundle\Controller\PostAdminController';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $admin->setParentFieldDescription(new FieldDescription('name'));
        $admin->setSubClasses(['foo' => Foo::class]);
        $admin->setRequest(new Request(['subclass' => 'foo']));
        $admin->getClass();
    }

    public function testCheckAccessThrowsExceptionOnMadeUpAction(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
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
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler->method('isGranted')->willReturnMap([
            [$admin, 'CUSTOM_ROLE', $admin, true],
            [$admin, 'EXTRA_CUSTOM_ROLE', $admin, false],
        ]);
        $customExtension = $this->createMock(AbstractAdminExtension::class);
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
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );

        self::assertFalse($admin->hasAccess('made-up'));
    }

    public function testHasAccess(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler->method('isGranted')->willReturnMap([
            [$admin, 'CUSTOM_ROLE', $admin, true],
            [$admin, 'EXTRA_CUSTOM_ROLE', $admin, false],
        ]);
        $customExtension = $this->createMock(AbstractAdminExtension::class);
        $customExtension->method('getAccessMapping')->with($admin)->willReturn(
            ['custom_action' => ['CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE']]
        );
        $admin->addExtension($customExtension);
        $admin->setSecurityHandler($securityHandler);

        self::assertFalse($admin->hasAccess('custom_action'));
    }

    public function testHasAccessAllowsAccess(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler->method('isGranted')->willReturnMap([
            [$admin, 'CUSTOM_ROLE', $admin, true],
            [$admin, 'EXTRA_CUSTOM_ROLE', $admin, true],
        ]);
        $customExtension = $this->createMock(AbstractAdminExtension::class);
        $customExtension->method('getAccessMapping')->with($admin)->willReturn(
            ['custom_action' => ['CUSTOM_ROLE', 'EXTRA_CUSTOM_ROLE']]
        );
        $admin->addExtension($customExtension);
        $admin->setSecurityHandler($securityHandler);

        self::assertTrue($admin->hasAccess('custom_action'));
    }

    public function testHasAccessAllowsAccessEditAction(): void
    {
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler->method('isGranted')->with($admin, 'EDIT_ROLE', $admin)->willReturn(true);
        $customExtension = $this->createMock(AbstractAdminExtension::class);
        $customExtension->method('getAccessMapping')->with($admin)->willReturn(
            ['edit_action' => ['EDIT_ROLE']]
        );
        $admin->addExtension($customExtension);
        $admin->setSecurityHandler($securityHandler);

        self::assertTrue($admin->hasAccess('edit_action'));
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
        $postAdmin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        self::assertFalse($postAdmin->hasChildren());
        self::assertFalse($postAdmin->hasChild('comment'));

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');
        $postAdmin->addChild($commentAdmin, 'post');

        self::assertTrue($postAdmin->hasChildren());
        self::assertTrue($postAdmin->hasChild('sonata.post.admin.comment'));

        self::assertSame('sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getCode());
        self::assertSame('sonata.post.admin.post|sonata.post.admin.comment', $postAdmin->getChild('sonata.post.admin.comment')->getBaseCodeRoute());
        self::assertSame($postAdmin, $postAdmin->getChild('sonata.post.admin.comment')->getParent());
        self::assertSame('post', $commentAdmin->getParentAssociationMapping());

        self::assertFalse($postAdmin->isChild());
        self::assertTrue($commentAdmin->isChild());

        self::assertSame(['sonata.post.admin.comment' => $commentAdmin], $postAdmin->getChildren());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getParent
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::setParent
     */
    public function testParent(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');
        self::assertFalse($commentAdmin->isChild());
        self::assertFalse($commentAdmin->hasParentFieldDescription());

        $commentAdmin->setParent($postAdmin, 'post');

        self::assertSame($postAdmin, $commentAdmin->getParent());
        self::assertSame('post', $commentAdmin->getParentAssociationMapping());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::configure
     */
    public function testConfigure(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        self::assertNotNull($admin->getUniqId());

        $admin->initialize();
        self::assertNotNull($admin->getUniqId());
        self::assertSame('Post', $admin->getClassnameLabel());

        $admin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');
        $admin->setClassnameLabel('postcomment');

        $admin->initialize();
        self::assertSame('postcomment', $admin->getClassnameLabel());
    }

    public function testConfigureWithValidParentAssociationMapping(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $comment = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');
        $comment->addChild($admin, 'comment');

        $admin->initialize();

        self::assertSame('comment', $admin->getParentAssociationMapping());
    }

    /**
     * @phpstan-return iterable<array-key, array{class-string, string}>
     * @psalm-suppress InvalidReturnType, InvalidReturnStatement
     */
    public function provideGetBaseRoutePattern(): iterable
    {
        // @phpstan-ignore-next-line
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
     * @param class-string $objFqn
     *
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePattern(string $objFqn, string $expected): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        self::assertSame($expected, $admin->getBaseRoutePattern());
    }

    /**
     * @param class-string $objFqn
     *
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePatternWithChildAdmin(string $objFqn, string $expected): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setParent($postAdmin, 'post');

        self::assertSame(sprintf('%s/{id}/comment', $expected), $commentAdmin->getBaseRoutePattern());
    }

    /**
     * @param class-string $objFqn
     *
     * @dataProvider provideGetBaseRoutePattern
     */
    public function testGetBaseRoutePatternWithTwoNestedChildAdmin(string $objFqn, string $expected): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );

        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            CommentVote::class,
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );
        $commentAdmin->setParent($postAdmin, 'post');
        $commentVoteAdmin->setParent($commentAdmin, 'comment');

        self::assertSame(sprintf('%s/{id}/comment/{childId}/commentvote', $expected), $commentVoteAdmin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithSpecifedPattern(): void
    {
        $postAdmin = new PostWithCustomRouteAdmin('sonata.post.admin.post_with_custom_route', Post::class, 'Sonata\NewsBundle\Controller\PostWithCustomRouteAdminController');

        self::assertSame('/post-custom', $postAdmin->getBaseRoutePattern());
    }

    public function testGetBaseRoutePatternWithChildAdminAndWithSpecifedPattern(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentWithCustomRouteAdmin('sonata.post.admin.comment_with_custom_route', Comment::class, 'Sonata\NewsBundle\Controller\CommentWithCustomRouteAdminController');
        $commentAdmin->setParent($postAdmin, 'post');

        self::assertSame('/fixtures/bundle/post/{id}/comment-custom', $commentAdmin->getBaseRoutePattern());
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion, UndefinedClass
     */
    public function testGetBaseRoutePatternWithUnreconizedClassname(): void
    {
        $this->expectException(\LogicException::class);

        // @phpstan-ignore-next-line
        $admin = new PostAdmin('sonata.post.admin.post', 'News\Thing\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->getBaseRoutePattern();
    }

    /**
     * @phpstan-return iterable<array-key, array{class-string, string}>
     * @psalm-suppress InvalidReturnType, InvalidReturnStatement
     */
    public function provideGetBaseRouteName(): iterable
    {
        // @phpstan-ignore-next-line
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
     * @param class-string $objFqn
     *
     * @dataProvider provideGetBaseRouteName
     */
    public function testGetBaseRouteName(string $objFqn, string $expected): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame($expected, $admin->getBaseRouteName());
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion, UndefinedClass
     */
    public function testGetBaseRouteNameWithUnreconizedClassname(): void
    {
        $this->expectException(\LogicException::class);

        // @phpstan-ignore-next-line
        $admin = new PostAdmin('sonata.post.admin.post', 'News\Thing\Post', 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->getBaseRouteName();
    }

    public function testGetBaseRouteNameWithSpecifiedName(): void
    {
        $postAdmin = new PostWithCustomRouteAdmin('sonata.post.admin.post_with_custom_route', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame('post_custom', $postAdmin->getBaseRouteName());
    }

    public function testGetBaseRouteNameWithChildAdminAndWithSpecifiedName(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentWithCustomRouteAdmin('sonata.post.admin.comment_with_custom_route', Comment::class, 'Sonata\NewsBundle\Controller\CommentWithCustomRouteAdminController');
        $commentAdmin->setParent($postAdmin, 'post');

        self::assertSame('admin_fixtures_bundle_post_comment_custom', $commentAdmin->getBaseRouteName());
    }

    public function testGetBaseRouteNameWithTwoNestedChildAdminAndWithSpecifiedName(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentWithCustomRouteAdmin(
            'sonata.post.admin.comment_with_custom_route',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentWithCustomRouteAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            CommentVote::class,
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );
        $commentAdmin->setParent($postAdmin, 'post');
        $commentVoteAdmin->setParent($commentAdmin, 'comment');

        self::assertSame('admin_fixtures_bundle_post_comment_custom_commentvote', $commentVoteAdmin->getBaseRouteName());
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::setUniqId
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getUniqId
     */
    public function testSetUniqId(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $uniqId = uniqid();
        $admin->setUniqId($uniqId);

        self::assertSame($uniqId, $admin->getUniqId());
    }

    public function testToString(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $s = new \stdClass();

        self::assertNotEmpty($admin->toString($s));

        $s = new FooToString();
        self::assertSame('salut', $admin->toString($s));
    }

    public function testToStringNull(): void
    {
        if (\PHP_VERSION_ID >= 80000) {
            self::markTestSkipped('PHP 8.0 does not allow __toString() method to return null');
        }

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        // To string method is implemented, but returns null
        $s = new FooToStringNull();
        self::assertNotEmpty($admin->toString($s));
    }

    public function testIsAclEnabled(): void
    {
        $postAdmin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $postAdmin->setSecurityHandler($this->createMock(SecurityHandlerInterface::class));
        self::assertFalse($postAdmin->isAclEnabled());

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setSecurityHandler($this->createMock(AclSecurityHandlerInterface::class));
        self::assertTrue($commentAdmin->isAclEnabled());
    }

    /**
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
        $admin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        self::assertFalse($admin->hasSubClass('test'));
        self::assertFalse($admin->hasActiveSubClass());
        self::assertCount(0, $admin->getSubClasses());
        self::assertSame(Post::class, $admin->getClass());

        // Just for the record, if there is no inheritance set, the getSubject is not used
        // the getSubject can also lead to some issue
        $admin->setSubject(new BlogPost());
        self::assertSame(BlogPost::class, $admin->getClass());

        /** @var class-string $postExtended1 */
        $postExtended1 = 'NewsBundle\Entity\PostExtended1';
        /** @var class-string $postExtended2 */
        $postExtended2 = 'NewsBundle\Entity\PostExtended2';

        $admin->setSubClasses([
            'extended1' => $postExtended1,
            'extended2' => $postExtended2,
        ]);
        self::assertFalse($admin->hasSubClass('test'));
        self::assertTrue($admin->hasSubClass('extended1'));
        self::assertFalse($admin->hasActiveSubClass());
        self::assertCount(2, $admin->getSubClasses());
        self::assertSame(
            BlogPost::class,
            $admin->getClass(),
            'When there is no subclass in the query the class parameter should be returned'
        );

        $request = new Request(['subclass' => 'extended1']);
        $admin->setRequest($request);
        self::assertFalse($admin->hasSubClass('test'));
        self::assertTrue($admin->hasSubClass('extended1'));
        self::assertTrue($admin->hasActiveSubClass());
        self::assertCount(2, $admin->getSubClasses());
        self::assertSame(
            $postExtended1,
            $admin->getActiveSubClass(),
            'It should return the curently active sub class.'
        );
        self::assertSame('extended1', $admin->getActiveSubclassCode());
        self::assertSame(
            'NewsBundle\Entity\PostExtended1',
            $admin->getClass(),
            'getClass() should return the name of the sub class when passed through a request query parameter.'
        );

        $request->query->set('subclass', 'inject');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Admin "%s" has no active subclass.', PostAdmin::class));

        $admin->getActiveSubclassCode();
    }

    public function testNonExistantSubclass(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($this->createMock(ModelManagerInterface::class));

        $admin->setRequest(new Request(['subclass' => 'inject']));

        /** @var class-string $postExtended1 */
        $postExtended1 = 'NewsBundle\Entity\PostExtended1';
        /** @var class-string $postExtended2 */
        $postExtended2 = 'NewsBundle\Entity\PostExtended2';

        $admin->setSubClasses([
            'extended1' => $postExtended1,
            'extended2' => $postExtended2,
        ]);

        self::assertTrue($admin->hasActiveSubClass());

        $this->expectException(\LogicException::class);

        $admin->getActiveSubClass();
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::hasActiveSubClass
     */
    public function testOnlyOneSubclassNeededToBeActive(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        /** @var class-string $postExtended1 */
        $postExtended1 = 'NewsBundle\Entity\PostExtended1';
        $admin->setSubClasses(['extended1' => $postExtended1]);

        $request = new Request(['subclass' => 'extended1']);
        $admin->setRequest($request);
        self::assertTrue($admin->hasActiveSubClass());
    }

    public function testGetPerPageOptions(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $perPageOptions = $admin->getPerPageOptions();

        self::assertSame([10, 25, 50, 100, 250], $perPageOptions);
    }

    public function testGetLabelTranslatorStrategy(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $labelTranslatorStrategy = $this->createMock(LabelTranslatorStrategyInterface::class);
        $admin->setLabelTranslatorStrategy($labelTranslatorStrategy);
        self::assertSame($labelTranslatorStrategy, $admin->getLabelTranslatorStrategy());
    }

    public function testGetLabelTranslatorStrategyWithException(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'Admin "%s" has no label translator strategy.',
            PostAdmin::class
        ));

        $admin->getLabelTranslatorStrategy();
    }

    public function testGetRouteBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $routeBuilder = $this->createMock(RouteBuilderInterface::class);
        $admin->setRouteBuilder($routeBuilder);
        self::assertSame($routeBuilder, $admin->getRouteBuilder());
    }

    public function testGetMenuFactory(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $menuFactory = $this->createMock(FactoryInterface::class);
        $admin->setMenuFactory($menuFactory);
        self::assertSame($menuFactory, $admin->getMenuFactory());
    }

    public function testGetExtensions(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame([], $admin->getExtensions());

        $adminExtension1 = $this->createMock(AdminExtensionInterface::class);
        $adminExtension2 = $this->createMock(AdminExtensionInterface::class);

        $admin->addExtension($adminExtension1);
        $admin->addExtension($adminExtension2);
        self::assertSame([$adminExtension1, $adminExtension2], $admin->getExtensions());
    }

    public function testGetFilterTheme(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame([], $admin->getFilterTheme());

        $admin->setFilterTheme(['FooTheme']);
        self::assertSame(['FooTheme'], $admin->getFilterTheme());
    }

    public function testGetFormTheme(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame([], $admin->getFormTheme());

        $admin->setFormTheme(['FooTheme']);

        self::assertSame(['FooTheme'], $admin->getFormTheme());
    }

    public function testGetSecurityHandler(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $admin->setSecurityHandler($securityHandler);
        self::assertSame($securityHandler, $admin->getSecurityHandler());
    }

    public function testGetSecurityInformation(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame([], $admin->getSecurityInformation());

        $securityInformation = [
            'GUEST' => ['VIEW', 'LIST'],
            'STAFF' => ['EDIT', 'LIST', 'CREATE'],
        ];

        $admin->setSecurityInformation($securityInformation);
        self::assertSame($securityInformation, $admin->getSecurityInformation());
    }

    public function testGetManagerType(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $admin->setManagerType('foo_orm');
        self::assertSame('foo_orm', $admin->getManagerType());
    }

    public function testGetModelManager(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $admin->setModelManager($modelManager);
        self::assertSame($modelManager, $admin->getModelManager());
    }

    public function testGetModelManagerWithException(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'Admin "%s" has no model manager.',
            PostAdmin::class
        ));

        $admin->getModelManager();
    }

    public function testGetBaseCodeRoute(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );

        self::assertSame($postAdmin->getCode(), $postAdmin->getBaseCodeRoute());

        $postAdmin->addChild($commentAdmin, 'post');

        self::assertSame(
            'sonata.post.admin.post|sonata.post.admin.comment',
            $commentAdmin->getBaseCodeRoute()
        );
    }

    public function testGetRouteGenerator(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);

        $admin->setRouteGenerator($routeGenerator);
        self::assertSame($routeGenerator, $admin->getRouteGenerator());
    }

    public function testGetRouteGeneratorWithException(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Admin "%s" has no route generator.', PostAdmin::class));

        $admin->getRouteGenerator();
    }

    public function testGetConfigurationPool(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $pool = new Pool(new Container());

        $admin->setConfigurationPool($pool);
        self::assertSame($pool, $admin->getConfigurationPool());
    }

    public function testGetConfigurationPoolWithException(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Admin "%s" has no pool.', PostAdmin::class));

        $admin->getConfigurationPool();
    }

    public function testGetShowBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $showBuilder = $this->createMock(ShowBuilderInterface::class);

        $admin->setShowBuilder($showBuilder);
        self::assertSame($showBuilder, $admin->getShowBuilder());
    }

    public function testGetListBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $listBuilder = $this->createMock(ListBuilderInterface::class);

        $admin->setListBuilder($listBuilder);
        self::assertSame($listBuilder, $admin->getListBuilder());
    }

    public function testGetDatagridBuilder(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        $admin->setDatagridBuilder($datagridBuilder);
        self::assertSame($datagridBuilder, $admin->getDatagridBuilder());
    }

    public function testGetFormContractor(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $formContractor = $this->createMock(FormContractorInterface::class);

        $admin->setFormContractor($formContractor);
        self::assertSame($formContractor, $admin->getFormContractor());
    }

    public function testGetRequest(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertFalse($admin->hasRequest());

        $request = new Request();

        $admin->setRequest($request);
        self::assertSame($request, $admin->getRequest());
        self::assertTrue($admin->hasRequest());
    }

    public function testGetRequestWithException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The Request object has not been set');

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->getRequest();
    }

    public function testGetTranslationDomain(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame('messages', $admin->getTranslationDomain());

        $admin->setTranslationDomain('foo');
        self::assertSame('foo', $admin->getTranslationDomain());
    }

    public function testGetTranslator(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $translator = $this->createMock(TranslatorInterface::class);

        $admin->setTranslator($translator);
        self::assertSame($translator, $admin->getTranslator());
    }

    public function testGetShowGroups(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame([], $admin->getShowGroups());

        $groups = ['group' => []];

        $admin->setShowGroups($groups);
        self::assertSame($groups, $admin->getShowGroups());
    }

    public function testGetFormGroups(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame([], $admin->getFormGroups());

        $groups = ['group' => []];

        $admin->setFormGroups($groups);
        self::assertSame($groups, $admin->getFormGroups());
    }

    public function testGetMaxPageLinks(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame(25, $admin->getMaxPageLinks());

        $admin->setMaxPageLinks(14);
        self::assertSame(14, $admin->getMaxPageLinks());
    }

    public function testGetMaxPerPage(): void
    {
        $modelManager = $this->createStub(ModelManagerInterface::class);

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        self::assertSame(25, $admin->getMaxPerPage());
    }

    public function testGetLabel(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertNull($admin->getLabel());

        $admin->setLabel('FooLabel');
        self::assertSame('FooLabel', $admin->getLabel());
    }

    public function testGetBaseController(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame('Sonata\NewsBundle\Controller\PostAdminController', $admin->getBaseControllerName());

        $admin->setBaseControllerName('Sonata\NewsBundle\Controller\FooAdminController');
        self::assertSame('Sonata\NewsBundle\Controller\FooAdminController', $admin->getBaseControllerName());
    }

    public function testGetIdParameter(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );

        self::assertSame('id', $postAdmin->getIdParameter());
        self::assertFalse($postAdmin->isChild());

        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentAdmin->setParent($postAdmin, 'post');

        self::assertTrue($commentAdmin->isChild());
        self::assertSame('childId', $commentAdmin->getIdParameter());

        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            CommentVote::class,
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );
        $commentVoteAdmin->setParent($commentAdmin, 'comment');

        self::assertTrue($commentVoteAdmin->isChild());
        self::assertSame('childChildId', $commentVoteAdmin->getIdParameter());
    }

    public function testGetExportFormats(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame(['json', 'xml', 'csv', 'xls'], $admin->getExportFormats());
    }

    public function testGetUrlsafeIdentifier(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $model = new \stdClass();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects(self::once())
            ->method('getUrlSafeIdentifier')
            ->with(self::equalTo($model))
            ->willReturn('foo');
        $admin->setModelManager($modelManager);

        self::assertSame('foo', $admin->getUrlSafeIdentifier($model));
    }

    public function testDeterminedPerPageValue(): void
    {
        $modelManager = $this->createStub(ModelManagerInterface::class);

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        self::assertFalse($admin->determinedPerPageValue(123));
        self::assertTrue($admin->determinedPerPageValue(25));
    }

    public function testIsGranted(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager
            ->method('getNormalizedIdentifier')
            ->willReturnCallback(static function (?object $model = null): ?string {
                // @phpstan-ignore-next-line
                return $model ? $model->id : null;
            });

        $admin->setModelManager($modelManager);

        $entity1 = new \stdClass();
        $entity1->id = '1';

        $securityHandler = $this->createMock(AclSecurityHandlerInterface::class);
        $securityHandler
            ->expects(self::exactly(6))
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

        self::assertTrue($admin->isGranted('FOO'));
        self::assertTrue($admin->isGranted('FOO'));
        self::assertTrue($admin->isGranted('FOO', $entity1));
        self::assertTrue($admin->isGranted('FOO', $entity1));
        self::assertFalse($admin->isGranted('BAR'));
        self::assertFalse($admin->isGranted('BAR'));
        self::assertFalse($admin->isGranted('BAR', $entity1));
        self::assertFalse($admin->isGranted('BAR', $entity1));

        $entity2 = new \stdClass();
        $entity2->id = '2';

        self::assertFalse($admin->isGranted('BAR', $entity2));
        self::assertFalse($admin->isGranted('BAR', $entity2));

        $entity3 = new \stdClass();
        $entity3->id = '3';

        self::assertFalse($admin->isGranted('BAR', $entity3));
        self::assertFalse($admin->isGranted('BAR', $entity3));
    }

    public function testSupportsPreviewMode(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertFalse($admin->supportsPreviewMode());
    }

    public function testShowIn(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $securityHandler = $this->createMock(AclSecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static function (AdminInterface $adminIn, array $attributes, ?object $object = null) use ($admin): bool {
                return $admin === $adminIn && $attributes === ['LIST'];
            });

        $admin->setSecurityHandler($securityHandler);

        self::assertTrue($admin->showIn(AbstractAdmin::CONTEXT_DASHBOARD));
        self::assertTrue($admin->showIn(AbstractAdmin::CONTEXT_MENU));
        self::assertTrue($admin->showIn('foo'));
    }

    public function testGetObjectIdentifier(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame('sonata.post.admin.post', $admin->getObjectIdentifier());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSetFilterPersister(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'SonataNewsBundle\Controller\PostAdminController');

        $filterPersister = $this->createMock(FilterPersisterInterface::class);

        $admin->setFilterPersister($filterPersister);
    }

    public function testGetRootCode(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame('sonata.post.admin.post', $admin->getRootCode());

        $parentAdmin = new PostAdmin('sonata.post.admin.post.parent', Post::class, 'Sonata\NewsBundle\Controller\PostParentAdminController');
        $parentFieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $parentFieldDescription->expects(self::once())
            ->method('getAdmin')
            ->willReturn($parentAdmin);

        self::assertFalse($admin->hasParentFieldDescription());
        $admin->setParentFieldDescription($parentFieldDescription);
        self::assertSame($parentFieldDescription, $admin->getParentFieldDescription());
        self::assertSame('sonata.post.admin.post.parent', $admin->getRootCode());
    }

    public function testGetRoot(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertSame($admin, $admin->getRoot());

        $parentAdmin = new PostAdmin('sonata.post.admin.post.parent', Post::class, 'Sonata\NewsBundle\Controller\PostParentAdminController');
        $parentFieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $parentFieldDescription->expects(self::once())
            ->method('getAdmin')
            ->willReturn($parentAdmin);

        self::assertFalse($admin->hasParentFieldDescription());
        $admin->setParentFieldDescription($parentFieldDescription);
        self::assertSame($parentFieldDescription, $admin->getParentFieldDescription());
        self::assertSame($parentAdmin, $admin->getRoot());
    }

    public function testGetExportFields(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects(self::once())
            ->method('getExportFields')
            ->with(self::equalTo(Post::class))
            ->willReturn(['foo', 'bar']);

        $admin->setModelManager($modelManager);
        self::assertSame(['foo', 'bar'], $admin->getExportFields());
    }

    public function testGetPersistentParametersWithNoExtension(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        self::assertEmpty($admin->getPersistentParameters());
    }

    public function testGetPersistentParametersWithValidExtension(): void
    {
        $expected = [
            'context' => 'foobar',
        ];

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $extension = $this->createMock(AdminExtensionInterface::class);
        $extension->expects(self::once())->method('configurePersistentParameters')->willReturn($expected);

        $admin->addExtension($extension);

        self::assertSame($expected, $admin->getPersistentParameters());
    }

    public function testGetNewInstanceForChildAdminWithParentValue(): void
    {
        $post = new Post();

        $postAdmin = $this->getMockBuilder(PostAdmin::class)->setConstructorArgs([
            'post',
            Post::class,
            CRUDController::class,
        ])->getMock();

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager->method('find')->willReturn($post);
        $postAdmin->setModelManager($modelManager);

        $postAdmin->method('getIdParameter')->willReturn('parent_id');

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder->method('getForm')->willReturn(null);

        $tagAdmin = new TagAdmin('admin.tag', Tag::class, 'MyBundle\MyController');
        $tagAdmin->setParent($postAdmin, 'post');

        $request = $this->createMock(Request::class);
        $request->method('get')->with('parent_id')->willReturn(42);
        $tagAdmin->setRequest($request);

        $tag = $tagAdmin->getNewInstance();

        self::assertSame($post, $tag->getPost());
    }

    public function testGetNewInstanceForChildAdminWithCollectionParentValue(): void
    {
        $post = new Post();

        $postAdmin = $this->getMockBuilder(PostAdmin::class)->setConstructorArgs([
            'post',
            Post::class,
            CRUDController::class,
        ])->getMock();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->method('find')->willReturn($post);
        $postAdmin->setModelManager($modelManager);

        $postAdmin->method('getIdParameter')->willReturn('parent_id');

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder->method('getForm')->willReturn(null);

        $postCategoryAdmin = new PostCategoryAdmin('admin.post_category', PostCategory::class, 'MyBundle\MyController');
        $postCategoryAdmin->setParent($postAdmin, 'posts');

        $request = $this->createMock(Request::class);
        $request->method('get')->with('parent_id')->willReturn(42);
        $postCategoryAdmin->setRequest($request);

        $postCategory = $postCategoryAdmin->getNewInstance();

        self::assertInstanceOf(Collection::class, $postCategory->getPosts());
        self::assertCount(1, $postCategory->getPosts());
        self::assertContains($post, $postCategory->getPosts());
    }

    public function testGetNewInstanceForEmbededAdminWithParentValue(): void
    {
        $post = new Post();

        $postAdmin = $this->getMockBuilder(PostAdmin::class)->setConstructorArgs([
            'post',
            Post::class,
            CRUDController::class,
        ])->getMock();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->method('find')->willReturn($post);
        $postAdmin->setModelManager($modelManager);

        $postAdmin->method('getIdParameter')->willReturn('parent_id');

        $formBuilder = $this->createStub(FormBuilderInterface::class);
        $formBuilder->method('getForm')->willReturn(null);

        $parentField = $this->createStub(FieldDescriptionInterface::class);
        $parentField->method('getAdmin')->willReturn($postAdmin);
        $parentField->method('getParentAssociationMappings')->willReturn([]);
        $parentField->method('getAssociationMapping')->willReturn(['fieldName' => 'tag', 'mappedBy' => 'post']);

        $tagAdmin = new TagAdmin('admin.tag', Tag::class, 'MyBundle\MyController');
        $tagAdmin->setParentFieldDescription($parentField);

        $request = $this->createMock(Request::class);
        $request->method('get')->with('parent_id')->willReturn(42);
        $tagAdmin->setRequest($request);

        $tag = $tagAdmin->getNewInstance();

        self::assertSame($post, $tag->getPost());
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

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setFormGroups($formGroups);

        $admin->removeFieldFromFormGroup('foo');
        self::assertSame($admin->getFormGroups(), [
            'foobar' => [
                'fields' => [
                    'bar' => 'bar',
                ],
            ],
        ]);

        $admin->removeFieldFromFormGroup('bar');
        self::assertSame($admin->getFormGroups(), []);
    }

    public function testGetFilterParameters(): void
    {
        $authorId = uniqid();

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');

        $postAdmin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $postAdmin->addChild($commentAdmin, 'post__author');

        $request = $this->createMock(Request::class);
        $query = new ParameterBag();
        $query
            ->set('filter', [
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

        $parameters = $commentAdmin->getFilterParameters();

        self::assertTrue(isset($parameters['post__author']));
        self::assertSame(['value' => $authorId], $parameters['post__author']);
    }

    public function testGetFilterParametersWithoutRequest(): void
    {
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );

        $parameters = $commentAdmin->getFilterParameters();

        self::assertArrayHasKey(DatagridInterface::PER_PAGE, $parameters);
        self::assertSame(25, $parameters[DatagridInterface::PER_PAGE]);
    }

    public function testGetFilterFieldDescription(): void
    {
        $modelAdmin = new ModelAdmin('sonata.post.admin.model', Post::class, 'Sonata\FooBundle\Controller\ModelAdminController');
        $modelAdmin->setLabelTranslatorStrategy(new NoopLabelTranslatorStrategy());

        $fooFieldDescription = new FieldDescription('foo');
        $barFieldDescription = new FieldDescription('bar');
        $bazFieldDescription = new FieldDescription('baz');

        $fieldDescriptionFactory = $this->createMock(FieldDescriptionFactoryInterface::class);
        $fieldDescriptionFactory
            ->expects(self::exactly(3))
            ->method('create')
            ->willReturnCallback(static function (string $adminClass, string $name, array $filterOptions) use ($fooFieldDescription, $barFieldDescription, $bazFieldDescription): FieldDescriptionInterface {
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
                }

                $fieldDescription->setName($name);

                return $fieldDescription;
            });

        $modelAdmin->setFieldDescriptionFactory($fieldDescriptionFactory);

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelAdmin->setModelManager($modelManager);

        $pager = $this->createMock(PagerInterface::class);

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects(self::once())
            ->method('getPager')
            ->willReturn($pager);

        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);
        $datagridBuilder->expects(self::once())
            ->method('getBaseDatagrid')
            ->with($this->identicalTo($modelAdmin))
            ->willReturn($datagrid);

        $datagridBuilder->expects(self::exactly(3))
            ->method('addFilter')
            ->willReturnCallback(static function (DatagridInterface $datagrid, ?string $type, FieldDescriptionInterface $fieldDescription): void {
                $fieldDescription->getAdmin()->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);
                $fieldDescription->mergeOption('field_options', ['required' => false]);
            });

        $modelAdmin->setDatagridBuilder($datagridBuilder);

        self::assertSame(['foo' => $fooFieldDescription, 'bar' => $barFieldDescription, 'baz' => $bazFieldDescription], $modelAdmin->getFilterFieldDescriptions());
        self::assertFalse($modelAdmin->hasFilterFieldDescription('fooBar'));
        self::assertTrue($modelAdmin->hasFilterFieldDescription('foo'));
        self::assertTrue($modelAdmin->hasFilterFieldDescription('bar'));
        self::assertTrue($modelAdmin->hasFilterFieldDescription('baz'));
        self::assertSame($fooFieldDescription, $modelAdmin->getFilterFieldDescription('foo'));
        self::assertSame($barFieldDescription, $modelAdmin->getFilterFieldDescription('bar'));
        self::assertSame($bazFieldDescription, $modelAdmin->getFilterFieldDescription('baz'));
    }

    public function testGetSubjectNoRequest(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects(self::never())
            ->method('find');

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        self::assertFalse($admin->hasSubject());
    }

    public function testGetSideMenu(): void
    {
        $item = $this->createMock(ItemInterface::class);
        $item
            ->expects(self::once())
            ->method('setChildrenAttribute')
            ->with('class', 'nav navbar-nav');
        $item
            ->expects(self::once())
            ->method('setExtra')
            ->with('translation_domain', 'foo_bar_baz');

        $menuFactory = $this->createMock(FactoryInterface::class);
        $menuFactory
            ->expects(self::once())
            ->method('createItem')
            ->willReturn($item);

        $modelAdmin = new ModelAdmin('sonata.post.admin.model', Post::class, 'Sonata\FooBundle\Controller\ModelAdminController');
        $modelAdmin->setMenuFactory($menuFactory);
        $modelAdmin->setTranslationDomain('foo_bar_baz');

        $modelAdmin->getSideMenu('foo');
    }

    /**
     * @phpstan-return iterable<array-key, array{int|string}>
     */
    public function provideGetSubject(): iterable
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
     * @param int|string $id
     *
     * @dataProvider provideGetSubject
     */
    public function testGetSubjectFailed($id): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects(self::once())
            ->method('find')
            ->with(Post::class, $id)
            ->willReturn(null); // entity not found

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        $admin->setRequest(new Request(['id' => $id]));
        self::assertFalse($admin->hasSubject());
    }

    /**
     * @param int|string $id
     *
     * @dataProvider provideGetSubject
     */
    public function testGetSubject($id): void
    {
        $model = new Post();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects(self::once())
            ->method('find')
            ->with(Post::class, $id)
            ->willReturn($model);

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setModelManager($modelManager);

        $admin->setRequest(new Request(['id' => $id]));
        self::assertTrue($admin->hasSubject());
        self::assertSame($model, $admin->getSubject());
        self::assertSame($model, $admin->getSubject()); // model manager must be used only once
    }

    public function testGetSubjectWithParentDescription(): void
    {
        $adminId = 1;

        $comment = new Comment();

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->method('find')
            ->with(Comment::class, $adminId)
            ->willReturn($comment);

        $request = new Request(['id' => $adminId]);

        $postAdmin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $postAdmin->setRequest($request);

        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');
        $commentAdmin->setRequest($request);
        $commentAdmin->setModelManager($modelManager);

        self::assertTrue($commentAdmin->hasSubject());
        self::assertSame($comment, $commentAdmin->getSubject());

        $commentAdmin->setSubject(null);
        $commentAdmin->setParentFieldDescription(new FieldDescription('name'));

        self::assertFalse($commentAdmin->hasSubject());
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

        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $templateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $templateRegistry->method('getTemplate')->with('button_create')->willReturn('Foo.html.twig');

        $admin->setTemplateRegistry($templateRegistry);

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->expects(self::once())
            ->method('isGranted')
            ->with($admin, 'CREATE', $admin)
            ->willReturn(true);
        $admin->setSecurityHandler($securityHandler);

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routeGenerator
            ->expects(self::once())
            ->method('hasAdminRoute')
            ->with($admin, 'create')
            ->willReturn(true);
        $admin->setRouteGenerator($routeGenerator);

        self::assertSame($expected, $admin->getActionButtons('list', null));
    }

    /**
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::configureActionButtons
     */
    public function testGetActionButtonsListCreateDisabled(): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->expects(self::once())
            ->method('isGranted')
            ->with($admin, 'CREATE', $admin)
            ->willReturn(false);
        $admin->setSecurityHandler($securityHandler);

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routeGenerator
            ->expects(self::once())
            ->method('hasAdminRoute')
            ->with($admin, 'create')
            ->willReturn(true);
        $admin->setRouteGenerator($routeGenerator);

        self::assertSame([], $admin->getActionButtons('list', null));
    }

    public function testGetActionButtonsListWithoutExtraChecks(): void
    {
        /** @var AbstractAdmin<object>&MockObject $admin */
        $admin = $this->getMockBuilder(AbstractAdmin::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getActionButtons', 'configureActionButtons'])
            ->getMockForAbstractClass();

        $securityHandler = $this->createStub(AclSecurityHandlerInterface::class);
        $admin->setSecurityHandler($securityHandler);

        $routerGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routerGenerator->expects(self::exactly(4))->method('hasAdminRoute')->willReturn(false);
        $admin->setRouteGenerator($routerGenerator);

        self::assertSame([], $admin->getActionButtons('show'));
        self::assertSame([], $admin->getActionButtons('edit'));
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

        $admin = new PostAdmin('sonata.post.admin.model', Post::class, 'Sonata\FooBundle\Controller\ModelAdminController');
        $admin->setRouteBuilder($pathInfo);
        $admin->setTranslationDomain('SonataAdminBundle');
        $admin->setLabelTranslatorStrategy($labelTranslatorStrategy);

        $routeGenerator = $this->createMock(RouteGeneratorInterface::class);
        $routeGenerator
            ->expects(self::once())
            ->method('hasAdminRoute')
            ->with($admin, 'delete')
            ->willReturn(true);
        $admin->setRouteGenerator($routeGenerator);

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static function (AdminInterface $adminIn, string $attributes, ?object $object = null) use ($admin): bool {
                return $admin === $adminIn && 'DELETE' === $attributes;
            });
        $admin->setSecurityHandler($securityHandler);

        self::assertSame($expected, $admin->getBatchActions());
    }

    /**
     * @dataProvider getListModeProvider
     */
    public function testGetListMode(string $expected, ?Request $request = null): void
    {
        $admin = new PostAdmin('sonata.post.admin.post', Post::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        if (null !== $request) {
            $admin->setRequest($request);
        }

        self::assertSame($expected, $admin->getListMode());
    }

    /**
     * @phpstan-return iterable<array-key, array{string, Request|null}>
     */
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
     * @param class-string $objFqn
     *
     * @covers \Sonata\AdminBundle\Admin\AbstractAdmin::getDashboardActions
     * @dataProvider provideGetBaseRouteName
     */
    public function testDefaultDashboardActionsArePresent(string $objFqn, string $expected): void
    {
        $pathInfo = new PathInfoBuilder($this->createMock(AuditManagerInterface::class));
        $routerMock = $this->createMock(RouterInterface::class);

        $routerMock->method('generate')->willReturn('/admin/post');

        $routeGenerator = new DefaultRouteGenerator(
            $routerMock,
            new RoutesCache($this->cacheTempFolder, true)
        );

        $admin = new PostAdmin('sonata.post.admin.post', $objFqn, 'Sonata\NewsBundle\Controller\PostAdminController');
        $admin->setRouteBuilder($pathInfo);
        $admin->setRouteGenerator($routeGenerator);
        $admin->initialize();

        $templateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $templateRegistry->method('getTemplate')->with('action_create')->willReturn('Foo.html.twig');

        $admin->setTemplateRegistry($templateRegistry);

        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static function (AdminInterface $adminIn, string $attributes, ?object $object = null) use ($admin): bool {
                return $admin === $adminIn && ('CREATE' === $attributes || 'LIST' === $attributes);
            });

        $admin->setSecurityHandler($securityHandler);

        self::assertArrayHasKey('list', $admin->getDashboardActions());
        self::assertArrayHasKey('create', $admin->getDashboardActions());
    }

    public function testDefaultFilters(): void
    {
        $admin = new FilteredAdmin('sonata.post.admin.model', Post::class, 'Sonata\FooBundle\Controller\ModelAdminController');

        $subjectId = uniqid();

        $request = $this->createMock(Request::class);
        $query = new ParameterBag();
        $query
            ->set('filter', [
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

        self::assertSame([
            DatagridInterface::PAGE => 1,
            DatagridInterface::PER_PAGE => 25,
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
    }

    public function testCircularChildAdmin(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Circular reference detected! The child admin `sonata.post.admin.post` is already in the parent tree of the `sonata.post.admin.comment` admin.'
        );

        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $postAdmin->addChild($commentAdmin, 'post');
        $commentAdmin->addChild($postAdmin, 'comment');
    }

    public function testCircularChildAdminTripleLevel(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Circular reference detected! The child admin `sonata.post.admin.post` is already in the parent tree of the `sonata.post.admin.comment_vote` admin.'
        );

        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            CommentVote::class,
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );
        $postAdmin->addChild($commentAdmin, 'post');
        $commentAdmin->addChild($commentVoteAdmin, 'comment');
        $commentVoteAdmin->addChild($postAdmin, 'post');
    }

    public function testCircularChildAdminWithItself(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Circular reference detected! The child admin `sonata.post.admin.post` is already in the parent tree of the `sonata.post.admin.post` admin.'
        );

        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $postAdmin->addChild($postAdmin, 'post');
    }

    public function testGetRootAncestor(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            CommentVote::class,
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );

        // Workaround for static analysis
        $commentAdminRootAncestor = $commentAdmin->getRootAncestor();

        self::assertSame($postAdmin, $postAdmin->getRootAncestor());
        self::assertSame($commentAdmin, $commentAdminRootAncestor);
        self::assertSame($commentVoteAdmin, $commentVoteAdmin->getRootAncestor());

        $postAdmin->addChild($commentAdmin, 'post');

        self::assertSame($postAdmin, $postAdmin->getRootAncestor());
        self::assertSame($postAdmin, $commentAdmin->getRootAncestor());
        self::assertSame($commentVoteAdmin, $commentVoteAdmin->getRootAncestor());

        $commentAdmin->addChild($commentVoteAdmin, 'comment');

        self::assertSame($postAdmin, $postAdmin->getRootAncestor());
        self::assertSame($postAdmin, $commentAdmin->getRootAncestor());
        self::assertSame($postAdmin, $commentVoteAdmin->getRootAncestor());
    }

    public function testGetChildDepth(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            CommentVote::class,
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );

        self::assertSame(0, $postAdmin->getChildDepth());
        self::assertSame(0, $commentAdmin->getChildDepth());
        self::assertSame(0, $commentVoteAdmin->getChildDepth());

        $postAdmin->addChild($commentAdmin, 'post');

        self::assertSame(0, $postAdmin->getChildDepth());
        self::assertSame(1, $commentAdmin->getChildDepth());
        self::assertSame(0, $commentVoteAdmin->getChildDepth());

        $commentAdmin->addChild($commentVoteAdmin, 'comment');

        self::assertSame(0, $postAdmin->getChildDepth());
        self::assertSame(1, $commentAdmin->getChildDepth());
        self::assertSame(2, $commentVoteAdmin->getChildDepth());
    }

    public function testGetCurrentLeafChildAdmin(): void
    {
        $postAdmin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            Comment::class,
            'Sonata\NewsBundle\Controller\CommentAdminController'
        );
        $commentVoteAdmin = new CommentVoteAdmin(
            'sonata.post.admin.comment_vote',
            CommentVote::class,
            'Sonata\NewsBundle\Controller\CommentVoteAdminController'
        );

        $postAdmin->addChild($commentAdmin, 'post');
        $commentAdmin->addChild($commentVoteAdmin, 'comment');

        // Workaround for static analysis
        $postAdminChildAdmin = $postAdmin->getCurrentLeafChildAdmin();

        self::assertNull($postAdminChildAdmin);
        self::assertNull($commentAdmin->getCurrentLeafChildAdmin());
        self::assertNull($commentVoteAdmin->getCurrentLeafChildAdmin());

        $commentAdmin->setCurrentChild(true);

        self::assertSame($commentAdmin, $postAdmin->getCurrentLeafChildAdmin());
        self::assertNull($commentAdmin->getCurrentLeafChildAdmin());
        self::assertNull($commentVoteAdmin->getCurrentLeafChildAdmin());

        $commentVoteAdmin->setCurrentChild(true);

        self::assertSame($commentVoteAdmin, $postAdmin->getCurrentLeafChildAdmin());
        self::assertSame($commentVoteAdmin, $commentAdmin->getCurrentLeafChildAdmin());
        self::assertNull($commentVoteAdmin->getCurrentLeafChildAdmin());
    }

    public function testAdminAvoidInifiniteLoop(): void
    {
        $this->expectNotToPerformAssertions();

        $registry = new FormRegistry([], new ResolvedFormTypeFactory());
        $formFactory = new FormFactory($registry);

        $admin = new AvoidInfiniteLoopAdmin('code', \stdClass::class, 'controller');
        $admin->setSubject(new \stdClass());

        $admin->setFormContractor(new FormContractor($formFactory, $registry));

        $admin->setShowBuilder(new ShowBuilder());

        $admin->setListBuilder(new ListBuilder());

        $pager = $this->createStub(PagerInterface::class);
        $proxyQuery = $this->createStub(ProxyQueryInterface::class);
        $admin->setDatagridBuilder(new DatagridBuilder($formFactory, $pager, $proxyQuery));

        $routeGenerator = $this->createStub(RouteGeneratorInterface::class);
        $routeGenerator->method('hasAdminRoute')->willReturn(false);
        $admin->setRouteGenerator($routeGenerator);

        $admin->getForm();
        $admin->getShow();
        $admin->getList();
        $admin->getDatagrid();
    }

    public function testGetDataSourceIterator(): void
    {
        $pager = $this->createStub(PagerInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $dataSource = $this->createMock(DataSourceInterface::class);
        $proxyQuery = $this->createStub(ProxyQueryInterface::class);
        $sourceIterator = $this->createStub(SourceIteratorInterface::class);

        $admin = new PostAdmin(
            'sonata.post.admin.post',
            Post::class,
            'Sonata\NewsBundle\Controller\PostAdminController'
        );

        $formFactory = new FormFactory(new FormRegistry([], new ResolvedFormTypeFactory()));
        $datagridBuilder = new DatagridBuilder($formFactory, $pager, $proxyQuery);

        $translator->method('trans')->willReturnCallback(static function (string $label): string {
            return sprintf('trans(%s)', $label);
        });

        $modelManager->expects(self::once())->method('getExportFields')->willReturn([
            'key' => 'field',
            'foo',
            'bar',
        ]);

        $dataSource
            ->expects(self::once())
            ->method('createIterator')
            ->with($proxyQuery, [
                'key' => 'field',
                'trans(export.label_foo)' => 'foo',
                'trans(export.label_bar)' => 'bar',
            ])
            ->willReturn($sourceIterator);

        $admin->setTranslator($translator);
        $admin->setDatagridBuilder($datagridBuilder);
        $admin->setModelManager($modelManager);
        $admin->setLabelTranslatorStrategy(new UnderscoreLabelTranslatorStrategy());
        $admin->setDataSource($dataSource);

        self::assertSame($sourceIterator, $admin->getDataSourceIterator());
    }
}
