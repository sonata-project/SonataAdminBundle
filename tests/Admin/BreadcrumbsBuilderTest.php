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

        $menu->expects($this->any())
            ->method('addChild')
            ->willReturnCallback(function () use ($menu) {
                return $menu;
            });

        $postAdmin = new PostAdmin('sonata.post.admin.post', DummySubject::class, 'SonataNewsBundle:PostAdmin');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'SonataNewsBundle:CommentAdmin');
        $subCommentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'SonataNewsBundle:CommentAdmin');

        $postAdmin->addChild($commentAdmin);
        $postAdmin->setRequest(new Request(['id' => $postAdminSubjectId]));
        $postAdmin->setModelManager($modelManager);

        $commentAdmin->setRequest(new Request(['childId' => $commentAdminSubjectId]));
        $commentAdmin->setModelManager($modelManager);

        $commentAdmin->initialize();
        $postAdmin->initialize();

        $commentAdmin->setCurrentChild($subCommentAdmin);

        $container->expects($this->any())
            ->method('getParameter')
            ->with('sonata.admin.configuration.breadcrumbs')
            ->will($this->returnValue([]));

        $pool->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($container));

        $postAdmin->setConfigurationPool($pool);
        $postAdmin->setMenuFactory($menuFactory);
        $postAdmin->setLabelTranslatorStrategy($translatorStrategy);
        $postAdmin->setRouteGenerator($routeGenerator);

        $commentAdmin->setLabelTranslatorStrategy($translatorStrategy);
        $commentAdmin->setRouteGenerator($routeGenerator);

        $modelManager->expects($this->any())
            ->method('find')
            ->willReturnCallback(function ($class, $id) use ($postAdminSubjectId, $commentAdminSubjectId) {
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
            ->will($this->returnValue($menu));

        $menu->expects($this->once())
            ->method('setUri')
            ->with($this->identicalTo(false));

        $menu->expects($this->exactly(5))
            ->method('getParent')
            ->will($this->returnValue(false));

        $routeGenerator->expects($this->exactly(5))
            ->method('generate')
            ->with('sonata_admin_dashboard')
            ->will($this->returnValue('http://somehost.com'));

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
            ->will($this->returnValue($menu));

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
        $translatorStrategy = $this->getMockForAbstractClass(LabelTranslatorStrategyInterface::class);
        $routeGenerator = $this->getMockForAbstractClass(RouteGeneratorInterface::class);
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $pool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $postAdmin = new PostAdmin('sonata.post.admin.post', DummySubject::class, 'SonataNewsBundle:PostAdmin');
        $commentAdmin = new CommentAdmin('sonata.post.admin.comment', Comment::class, 'SonataNewsBundle:CommentAdmin');

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

        $menuFactory->expects($this->any())
            ->method('createItem')
            ->with('root')
            ->will($this->returnValue($menu));

        $translatorStrategy->expects($this->any())
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

        $menu->expects($this->any())
            ->method('addChild')
            ->withConsecutive(
                ['link_breadcrumb_dashboard'],
                ['someOtherLabel'],
                ['someInterestingLabel'],

                ['link_breadcrumb_dashboard'],
                ['someCoolLabel'],
                ['dummy subject representation']
            )
            ->will($this->returnValue($menu));

        $container->expects($this->any())
            ->method('getParameter')
            ->with('sonata.admin.configuration.breadcrumbs')
            ->will($this->returnValue([]));

        $pool->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($container));

        $postAdmin->setConfigurationPool($pool);

        $postAdmin->getBreadcrumbs('repost');
        $postAdmin->setSubject(new DummySubject());
        $flagBreadcrumb = $postAdmin->getBreadcrumbs('flag');
        $this->assertSame($flagBreadcrumb, $postAdmin->getBreadcrumbs('flag'));
    }

    public function testUnitChildGetBreadCrumbs(): void
    {
        $menu = $this->prophesize(ItemInterface::class);
        $menu->getParent()->willReturn(null);

        $dashboardMenu = $this->prophesize(ItemInterface::class);
        $dashboardMenu->getParent()->willReturn($menu);

        $adminListMenu = $this->prophesize(ItemInterface::class);
        $adminListMenu->getParent()->willReturn($dashboardMenu);

        $adminSubjectMenu = $this->prophesize(ItemInterface::class);
        $adminSubjectMenu->getParent()->willReturn($adminListMenu);

        $childMenu = $this->prophesize(ItemInterface::class);
        $childMenu->getParent()->willReturn($adminSubjectMenu);

        $leafMenu = $this->prophesize(ItemInterface::class);
        $leafMenu->getParent()->willReturn($childMenu);

        $action = 'my_action';
        $breadcrumbsBuilder = new BreadcrumbsBuilder(['child_admin_route' => 'show']);
        $admin = $this->prophesize(AbstractAdmin::class);
        $admin->isChild()->willReturn(false);

        $menuFactory = $this->prophesize(MenuFactory::class);
        $menuFactory->createItem('root')->willReturn($menu);
        $admin->getMenuFactory()->willReturn($menuFactory);
        $labelTranslatorStrategy = $this->prophesize(
            LabelTranslatorStrategyInterface::class
        );

        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeGenerator->generate('sonata_admin_dashboard')->willReturn('/dashboard');

        $admin->getRouteGenerator()->willReturn($routeGenerator->reveal());
        $menu->addChild('link_breadcrumb_dashboard', [
            'uri' => '/dashboard',
            'extras' => [
                'translation_domain' => 'SonataAdminBundle',
            ],
        ])->willReturn(
            $dashboardMenu->reveal()
        );
        $labelTranslatorStrategy->getLabel(
            'my_class_name_list',
            'breadcrumb',
            'link'
        )->willReturn('My class');
        $labelTranslatorStrategy->getLabel(
            'my_child_class_name_list',
            'breadcrumb',
            'link'
        )->willReturn('My child class');
        $childAdmin = $this->prophesize(AbstractAdmin::class);
        $childAdmin->isChild()->willReturn(true);
        $childAdmin->getParent()->willReturn($admin->reveal());
        $childAdmin->getTranslationDomain()->willReturn('ChildBundle');
        $childAdmin->getLabelTranslatorStrategy()
            ->shouldBeCalled()
            ->willReturn($labelTranslatorStrategy->reveal());
        $childAdmin->getClassnameLabel()->willReturn('my_child_class_name');
        $childAdmin->hasRoute('list')->willReturn(true);
        $childAdmin->hasAccess('list')->willReturn(true);
        $childAdmin->generateUrl('list')->willReturn('/myadmin/my-object/mychildadmin/list');
        $childAdmin->getCurrentChildAdmin()->willReturn(null);
        $childAdmin->hasSubject()->willReturn(true);
        $childAdmin->getSubject()->willReturn('my subject');
        $childAdmin->toString('my subject')->willReturn('My subject');

        $admin->hasAccess('show', 'my subject')->willReturn(true)->shouldBeCalled();
        $admin->hasRoute('show')->willReturn(true);
        $admin->generateUrl('show', ['id' => 'my-object'])->willReturn('/myadmin/my-object');

        $admin->trans('My class', [], null)->willReturn('Ma classe');
        $admin->hasRoute('list')->willReturn(true);
        $admin->hasAccess('list')->willReturn(true);
        $admin->generateUrl('list')->willReturn('/myadmin/list');
        $admin->getCurrentChildAdmin()->willReturn($childAdmin->reveal());
        $request = $this->prophesize(Request::class);
        $request->get('slug')->willReturn('my-object');

        $admin->getIdParameter()->willReturn('slug');
        $admin->hasRoute('edit')->willReturn(true);
        $admin->hasAccess('edit')->willReturn(true);
        $admin->generateUrl('edit', ['id' => 'my-object'])->willReturn('/myadmin/my-object');
        $admin->getRequest()->willReturn($request->reveal());
        $admin->hasSubject()->willReturn(true);
        $admin->getSubject()->willReturn('my subject');
        $admin->toString('my subject')->willReturn('My subject');
        $admin->getTranslationDomain()->willReturn('FooBundle');
        $admin->getLabelTranslatorStrategy()->willReturn(
            $labelTranslatorStrategy->reveal()
        );
        $admin->getClassnameLabel()->willReturn('my_class_name');

        $dashboardMenu->addChild('My class', [
            'extras' => [
                'translation_domain' => 'FooBundle',
            ],
            'uri' => '/myadmin/list',
        ])->shouldBeCalled()->willReturn($adminListMenu->reveal());

        $adminListMenu->addChild('My subject', [
            'uri' => '/myadmin/my-object',
            'extras' => [
                'translation_domain' => false,
            ],
        ])->shouldBeCalled()->willReturn($adminSubjectMenu->reveal());

        $adminSubjectMenu->addChild('My child class', [
            'extras' => [
                'translation_domain' => 'ChildBundle',
            ],
            'uri' => '/myadmin/my-object/mychildadmin/list',
        ])->shouldBeCalled()->willReturn($childMenu->reveal());
        $adminSubjectMenu->setExtra('safe_label', false)->willReturn($childMenu);

        $childMenu->addChild('My subject', [
            'extras' => [
                'translation_domain' => false,
            ],
        ])->shouldBeCalled()->willReturn($leafMenu->reveal());

        $breadcrumbs = $breadcrumbsBuilder->getBreadcrumbs($childAdmin->reveal(), $action);
        $this->assertCount(5, $breadcrumbs);
    }

    public function actionProvider()
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
    public function testUnitBuildBreadcrumbs($action): void
    {
        $breadcrumbsBuilder = new BreadcrumbsBuilder();

        $menu = $this->prophesize(ItemInterface::class);
        $menuFactory = $this->prophesize(MenuFactory::class);
        $menuFactory->createItem('root')->willReturn($menu);
        $admin = $this->prophesize(AbstractAdmin::class);
        $admin->getMenuFactory()->willReturn($menuFactory);
        $labelTranslatorStrategy = $this->prophesize(LabelTranslatorStrategyInterface::class);

        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeGenerator->generate('sonata_admin_dashboard')->willReturn('/dashboard');
        $admin->getRouteGenerator()->willReturn($routeGenerator->reveal());
        $menu->addChild('link_breadcrumb_dashboard', [
            'uri' => '/dashboard',
            'extras' => [
                'translation_domain' => 'SonataAdminBundle',
            ],
        ])->willReturn(
            $menu->reveal()
        );
        $labelTranslatorStrategy->getLabel(
            'my_class_name_list',
            'breadcrumb',
            'link'
        )->willReturn('My class');
        $labelTranslatorStrategy->getLabel(
            'my_child_class_name_list',
            'breadcrumb',
            'link'
        )->willReturn('My child class');
        $labelTranslatorStrategy->getLabel(
            'my_child_class_name_my_action',
            'breadcrumb',
            'link'
        )->willReturn('My action');
        if ('create' == $action) {
            $labelTranslatorStrategy->getLabel(
                'my_class_name_create',
                'breadcrumb',
                'link'
            )->willReturn('create my object');
            $menu->addChild('create my object', [
                'extras' => [
                    'translation_domain' => 'FooBundle',
                ],
            ])->willReturn($menu);
        }
        $childAdmin = $this->prophesize(AbstractAdmin::class);
        $childAdmin->getTranslationDomain()->willReturn('ChildBundle');
        $childAdmin->getLabelTranslatorStrategy()->willReturn($labelTranslatorStrategy->reveal());
        $childAdmin->getClassnameLabel()->willReturn('my_child_class_name');
        $childAdmin->hasRoute('list')->willReturn(false);
        $childAdmin->getCurrentChildAdmin()->willReturn(null);
        $childAdmin->hasSubject()->willReturn(false);

        $admin->hasRoute('list')->willReturn(true);
        $admin->hasAccess('list')->willReturn(true);
        $admin->generateUrl('list')->willReturn('/myadmin/list');
        $admin->getCurrentChildAdmin()->willReturn(
            'my_action' == $action ? $childAdmin->reveal() : false
        );
        if ('list' == $action) {
            $admin->isChild()->willReturn(true);
            $menu->setUri(false)->shouldBeCalled();
        } else {
            $menu->setUri()->shouldNotBeCalled();
        }
        $request = $this->prophesize(Request::class);
        $request->get('slug')->willReturn('my-object');

        $admin->getIdParameter()->willReturn('slug');
        $admin->hasRoute('edit')->willReturn(false);
        $admin->getRequest()->willReturn($request->reveal());
        $admin->hasSubject()->willReturn(true);
        $admin->getSubject()->willReturn('my subject');
        $admin->toString('my subject')->willReturn('My subject');
        $admin->getTranslationDomain()->willReturn('FooBundle');
        $admin->getLabelTranslatorStrategy()->willReturn(
            $labelTranslatorStrategy->reveal()
        );
        $admin->getClassnameLabel()->willReturn('my_class_name');

        $menu->addChild('My class', [
            'uri' => '/myadmin/list',
            'extras' => [
                'translation_domain' => 'FooBundle',
            ],
        ])->willReturn($menu->reveal());
        $menu->addChild('My subject', [
            'extras' => [
                'translation_domain' => false,
            ],
        ])->willReturn($menu);
        $menu->addChild('My subject', [
            'uri' => null,
            'extras' => [
                'translation_domain' => false,
            ],
        ])->willReturn($menu);
        $menu->addChild('My child class', [
            'extras' => [
                'translation_domain' => 'ChildBundle',
            ],
            'uri' => null,
        ])->willReturn($menu);
        $menu->setExtra('safe_label', false)->willReturn($menu);
        $menu->addChild('My action', [
            'extras' => [
                'translation_domain' => 'ChildBundle',
            ],
        ])->willReturn($menu);

        $breadcrumbsBuilder->buildBreadCrumbs($admin->reveal(), $action);
    }
}
