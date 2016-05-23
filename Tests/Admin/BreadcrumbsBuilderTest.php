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
    public function testGetBreadcrumbs()
    {
        $class = 'Application\Sonata\NewsBundle\Entity\Post';
        $baseControllerName = 'SonataNewsBundle:PostAdmin';

        $admin = new PostAdmin('sonata.post.admin.post', $class, $baseControllerName);
        $commentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
            'SonataNewsBundle:CommentAdmin'
        );
        $subCommentAdmin = new CommentAdmin(
            'sonata.post.admin.comment',
            'Application\Sonata\NewsBundle\Entity\Comment',
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

        $admin->setMenuFactory($menuFactory);
        $admin->setLabelTranslatorStrategy($translatorStrategy);
        $admin->setRouteGenerator($routeGenerator);
        $admin->setModelManager($modelManager);

        $commentAdmin->setLabelTranslatorStrategy($translatorStrategy);
        $commentAdmin->setRouteGenerator($routeGenerator);

        $modelManager->expects($this->exactly(1))
            ->method('find')
            ->with('Application\Sonata\NewsBundle\Entity\Post', 42)
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
                array('Post_list'),
                array('Comment_list'),
                array('Comment_repost'),

                array('dashboard'),
                array('Post_list'),
                array('Comment_list'),
                array('Comment_flag'),

                array('dashboard'),
                array('Post_list'),
                array('Comment_list'),
                array('Comment_edit'),

                array('dashboard'),
                array('Post_list'),
                array('Comment_list'),

                array('dashboard'),
                array('Post_list'),
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
                array('dummy subject representation')
            )
            ->will($this->returnValue($menu));

        $admin->getBreadcrumbs('repost');
        $admin->setSubject(new DummySubject());
        $admin->getBreadcrumbs('flag');

        $commentAdmin->getBreadcrumbs('edit');

        $commentAdmin->getBreadcrumbs('list');
        $commentAdmin->setSubject(new DummySubject());
        $commentAdmin->getBreadcrumbs('reply');
    }

    public function testGetBreadcrumbsWithNoCurrentAdmin()
    {
        $class = 'Application\Sonata\NewsBundle\Entity\Post';
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

        $menuFactory->expects($this->exactly(2))
            ->method('createItem')
            ->with('root')
            ->will($this->returnValue($menu));

        $translatorStrategy->expects($this->exactly(5))
            ->method('getLabel')
            ->withConsecutive(
                array('dashboard'),
                array('Post_list'),
                array('Post_repost'),

                array('dashboard'),
                array('Post_list')
            )
            ->will($this->onConsecutiveCalls(
                'someLabel',
                'someOtherLabel',
                'someInterestingLabel',
                'someFancyLabel',
                'someCoolLabel'
            ));

        $menu->expects($this->exactly(6))
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

        $admin->getBreadcrumbs('repost');
        $admin->setSubject(new DummySubject());
        $flagBreadcrumb = $admin->getBreadcrumbs('flag');
        $this->assertSame($flagBreadcrumb, $admin->getBreadcrumbs('flag'));
    }

    public function testUnitGetBreadCrumbs()
    {
        $root = $this->prophesize('Knp\Menu\ItemInterface');
        $breadcrumbs = $this->prophesize('Knp\Menu\ItemInterface');
        $breadcrumbs->getParent()->willreturn($root);
        $breadcrumbs = $breadcrumbs->reveal();
        $action = 'my_action';
        $admin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $admin->isChild()->willReturn(false);
        $admin->buildBreadcrumbs($action)->willReturn($breadcrumbs);
        $breadcrumbsBuilder = new BreadcrumbsBuilder($admin->reveal());
        $this->assertSame(array($breadcrumbs), $breadcrumbsBuilder->getBreadcrumbs($action));
    }

    public function testUnitChildGetBreadCrumbs()
    {
        $parentMenu = 'parent menu';

        $action = 'my_action';
        $parentAdmin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $parentAdmin->getBreadcrumbs($action)->willReturn($parentMenu);
        $admin = $this->prophesize('Sonata\AdminBundle\Admin\AbstractAdmin');
        $admin->isChild()->willReturn(true);
        $admin->getParent()->willReturn($parentAdmin->reveal());
        $breadcrumbsBuilder = new BreadcrumbsBuilder($admin->reveal());

        $this->assertSame(
            'parent menu',
            $breadcrumbsBuilder->getBreadcrumbs($action)
        );
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

        $breadcrumbsBuilder = new BreadcrumbsBuilder($admin->reveal());
        $breadcrumbsBuilder->buildBreadCrumbs($action);
    }
}
