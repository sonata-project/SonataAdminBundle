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

use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * This test class contains unit and integration tests. Maybe it could be
 * separated into two classes.
 *
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
class BreadcrumbsBuilderTest extends TestCase
{
    public function testChildGetBreadCrumbs(): void
    {
        $subject = new \stdClass();

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
        $childAdmin->getSubject()->willReturn($subject);
        $childAdmin->toString($subject)->willReturn('My subject');

        $admin->hasAccess('show', $subject)->willReturn(true)->shouldBeCalled();
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
        $admin->getSubject()->willReturn($subject);
        $admin->toString($subject)->willReturn('My subject');
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
    public function testBuildBreadcrumbs(string $action): void
    {
        $subject = new \stdClass();

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
        if ('create' === $action) {
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
            'my_action' === $action ? $childAdmin->reveal() : null
        );
        if ('list' === $action) {
            $admin->isChild()->willReturn(true);
            $menu->setUri(null)->shouldBeCalled();
        } else {
            $menu->setUri()->shouldNotBeCalled();
        }
        $request = $this->prophesize(Request::class);
        $request->get('slug')->willReturn('my-object');

        $admin->getIdParameter()->willReturn('slug');
        $admin->hasRoute('edit')->willReturn(false);
        $admin->getRequest()->willReturn($request->reveal());
        $admin->hasSubject()->willReturn(true);
        $admin->getSubject()->willReturn($subject);
        $admin->toString($subject)->willReturn('My subject');
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

        $reflection = new \ReflectionMethod('Sonata\AdminBundle\Admin\BreadcrumbsBuilder', 'buildBreadcrumbs');
        $reflection->setAccessible(true);

        $reflection->invoke($breadcrumbsBuilder, $admin->reveal(), $action);
    }
}
