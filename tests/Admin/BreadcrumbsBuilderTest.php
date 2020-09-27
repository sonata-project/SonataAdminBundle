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

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Comment;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\DummySubject;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This test class contains unit and integration tests. Maybe it could be
 * separated into two classes.
 *
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
class BreadcrumbsBuilderTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testGetBreadcrumbs(): void
    {
        $postAdminSubjectId = 42;
        $commentAdminSubjectId = 100500;

        $menuFactory = $this->getMockForAbstractClass(FactoryInterface::class);
        $menu = $this->getMockForAbstractClass(ItemInterface::class);
        $translatorStrategy = $this->getMockForAbstractClass(LabelTranslatorStrategyInterface::class);
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $routeGenerator = $this->getMockForAbstractClass(RouteGeneratorInterface::class);
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $pool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $menu
            ->method('addChild')
            ->willReturnCallback(static function () use ($menu): ItemInterface {
                return $menu;
            });

        $postAdmin = new PostAdmin('sonata.post.admin.post', DummySubject::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');

        $postAdmin->addChild($commentAdmin);
        $postAdmin->setRequest(new Request(['id' => $postAdminSubjectId]));
        $postAdmin->setModelManager($modelManager);

        $commentAdmin->setRequest(new Request(['childId' => $commentAdminSubjectId]));
        $commentAdmin->setModelManager($modelManager);

        $commentAdmin->initialize();
        $postAdmin->initialize();

        $commentAdmin->setCurrentChild(true);

        $container
            ->method('getParameter')
            ->with('sonata.admin.configuration.breadcrumbs')
            ->willReturn([]);

        $pool
            ->method('getContainer')
            ->willReturn($container);

        $postAdmin->setConfigurationPool($pool);
        $postAdmin->setMenuFactory($menuFactory);
        $postAdmin->setLabelTranslatorStrategy($translatorStrategy);
        $postAdmin->setRouteGenerator($routeGenerator);

        $commentAdmin->setLabelTranslatorStrategy($translatorStrategy);
        $commentAdmin->setRouteGenerator($routeGenerator);

        $modelManager
            ->method('find')
            ->willReturnCallback(static function (string $class, int $id) use ($postAdminSubjectId, $commentAdminSubjectId) {
                if (DummySubject::class === $class && $postAdminSubjectId === $id) {
                    return new DummySubject();
                }

                if (Comment::class === $class && $commentAdminSubjectId === $id) {
                    return new Comment();
                }

                throw new \Exception('Unexpected class and id combination');
            });

        $menuFactory->expects($this->exactly(5))
            ->method('createItem')
            ->with('root')
            ->willReturn($menu);

        $menu->expects($this->once())
            ->method('setUri')
            ->with($this->identicalTo(null));

        $menu->expects($this->exactly(5))
            ->method('getParent')
            ->willReturn(null);

        $routeGenerator->expects($this->exactly(5))
            ->method('generate')
            ->with('sonata_admin_dashboard')
            ->willReturn('http://somehost.com');

        $translatorStrategy->expects($this->exactly(10))
            ->method('getLabel')
            ->withConsecutive(
                ['DummySubject_list'],
                ['Comment_list'],
                ['DummySubject_list'],
                ['Comment_list'],
                ['DummySubject_list'],
                ['Comment_list'],
                ['DummySubject_list'],
                ['Comment_list'],
                ['DummySubject_list'],
                ['Comment_list'],
                ['Comment_edit'],
                ['DummySubject_list'],
                ['Comment_list'],
                ['DummySubject_list'],
                ['Comment_list']
            )
            ->will($this->onConsecutiveCalls(
                'someOtherLabel',
                'someInterestingLabel',
                'someFancyLabel',
                'someTipTopLabel',
                'someFunkyLabel',
                'someAwesomeLabel',
                'someMildlyInterestingLabel',
                'someWTFLabel',
                'someBadLabel',
                'someLongLabel',
                'someEndlessLabel',
                'someOriginalLabel',
                'someOkayishLabel'
            ));

        $menu->expects($this->exactly(24))
            ->method('addChild')
            ->withConsecutive(
                ['link_breadcrumb_dashboard'],
                ['someOtherLabel'],
                ['dummy subject representation'],
                ['someInterestingLabel'],
                ['this is a comment'],
                ['link_breadcrumb_dashboard'],
                ['someFancyLabel'],
                ['dummy subject representation'],
                ['someTipTopLabel'],
                ['this is a comment'],
                ['link_breadcrumb_dashboard'],
                ['someFunkyLabel'],
                ['dummy subject representation'],
                ['someAwesomeLabel'],
                ['this is a comment'],
                ['link_breadcrumb_dashboard'],
                ['someMildlyInterestingLabel'],
                ['dummy subject representation'],
                ['someWTFLabel'],
                ['link_breadcrumb_dashboard'],
                ['someBadLabel'],
                ['dummy subject representation'],
                ['someLongLabel'],
                ['this is a comment']
            )
            ->willReturn($menu);

        $postAdmin->getBreadcrumbs('repost');
        $postAdmin->setSubject(new DummySubject());
        $postAdmin->getBreadcrumbs('flag');

        $commentAdmin->setConfigurationPool($pool);
        $commentAdmin->getBreadcrumbs('edit');

        $commentAdmin->getBreadcrumbs('list');
        $commentAdmin->setSubject(new Comment());
        $commentAdmin->getBreadcrumbs('reply');
    }

    /**
     * @group legacy
     */
    public function testGetBreadcrumbsWithNoCurrentAdmin(): void
    {
        $postAdminSubjectId = 42;
        $commentAdminSubjectId = 100500;

        $menuFactory = $this->getMockForAbstractClass(FactoryInterface::class);
        $menu = $this->getMockForAbstractClass(ItemInterface::class);
        $menu
            ->method('getParent')
            ->willReturn(null);
        $translatorStrategy = $this->getMockForAbstractClass(LabelTranslatorStrategyInterface::class);
        $routeGenerator = $this->getMockForAbstractClass(RouteGeneratorInterface::class);
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $pool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $postAdmin = new PostAdmin('sonata.post.admin.post', DummySubject::class, 'Sonata\NewsBundle\Controller\PostAdminController');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'Sonata\NewsBundle\Controller\CommentAdminController');

        $postAdmin->addChild($commentAdmin);
        $postAdmin->setRequest(new Request(['id' => $postAdminSubjectId]));

        $commentAdmin->setRequest(new Request(['childId' => $commentAdminSubjectId]));

        $commentAdmin->setModelManager($modelManager);
        $postAdmin->setModelManager($modelManager);

        $commentAdmin->initialize();
        $postAdmin->initialize();

        $postAdmin->setMenuFactory($menuFactory);
        $postAdmin->setLabelTranslatorStrategy($translatorStrategy);
        $postAdmin->setRouteGenerator($routeGenerator);

        $menuFactory
            ->method('createItem')
            ->with('root')
            ->willReturn($menu);

        $translatorStrategy
            ->method('getLabel')
            ->withConsecutive(
                ['DummySubject_list'],
                ['DummySubject_repost'],
                ['DummySubject_list']
            )
            ->will($this->onConsecutiveCalls(
                'someOtherLabel',
                'someInterestingLabel',
                'someCoolLabel'
            ));

        $menu
            ->method('addChild')
            ->withConsecutive(
                ['link_breadcrumb_dashboard'],
                ['someOtherLabel'],
                ['someInterestingLabel'],
                ['link_breadcrumb_dashboard'],
                ['someCoolLabel'],
                ['dummy subject representation']
            )
            ->willReturn($menu);

        $container
            ->method('getParameter')
            ->with('sonata.admin.configuration.breadcrumbs')
            ->willReturn([]);

        $pool
            ->method('getContainer')
            ->willReturn($container);

        $postAdmin->setConfigurationPool($pool);

        $postAdmin->getBreadcrumbs('repost');
        $postAdmin->setSubject(new DummySubject());
        $flagBreadcrumb = $postAdmin->getBreadcrumbs('flag');
        $this->assertSame($flagBreadcrumb, $postAdmin->getBreadcrumbs('flag'));
    }

    public function testUnitChildGetBreadCrumbs(): void
    {
        $action = 'my_action';
        $breadcrumbsBuilder = new BreadcrumbsBuilder(['child_admin_route' => 'show']);
        $admin = $this->createStub(AbstractAdmin::class);
        $admin->method('isChild')->willReturn(false);

        $admin->method('getMenuFactory')->willReturn(new MenuFactory());
        $labelTranslatorStrategy = $this->createStub(LabelTranslatorStrategyInterface::class);

        $routeGenerator = $this->createStub(RouteGeneratorInterface::class);
        $routeGenerator->method('generate')->with('sonata_admin_dashboard')->willReturn('/dashboard');

        $admin->method('getRouteGenerator')->willReturn($routeGenerator);
        $labelTranslatorStrategy->method('getLabel')->willReturnMap([
            ['my_class_name_list', 'breadcrumb', 'link', 'My class'],
            ['my_child_class_name_list', 'breadcrumb', 'link', 'My child class'],
        ]);

        $childAdmin = $this->createMock(AbstractAdmin::class);
        $childAdmin->method('isChild')->willReturn(true);
        $childAdmin->method('getParent')->willReturn($admin);
        $childAdmin->method('getTranslationDomain')->willReturn('ChildBundle');
        $childAdmin->expects($this->atLeastOnce())->method('getLabelTranslatorStrategy')
            ->willReturn($labelTranslatorStrategy);
        $childAdmin->method('getClassnameLabel')->willReturn('my_child_class_name');
        $childAdmin->method('hasRoute')->with('list')->willReturn(true);
        $childAdmin->method('hasAccess')->with('list')->willReturn(true);
        $childAdmin->method('generateUrl')->with('list')->willReturn('/myadmin/my-object/mychildadmin/list');
        $childAdmin->method('getCurrentChildAdmin')->willReturn(null);
        $childAdmin->method('hasSubject')->willReturn(true);
        $childAdmin->method('getSubject')->willReturn('my subject');
        $childAdmin->method('toString')->with('my subject')->willReturn('My subject');

        $admin->method('hasRoute')->willReturnMap([
            ['show', true],
            ['list', true],
            ['edit', true],
        ]);
        $admin->method('hasAccess')->willReturnMap([
            ['show', 'my subject', true],
            ['list', null, true],
            ['edit', null, true],
        ]);
        $admin->method('generateUrl')->willReturnMap([
            ['show', ['id' => 'my-object'], UrlGeneratorInterface::ABSOLUTE_PATH, '/myadmin/my-object'],
            ['edit', ['id' => 'my-object'], UrlGeneratorInterface::ABSOLUTE_PATH, '/myadmin/my-object'],
            ['list', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/myadmin/list'],
        ]);

        $admin->method('trans')->with('My class', [], null)->willReturn('Ma classe');
        $admin->method('getCurrentChildAdmin')->willReturn($childAdmin);
        $request = $this->createStub(Request::class);
        $request->method('get')->with('slug')->willReturn('my-object');

        $admin->method('getIdParameter')->willReturn('slug');
        $admin->method('getRequest')->willReturn($request);
        $admin->method('hasSubject')->willReturn(true);
        $admin->method('getSubject')->willReturn('my subject');
        $admin->method('toString')->with('my subject')->willReturn('My subject');
        $admin->method('getTranslationDomain')->willReturn('FooBundle');
        $admin->method('getLabelTranslatorStrategy')->willReturn($labelTranslatorStrategy);
        $admin->method('getClassnameLabel')->willReturn('my_class_name');

        $breadcrumbs = $breadcrumbsBuilder->getBreadcrumbs($childAdmin, $action);
        $this->assertCount(5, $breadcrumbs);
        [
            $dashboardMenu,
            $adminListMenu,
            $adminSubjectMenu,
            $childMenu,
        ] = $breadcrumbs;

        self::assertSame('link_breadcrumb_dashboard', $dashboardMenu->getName());
        self::assertSame('/dashboard', $dashboardMenu->getUri());
        self::assertSame(
            ['translation_domain' => 'SonataAdminBundle'],
            $dashboardMenu->getExtras()
        );

        self::assertSame('My class', $adminListMenu->getName());
        self::assertSame('/myadmin/list', $adminListMenu->getUri());
        self::assertSame(
            ['translation_domain' => 'FooBundle'],
            $adminListMenu->getExtras()
        );

        self::assertSame('My subject', $adminSubjectMenu->getName());
        self::assertSame('/myadmin/my-object', $adminSubjectMenu->getUri());
        self::assertSame(
            [
                'translation_domain' => false,
                'safe_label' => false,
            ],
            $adminSubjectMenu->getExtras()
        );

        self::assertSame('My child class', $childMenu->getName());
        self::assertSame('/myadmin/my-object/mychildadmin/list', $childMenu->getUri());
        self::assertSame(
            ['translation_domain' => 'ChildBundle'],
            $childMenu->getExtras()
        );
    }

    public function actionProvider(): array
    {
        return [
            ['my_action'],
            ['list'],
            ['edit'],
            ['create'],
        ];
    }

    /**
     * @dataProvider actionProvider
     */
    public function testUnitBuildBreadcrumbs(string $action): void
    {
        $breadcrumbsBuilder = new BreadcrumbsBuilder();

        $menu = $this->createMock(ItemInterface::class);
        $menuFactory = $this->createStub(MenuFactory::class);
        $menuFactory->method('createItem')->with('root')->willReturn($menu);
        $admin = $this->createStub(AbstractAdmin::class);
        $admin->method('getMenuFactory')->willReturn($menuFactory);
        $labelTranslatorStrategy = $this->createStub(LabelTranslatorStrategyInterface::class);

        $routeGenerator = $this->createStub(RouteGeneratorInterface::class);
        $routeGenerator->method('generate')->with('sonata_admin_dashboard')->willReturn('/dashboard');
        $admin->method('getRouteGenerator')->willReturn($routeGenerator);

        $menu->method('addChild')->willReturnMap([
            ['link_breadcrumb_dashboard', [
                'uri' => '/dashboard',
                'extras' => ['translation_domain' => 'SonataAdminBundle'],
            ], $menu],
            ['create my object', [
                'extras' => ['translation_domain' => 'FooBundle'],
            ], $menu],
            ['My class', [
                'extras' => ['translation_domain' => 'FooBundle'],
                'uri' => '/myadmin/list',
            ], $menu],
            ['My subject', [
                'extras' => ['translation_domain' => false],
            ], $menu],
            ['My subject', [
                'uri' => null,
                'extras' => ['translation_domain' => false],
            ], $menu],
            ['My child class', [
                'extras' => ['translation_domain' => 'ChildBundle'],
                'uri' => null,
            ], $menu],
            ['My action', [
                'extras' => ['translation_domain' => 'ChildBundle'],
            ], $menu],
        ]);

        $menu->method('setExtra')->with('safe_label', false)->willReturn($menu);

        $labelTranslatorStrategy->method('getLabel')->willReturnMap([
            ['my_class_name_list', 'breadcrumb', 'link', 'My class'],
            ['my_child_class_name_list', 'breadcrumb', 'link', 'My child class'],
            ['my_child_class_name_my_action', 'breadcrumb', 'link', 'My action'],
            ['my_class_name_create', 'breadcrumb', 'link', 'create my object'],
        ]);

        $childAdmin = $this->createStub(AbstractAdmin::class);
        $childAdmin->method('getTranslationDomain')->willReturn('ChildBundle');
        $childAdmin->method('getLabelTranslatorStrategy')->willReturn($labelTranslatorStrategy);
        $childAdmin->method('getClassnameLabel')->willReturn('my_child_class_name');
        $childAdmin->method('hasRoute')->with('list')->willReturn(false);
        $childAdmin->method('getCurrentChildAdmin')->willReturn(null);
        $childAdmin->method('hasSubject')->willReturn(false);

        $admin->method('hasRoute')->willReturnMap([
            ['list', true],
            ['edit', false],
        ]);
        $admin->method('hasAccess')->with('list')->willReturn(true);
        $admin->method('generateUrl')->with('list')->willReturn('/myadmin/list');
        $admin->method('getCurrentChildAdmin')->willReturn('my_action' === $action ? $childAdmin : false);

        if ('list' === $action) {
            $admin->method('isChild')->willReturn(true);
            $menu->expects($this->once())->method('setUri')->with(null);
        } else {
            $menu->expects($this->never())->method('setUri');
        }

        $request = $this->createStub(Request::class);
        $request->method('get')->with('slug')->willReturn('my-object');

        $admin->method('getIdParameter')->willReturn('slug');
        $admin->method('getRequest')->willReturn($request);
        $admin->method('hasSubject')->willReturn(true);
        $admin->method('getSubject')->willReturn('my subject');
        $admin->method('toString')->with('my subject')->willReturn('My subject');
        $admin->method('getTranslationDomain')->willReturn('FooBundle');
        $admin->method('getLabelTranslatorStrategy')->willReturn(
            $labelTranslatorStrategy
        );
        $admin->method('getClassnameLabel')->willReturn('my_class_name');

        $breadcrumbsBuilder->buildBreadcrumbs($admin, $action);
    }
}
