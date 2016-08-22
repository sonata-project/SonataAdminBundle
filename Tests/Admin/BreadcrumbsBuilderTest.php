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
use Sonata\AdminBundle\Tests\Fixtures\Admin\CommentAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Comment;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\DummySubject;
use Symfony\Component\HttpFoundation\Request;

/**
 * This test class contains unit and integration tests. Maybe it could be
 * separated into two classes.
 *
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
class BreadcrumbsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group legacy
     */
    public function testGetBreadcrumbs()
    {
        $class = 'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\DummySubject';
        $baseControllerName = 'SonataNewsBundle:PostAdmin';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Comment',
            'SonataNewsBundle:CommentAdmin'
        );
        $subCommentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Comment',
            'SonataNewsBundle:CommentAdmin'
        );
        $admin->addChild($commentAdmin);
        $admin->setRequest(new Request(array('id' => 42)));
        $commentAdmin->setRequest(new Request());
        $commentAdmin->initialize();
        $admin->initialize();
        $commentAdmin->setCurrentChild($subCommentAdmin);

        $menuFactory = $this->getMock('Knp\Menu\FactoryInterface');
        $menu = $this->getMock('Knp\Menu\ItemInterface');
        $translatorStrategy = $this->getMock(
            'Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface'
        );
        $routeGenerator = $this->getMock(
            'Sonata\AdminBundle\Route\RouteGeneratorInterface'
        );
        $modelManager = $this->getMock(
            'Sonata\AdminBundle\Model\ModelManagerInterface'
        );

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())
            ->method('getParameter')
            ->with('sonata.admin.configuration.breadcrumbs')
            ->will($this->returnValue(array()));
        $pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
            ->disableOriginalConstructor()
            ->getMock();
        $pool->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($container));

        $admin->setConfigurationPool($pool);
        $admin->setMenuFactory($menuFactory);
        $admin->setLabelTranslatorStrategy($translatorStrategy);
        $admin->setRouteGenerator($routeGenerator);
        $admin->setModelManager($modelManager);

        $commentAdmin->setLabelTranslatorStrategy($translatorStrategy);
        $commentAdmin->setRouteGenerator($routeGenerator);

        $modelManager->expects($this->exactly(1))
            ->method('find')
            ->with('Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\DummySubject', 42)
            ->will($this->returnValue(new DummySubject()));

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

        $translatorStrategy->expects($this->exactly(18))
            ->method('getLabel')
            ->withConsecutive(
                array('dashboard'),
                array('DummySubject_list'),
                array('Comment_list'),
                array('Comment_repost'),

                array('dashboard'),
                array('DummySubject_list'),
                array('Comment_list'),
                array('Comment_flag'),

                array('dashboard'),
                array('DummySubject_list'),
                array('Comment_list'),
                array('Comment_edit'),

                array('dashboard'),
                array('DummySubject_list'),
                array('Comment_list'),

                array('dashboard'),
                array('DummySubject_list'),
                array('Comment_list')
            )
            ->will($this->onConsecutiveCalls(
                'someLabel',
                'someOtherLabel',
                'someInterestingLabel',
                'someFancyLabel',

                'someCoolLabel',
                'someTipTopLabel',
                'someFunkyLabel',
                'someAwesomeLabel',

                'someLikeableLabel',
                'someMildlyInterestingLabel',
                'someWTFLabel',
                'someBadLabel',

                'someBoringLabel',
                'someLongLabel',
                'someEndlessLabel',

                'someAlmostThereLabel',
                'someOriginalLabel',
                'someOkayishLabel'
            ));

        $menu->expects($this->exactly(24))
            ->method('addChild')
            ->withConsecutive(
                array('someLabel'),
                array('someOtherLabel'),
                array('dummy subject representation'),
                array('someInterestingLabel'),
                array('someFancyLabel'),

                array('someCoolLabel'),
                array('someTipTopLabel'),
                array('dummy subject representation'),
                array('someFunkyLabel'),
                array('someAwesomeLabel'),

                array('someLikeableLabel'),
                array('someMildlyInterestingLabel'),
                array('dummy subject representation'),
                array('someWTFLabel'),
                array('someBadLabel'),

                array('someBoringLabel'),
                array('someLongLabel'),
                array('dummy subject representation'),
                array('someEndlessLabel'),

                array('someAlmostThereLabel'),
                array('someOriginalLabel'),
                array('dummy subject representation'),
                array('someOkayishLabel'),
                array('this is a comment')
            )
            ->will($this->returnValue($menu));

        $admin->getBreadcrumbs('repost');
        $admin->setSubject(new DummySubject());
        $admin->getBreadcrumbs('flag');

        $commentAdmin->setConfigurationPool($pool);
        $commentAdmin->getBreadcrumbs('edit');

        $commentAdmin->getBreadcrumbs('list');
        $commentAdmin->setSubject(new Comment());
        $commentAdmin->getBreadcrumbs('reply');
    }

    /**
     * @group legacy
     */
    public function testGetBreadcrumbsWithNoCurrentAdmin()
    {
        $class = 'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\DummySubject';
        $baseControllerName = 'SonataNewsBundle:PostAdmin';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'SonataNewsBundle:CommentAdmin'
        );
        $admin->addChild($commentAdmin);
        $admin->setRequest(new Request(array('id' => 42)));
        $commentAdmin->setRequest(new Request());
        $commentAdmin->initialize();
        $admin->initialize();

        $menuFactory = $this->getMock('Knp\Menu\FactoryInterface');
        $menu = $this->getMock('Knp\Menu\ItemInterface');
        $translatorStrategy = $this->getMock(
            'Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface'
        );
        $routeGenerator = $this->getMock(
            'Sonata\AdminBundle\Route\RouteGeneratorInterface'
        );

        $admin->setMenuFactory($menuFactory);
        $admin->setLabelTranslatorStrategy($translatorStrategy);
        $admin->setRouteGenerator($routeGenerator);

        $menuFactory->expects($this->any())
            ->method('createItem')
            ->with('root')
            ->will($this->returnValue($menu));

        $translatorStrategy->expects($this->any())
            ->method('getLabel')
            ->withConsecutive(
                array('dashboard'),
                array('DummySubject_list'),
                array('DummySubject_repost'),

                array('dashboard'),
                array('DummySubject_list')
            )
            ->will($this->onConsecutiveCalls(
                'someLabel',
                'someOtherLabel',
                'someInterestingLabel',
                'someFancyLabel',
                'someCoolLabel'
            ));

        $menu->expects($this->any())
            ->method('addChild')
            ->withConsecutive(
                array('someLabel'),
                array('someOtherLabel'),
                array('someInterestingLabel'),
                array('someFancyLabel'),

                array('someCoolLabel'),
                array('dummy subject representation')
            )
            ->will($this->returnValue($menu));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())
            ->method('getParameter')
            ->with('sonata.admin.configuration.breadcrumbs')
            ->will($this->returnValue(array()));
        $pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')
            ->disableOriginalConstructor()
            ->getMock();
        $pool->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($container));

        $admin->setConfigurationPool($pool);

        $admin->getBreadcrumbs('repost');
        $admin->setSubject(new DummySubject());
        $flagBreadcrumb = $admin->getBreadcrumbs('flag');
        $this->assertSame($flagBreadcrumb, $admin->getBreadcrumbs('flag'));
    }

    public function testUnitChildGetBreadCrumbs()
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
        $breadcrumbsBuilder = new BreadcrumbsBuilder(array('child_admin_route' => 'show'));
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

        $routeGenerator = $this->prophesize('Sonata\AdminBundle\Route\RouteGeneratorInterface');
        $routeGenerator->generate('sonata_admin_dashboard')->willReturn('/dashboard');

        $admin->getRouteGenerator()->willReturn($routeGenerator->reveal());
        $menu->addChild('Dashboard', array(
            'uri' => '/dashboard',
            'extras' => array(
                'translation_domain' => 'SonataAdminBundle',
            ),
        ))->willReturn(
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
        $childAdmin->getTranslationDomain()->willReturn('ChildBundle');
        $childAdmin->getLabelTranslatorStrategy()
            ->shouldBeCalled()
            ->willReturn($labelTranslatorStrategy->reveal());
        $childAdmin->getClassnameLabel()->willReturn('my_child_class_name');
        $childAdmin->hasRoute('list')->willReturn(true);
        $childAdmin->isGranted('LIST')->willReturn(true);
        $childAdmin->generateUrl('list')->willReturn('/myadmin/my-object/mychildadmin/list');
        $childAdmin->getCurrentChildAdmin()->willReturn(null);
        $childAdmin->hasSubject()->willReturn(true);
        $childAdmin->getSubject()->willReturn('my subject');
        $childAdmin->toString('my subject')->willReturn('My subject');

        $admin->hasAccess('show', 'my subject')->willReturn(true)->shouldBeCalled();
        $admin->hasRoute('show')->willReturn(true);
        $admin->generateUrl('show', array('id' => 'my-object'))->willReturn('/myadmin/my-object');

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
        $admin->getTranslationDomain()->willReturn('FooBundle');
        $admin->getLabelTranslatorStrategy()->willReturn(
            $labelTranslatorStrategy->reveal()
        );
        $admin->getClassnameLabel()->willReturn('my_class_name');

        $dashboardMenu->addChild('My class', array(
            'extras' => array(
                'translation_domain' => 'FooBundle',
            ),
            'uri' => '/myadmin/list',
        ))->shouldBeCalled()->willReturn($adminListMenu->reveal());

        $adminListMenu->addChild('My subject', array(
            'uri' => '/myadmin/my-object',
            'extras' => array(
                'translation_domain' => false,
            ),
        ))->shouldBeCalled()->willReturn($adminSubjectMenu->reveal());

        $adminSubjectMenu->addChild('My child class', array(
            'extras' => array(
                'translation_domain' => 'ChildBundle',
            ),
            'uri' => '/myadmin/my-object/mychildadmin/list',
        ))->shouldBeCalled()->willReturn($childMenu->reveal());
        $adminSubjectMenu->setExtra('safe_label', false)->willReturn($childMenu);

        $childMenu->addChild('My subject', array(
            'extras' => array(
                'translation_domain' => false,
            ),
        ))->shouldBeCalled()->willReturn($leafMenu->reveal());

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
    public function testUnitBuildBreadcrumbs($action)
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

        $routeGenerator = $this->prophesize('Sonata\AdminBundle\Route\RouteGeneratorInterface');
        $routeGenerator->generate('sonata_admin_dashboard')->willReturn('/dashboard');
        $admin->getRouteGenerator()->willReturn($routeGenerator->reveal());
        $menu->addChild('Dashboard', array(
            'uri' => '/dashboard',
            'extras' => array(
                'translation_domain' => 'SonataAdminBundle',
            ),
        ))->willReturn(
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
            $menu->addChild('create my object', array(
                'extras' => array(
                    'translation_domain' => 'FooBundle',
                ),
            ))->willReturn($menu);
        }
        $childAdmin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $childAdmin->getTranslationDomain()->willReturn('ChildBundle');
        $childAdmin->getLabelTranslatorStrategy()->willReturn($labelTranslatorStrategy->reveal());
        $childAdmin->getClassnameLabel()->willReturn('my_child_class_name');
        $childAdmin->hasRoute('list')->willReturn(false);
        $childAdmin->getCurrentChildAdmin()->willReturn(null);
        $childAdmin->hasSubject()->willReturn(false);

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
        $admin->getTranslationDomain()->willReturn('FooBundle');
        $admin->getLabelTranslatorStrategy()->willReturn(
            $labelTranslatorStrategy->reveal()
        );
        $admin->getClassnameLabel()->willReturn('my_class_name');

        $menu->addChild('My class', array(
            'uri' => '/myadmin/list',
            'extras' => array(
                'translation_domain' => 'FooBundle',
            ),
        ))->willReturn($menu->reveal());
        $menu->addChild('My subject', array(
            'extras' => array(
                'translation_domain' => false,
            ),
        ))->willReturn($menu);
        $menu->addChild('My subject', array(
            'uri' => null,
            'extras' => array(
                'translation_domain' => false,
            ),
        ))->willReturn($menu);
        $menu->addChild('My child class', array(
            'extras' => array(
                'translation_domain' => 'ChildBundle',
            ),
            'uri' => null,
        ))->willReturn($menu);
        $menu->setExtra('safe_label', false)->willReturn($menu);
        $menu->addChild('My action', array(
            'extras' => array(
                'translation_domain' => 'ChildBundle',
            ),
        ))->willReturn($menu);

        $breadcrumbsBuilder->buildBreadCrumbs($admin->reveal(), $action);
    }
}
