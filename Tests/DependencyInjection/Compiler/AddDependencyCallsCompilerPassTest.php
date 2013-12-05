<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author     Tiago Garcia
 */
class AddDependencyCallsCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /** @var SonataAdminExtension $extension */
    private $extension;

    /** @var array $config */
    private $config;

    public function setUp()
    {
        parent::setUp();

        $this->extension = new SonataAdminExtension();
        $this->config    = $this->getConfig();
    }

    /**
     * @covers Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass::process
     */
    public function testProcessParsingFullValidConfig()
    {
        $container = $this->getContainer();
        $this->extension->load(array($this->config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $compilerPass->process($container);
        $container->compile();

        $this->assertTrue($container->hasParameter('sonata.admin.configuration.dashboard_groups'));

        $dashboardGroupsSettings = $container->getParameter('sonata.admin.configuration.dashboard_groups');

        $this->assertArrayHasKey('sonata_group_one', $dashboardGroupsSettings);

        $this->assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']);
        $this->assertArrayHasKey('label_catalogue', $dashboardGroupsSettings['sonata_group_one']);
        $this->assertArrayHasKey('items', $dashboardGroupsSettings['sonata_group_one']);
        $this->assertArrayHasKey('item_adds', $dashboardGroupsSettings['sonata_group_one']);
        $this->assertArrayHasKey('roles', $dashboardGroupsSettings['sonata_group_one']);

        $this->assertEquals('Group One Label', $dashboardGroupsSettings['sonata_group_one']['label']);
        $this->assertEquals('SonataAdminBundle', $dashboardGroupsSettings['sonata_group_one']['label_catalogue']);
        $this->assertContains('sonata_post_admin', $dashboardGroupsSettings['sonata_group_one']['items']);
        $this->assertContains('sonata_news_admin', $dashboardGroupsSettings['sonata_group_one']['item_adds']);
        $this->assertContains('ROLE_ONE', $dashboardGroupsSettings['sonata_group_one']['roles']);
    }

    /**
     * @covers Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass::process
     */
    public function testProcessResultingConfig()
    {
        $container = $this->getContainer();
        $this->extension->load(array($this->config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $compilerPass->process($container);
        $container->compile();

        $this->assertTrue($container->hasDefinition('sonata.admin.pool'));
        $this->assertTrue($container->hasDefinition('sonata_post_admin'));
        $this->assertTrue($container->hasDefinition('sonata_article_admin'));
        $this->assertTrue($container->hasDefinition('sonata_news_admin'));

        $pool = $container->get('sonata.admin.pool');
        $adminServiceIds = $pool->getAdminServiceIds();
        $adminGroups = $pool->getAdminGroups();
        $adminClasses = $pool->getAdminClasses();

        $this->assertContains('sonata_post_admin', $adminServiceIds);
        $this->assertContains('sonata_article_admin', $adminServiceIds);
        $this->assertContains('sonata_news_admin', $adminServiceIds);

        $this->assertArrayHasKey('sonata_group_one', $adminGroups);
        $this->assertArrayHasKey('label', $adminGroups['sonata_group_one']);
        $this->assertArrayHasKey('label_catalogue', $adminGroups['sonata_group_one']);
        $this->assertArrayHasKey('items', $adminGroups['sonata_group_one']);
        $this->assertArrayHasKey('item_adds', $adminGroups['sonata_group_one']);
        $this->assertArrayHasKey('roles', $adminGroups['sonata_group_one']);
        $this->assertEquals('Group One Label', $adminGroups['sonata_group_one']['label']);
        $this->assertEquals('SonataAdminBundle', $adminGroups['sonata_group_one']['label_catalogue']);
        $this->assertContains('sonata_post_admin', $adminGroups['sonata_group_one']['items']);
        $this->assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['items']);
        $this->assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['item_adds']);
        $this->assertFalse(in_array('sonata_article_admin', $adminGroups['sonata_group_one']['items']));
        $this->assertContains('ROLE_ONE', $adminGroups['sonata_group_one']['roles']);

        $this->assertArrayHasKey('Sonata\AdminBundle\Tests\DependencyInjection\Post', $adminClasses);
        $this->assertContains('sonata_post_admin', $adminClasses['Sonata\AdminBundle\Tests\DependencyInjection\Post']);
        $this->assertArrayHasKey('Sonata\AdminBundle\Tests\DependencyInjection\Article', $adminClasses);
        $this->assertContains('sonata_article_admin', $adminClasses['Sonata\AdminBundle\Tests\DependencyInjection\Article']);
        $this->assertArrayHasKey('Sonata\AdminBundle\Tests\DependencyInjection\News', $adminClasses);
        $this->assertContains('sonata_news_admin', $adminClasses['Sonata\AdminBundle\Tests\DependencyInjection\News']);
    }

    /**
     * @covers Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass::process
     */
    public function testProcessGroupNameAsParameter()
    {
        $config = array(
            'dashboard' => array(
                'groups' => array(
                    '%sonata.admin.parameter.groupname%' => array(
                    ),
                )
            )
        );

        $container = $this->getContainer();
        $container->setParameter('sonata.admin.parameter.groupname', 'resolved_group_name');

        $this->extension->load(array($config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $compilerPass->process($container);
        $container->compile();

        $adminGroups = $container->get('sonata.admin.pool')->getAdminGroups();

        $this->assertArrayHasKey('resolved_group_name', $adminGroups);
        $this->assertFalse(array_key_exists('%sonata.admin.parameter.groupname%', $adminGroups));
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        $config = array(
            'dashboard' => array(
                'groups' => array(
                    'sonata_group_one' => array(
                        'label' => 'Group One Label',
                        'label_catalogue' => 'SonataAdminBundle',
                        'items' => array(
                            'sonata_post_admin'
                        ),
                        'item_adds' => array(
                            'sonata_news_admin'
                        ),
                        'roles' => array('ROLE_ONE'),
                    ),
                )
            )
        );
        return $config;
    }

    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array(
            'SonataCoreBundle' => true
        ));

        // Add dependencies for SonataAdminBundle (these services will never get called so dummy classes will do)
        $container
            ->register('twig')
            ->setClass('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $container
            ->register('templating')
            ->setClass('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $container
            ->register('translator')
            ->setClass('Symfony\Bundle\FrameworkBundle\Translation\TranslatorInterface');
        $container
            ->register('validator')
            ->setClass('Symfony\Bundle\FrameworkBundle\Validator\Validator');
        $container
            ->register('validator.validator_factory')
            ->setClass('Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory');
        $container
            ->register('router')
            ->setClass('Symfony\Component\Routing\RouterInterface');
        $container
            ->register('form.factory')
            ->setClass('Symfony\Component\Form\FormFactoryInterface');
        $container
            ->register('sonata.admin.manager.orm')
            ->setClass('Sonata\DoctrineORMAdminBundle\Model\ModelManager');
        $container
            ->register('sonata.admin.builder.orm_form')
            ->setClass('Sonata\DoctrineORMAdminBundle\Builder\FormContractor');
        $container
            ->register('sonata.admin.builder.orm_show')
            ->setClass('Sonata\DoctrineORMAdminBundle\Builder\ShowBuilder');
        $container
            ->register('sonata.admin.builder.orm_list')
            ->setClass('Sonata\DoctrineORMAdminBundle\Builder\ListBuilder');
        $container
            ->register('sonata.admin.builder.orm_datagrid')
            ->setClass('Sonata\DoctrineORMAdminBundle\Builder\DatagridBuilder');
        $container
            ->register('knp_menu.factory')
            ->setClass('Knp\Menu\Silex\RouterAwareFactory');
        $container
            ->register('event_dispatcher')
            ->setClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        // Add admin definition's
        $container
            ->register('sonata_post_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\Post', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_group_one', 'manager_type' => 'orm'));
        $container
            ->register('sonata_news_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\News', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_group_two', 'manager_type' => 'orm'));
        $container
            ->register('sonata_article_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\Article', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_group_one', 'manager_type' => 'orm'));

        return $container;
    }
}
