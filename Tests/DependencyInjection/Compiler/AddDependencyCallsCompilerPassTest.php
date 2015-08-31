<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
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

        $this->assertSame('Group One Label', $dashboardGroupsSettings['sonata_group_one']['label']);
        $this->assertSame('SonataAdminBundle', $dashboardGroupsSettings['sonata_group_one']['label_catalogue']);
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
        $this->assertSame('Group One Label', $adminGroups['sonata_group_one']['label']);
        $this->assertSame('SonataAdminBundle', $adminGroups['sonata_group_one']['label_catalogue']);
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
        $newsRouteBuilderMethodCall = current(array_filter(
            $container->getDefinition('sonata_news_admin')->getMethodCalls(),
            function ($element) {
                return $element[0] == 'setRouteBuilder';
            }
        ));
        $this->assertSame(
            'sonata.admin.route.path_info',
            (string) $newsRouteBuilderMethodCall[1][0],
            'The news admin uses the orm, and should therefore use the path_info router.'
        );
        $articleRouteBuilderMethodCall = current(array_filter(
            $container->getDefinition('sonata_article_admin')->getMethodCalls(),
            function ($element) {
                return $element[0] == 'setRouteBuilder';
            }
        ));
        $this->assertSame(
            'sonata.admin.route.path_info_slashes',
            (string) $articleRouteBuilderMethodCall[1][0],
            'The article admin uses the odm, and should therefore use the path_info_slashes router.'
        );
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
                ),
            ),
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
                        'label'           => 'Group One Label',
                        'label_catalogue' => 'SonataAdminBundle',
                        'items'           => array(
                            'sonata_post_admin',
                        ),
                        'item_adds' => array(
                            'sonata_news_admin',
                        ),
                        'roles' => array('ROLE_ONE'),
                    ),
                ),
            ),
        );

        return $config;
    }

    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array(
            'SonataCoreBundle' => true,
            'KnpMenuBundle'    => true,
        ));
        $container->setParameter('kernel.cache_dir', '/tmp');
        $container->setParameter('kernel.debug', true);

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
        foreach (array(
            'doctrine_phpcr' => 'PHPCR',
            'orm'            => 'ORM', ) as $key => $bundleSubstring) {
            $container
                ->register(sprintf('sonata.admin.manager.%s', $key))
                ->setClass(sprintf(
                    'Sonata\Doctrine%sAdminBundle\Model\ModelManager',
                    $bundleSubstring
                ));
            $container
                ->register(sprintf('sonata.admin.builder.%s_form', $key))
                ->setClass(sprintf(
                    'Sonata\Doctrine%sAdminBundle\Builder\FormContractor',
                    $bundleSubstring
                ));
            $container
                ->register(sprintf('sonata.admin.builder.%s_show', $key))
                ->setClass(sprintf(
                    'Sonata\Doctrine%sAdminBundle\Builder\ShowBuilder',
                    $bundleSubstring
                ));
            $container
                ->register(sprintf('sonata.admin.builder.%s_list', $key))
                ->setClass(sprintf(
                    'Sonata\Doctrine%sAdminBundle\Builder\ListBuilder',
                    $bundleSubstring
                ));
            $container
                ->register(sprintf('sonata.admin.builder.%s_datagrid', $key))
                ->setClass(sprintf(
                    'Sonata\Doctrine%sAdminBundle\Builder\DatagridBuilder',
                    $bundleSubstring
                ));
        }
        $container
            ->register('sonata.admin.route.path_info_slashes')
            ->setClass('Sonata\DoctrinePHPCRAdminBundle\Route\PathInfoBuilderSlashes');
        $container
            ->register('sonata.admin.route.cache')
            ->setClass('Sonata\AdminBundle\Route\RoutesCache');
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
            ->addTag('sonata.admin', array('group' => 'sonata_group_one', 'manager_type' => 'doctrine_phpcr'));

        return $container;
    }
}
