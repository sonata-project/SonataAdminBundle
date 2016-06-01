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

use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;

/**
 * This test class contains unit and integration tests. Maybe it could be
 * separated into two classes.
 *
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
class BreadcrumbsBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testChildGetBreadCrumbs()
    {
        $menu = $this->prophesize('Knp\Menu\ItemInterface');
        $menu->getParent()->willReturn(null);

        $dashboardMenu = $this->prophesize('Knp\Menu\ItemInterface');
        $dashboardMenu->getParent()->willReturn($menu);

        $adminListMenu = $this->prophesize('Knp\Menu\ItemInterface');
        $adminListMenu->getParent()->willReturn($dashboardMenu);

        $adminSubjectMenu = $this->prophesize('Knp\Menu\ItemInterface');
        $adminSubjectMenu->getParent()->willReturn($adminListMenu);

        $childMenu = $this->prophesize('Knp\Menu\ItemInterface');
        $childMenu->getParent()->willReturn($adminSubjectMenu);

        $leafMenu = $this->prophesize('Knp\Menu\ItemInterface');
        $leafMenu->getParent()->willReturn($childMenu);

        $action = 'my_action';
        $breadcrumbsBuilder = new BreadcrumbsBuilder();
        $admin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $admin->isChild()->willReturn(false);

        $menuFactory = $this->prophesize('Knp\Menu\MenuFactory');
        $menuFactory->createItem('root')->willReturn($menu);
        $admin->getMenuFactory()->willReturn($menuFactory);
        $labelTranslatorStrategy = $this->prophesize(
            'Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface'
        );
        $labelTranslatorStrategy->getLabel(
            'dashboard',
            'breadcrumb',
            'link'
        )->willReturn('Dashboard');
        $admin->trans('Dashboard', array(), 'SonataAdminBundle')->willReturn(
            'Tableau de bord'
        );

        $routeGenerator = $this->prophesize('Sonata\AdminBundle\Route\RouteGeneratorInterface');
        $routeGenerator->generate('sonata_admin_dashboard')->willReturn('/dashboard');

        $admin->getRouteGenerator()->willReturn($routeGenerator->reveal());
        $menu->addChild('Tableau de bord', array('uri' => '/dashboard'))->willReturn(
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
        $childAdmin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $childAdmin->isChild()->willReturn(true);
        $childAdmin->getParent()->willReturn($admin->reveal());
        $childAdmin->getLabelTranslatorStrategy()
            ->shouldBeCalled()
            ->willReturn($labelTranslatorStrategy->reveal());
        $childAdmin->getClassnameLabel()->willReturn('my_child_class_name');
        $childAdmin->hasRoute('list')->willReturn(true);
        $childAdmin->isGranted('LIST')->willReturn(true);
        $childAdmin->generateUrl('list')->willReturn('/myadmin/my-object/mychildadmin/list');
        $childAdmin->trans('My child class', array(), null)->willReturn('Ma classe fille');
        $childAdmin->getCurrentChildAdmin()->willReturn(null);
        $childAdmin->hasSubject()->willReturn(true);
        $childAdmin->getSubject()->willReturn('my subject');
        $childAdmin->toString('my subject')->willReturn('My subject');

        $admin->trans('My class', array(), null)->willReturn('Ma classe');
        $admin->hasRoute('list')->willReturn(true);
        $admin->isGranted('LIST')->willReturn(true);
        $admin->generateUrl('list')->willReturn('/myadmin/list');
        $admin->getCurrentChildAdmin()->willReturn($childAdmin->reveal());
        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');
        $request->get('slug')->willReturn('my-object');

        $admin->getIdParameter()->willReturn('slug');
        $admin->hasRoute('edit')->willReturn(true);
        $admin->isGranted('EDIT')->willReturn(true);
        $admin->generateUrl('edit', array('id' => 'my-object'))->willReturn('/myadmin/my-object');
        $admin->getRequest()->willReturn($request->reveal());
        $admin->hasSubject()->willReturn(true);
        $admin->getSubject()->willReturn('my subject');
        $admin->toString('my subject')->willReturn('My subject');
        $admin->getLabelTranslatorStrategy()->willReturn(
            $labelTranslatorStrategy->reveal()
        );
        $admin->getClassnameLabel()->willReturn('my_class_name');

        $dashboardMenu->addChild('Ma classe', array(
            'uri' => '/myadmin/list',
        ))->shouldBeCalled()->willReturn($adminListMenu->reveal());

        $adminListMenu->addChild('My subject', array(
            'uri' => '/myadmin/my-object',
        ))->shouldBeCalled()->willReturn($adminSubjectMenu->reveal());

        $adminSubjectMenu->addChild('Ma classe fille', array(
            'uri' => '/myadmin/my-object/mychildadmin/list',
        ))->shouldBeCalled()->willReturn($childMenu->reveal());

        $childMenu->addChild('My subject')
            ->shouldBeCalled()->willReturn($leafMenu->reveal());

        $breadcrumbs = $breadcrumbsBuilder->getBreadcrumbs($childAdmin->reveal(), $action);
        $this->assertCount(5, $breadcrumbs);
    }

    public function actionProvider()
    {
        return array(
            array('my_action'),
            array('list'),
            array('edit'),
            array('create'),
        );
    }

    /**
     * @dataProvider actionProvider
     */
    public function testBuildBreadcrumbs($action)
    {
        $breadcrumbsBuilder = new BreadcrumbsBuilder();

        $menu = $this->prophesize('Knp\Menu\ItemInterface');
        $menuFactory = $this->prophesize('Knp\Menu\MenuFactory');
        $menuFactory->createItem('root')->willReturn($menu);
        $admin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $admin->getMenuFactory()->willReturn($menuFactory);
        $labelTranslatorStrategy = $this->prophesize(
            'Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface'
        );
        $labelTranslatorStrategy->getLabel(
            'dashboard',
            'breadcrumb',
            'link'
        )->willReturn('Dashboard');
        $admin->trans('Dashboard', array(), 'SonataAdminBundle')->willReturn(
            'Tableau de bord'
        );

        $routeGenerator = $this->prophesize('Sonata\AdminBundle\Route\RouteGeneratorInterface');
        $routeGenerator->generate('sonata_admin_dashboard')->willReturn('/dashboard');
        $admin->getRouteGenerator()->willReturn($routeGenerator->reveal());
        $menu->addChild('Tableau de bord', array('uri' => '/dashboard'))->willReturn(
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
        if ($action == 'create') {
            $labelTranslatorStrategy->getLabel(
                'my_class_name_create',
                'breadcrumb',
                'link'
            )->willReturn('create my object');
            $admin->trans('create my object', array(), null)->willReturn('Créer mon objet')->shouldBeCalled();
            $menu->addChild('Créer mon objet', array())->willReturn($menu);
        }
        $childAdmin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $childAdmin->getLabelTranslatorStrategy()->willReturn($labelTranslatorStrategy->reveal());
        $childAdmin->getClassnameLabel()->willReturn('my_child_class_name');
        $childAdmin->hasRoute('list')->willReturn(false);
        $childAdmin->trans('My child class', array(), null)->willReturn('Ma classe fille');
        $childAdmin->trans('My action', array(), null)->willReturn('Mon action');
        $childAdmin->getCurrentChildAdmin()->willReturn(null);
        $childAdmin->hasSubject()->willReturn(false);

        $admin->trans('My class', array(), null)->willReturn('Ma classe');
        $admin->hasRoute('list')->willReturn(true);
        $admin->isGranted('LIST')->willReturn(true);
        $admin->generateUrl('list')->willReturn('/myadmin/list');
        $admin->getCurrentChildAdmin()->willReturn(
            $action == 'my_action' ? $childAdmin->reveal() : false
        );
        if ($action == 'list') {
            $admin->isChild()->willReturn(true);
            $menu->setUri(false)->shouldBeCalled();
        }
        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');
        $request->get('slug')->willReturn('my-object');

        $admin->getIdParameter()->willReturn('slug');
        $admin->hasRoute('edit')->willReturn(false);
        $admin->getRequest()->willReturn($request->reveal());
        $admin->hasSubject()->willReturn(true);
        $admin->getSubject()->willReturn('my subject');
        $admin->toString('my subject')->willReturn('My subject');
        $admin->getLabelTranslatorStrategy()->willReturn(
            $labelTranslatorStrategy->reveal()
        );
        $admin->getClassnameLabel()->willReturn('my_class_name');

        $menu->addChild('Ma classe', array(
            'uri' => '/myadmin/list',
        ))->willReturn($menu->reveal());
        $menu->addChild('My subject')->willReturn($menu);
        $menu->addChild('My subject', array('uri' => null))->willReturn($menu);
        $menu->addChild('Ma classe fille', array('uri' => null))->willReturn($menu);
        $menu->addChild('Mon action', array())->willReturn($menu);

        $breadcrumbsBuilder->buildBreadCrumbs($admin->reveal(), $action);
    }
}
