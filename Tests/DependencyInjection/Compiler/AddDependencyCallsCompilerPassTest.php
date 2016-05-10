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
        $this->config = $this->getConfig();
    }

    public function testTranslatorDisabled()
    {
        $this->setExpectedException(
          'RuntimeException', 'The "translator" service is not yet enabled.
                It\'s required by SonataAdmin to display all labels properly.

                To learn how to enable the translator service please visit:
                http://symfony.com/doc/current/book/translation.html#book-translation-configuration
             '
        );

        $container = $this->getContainer();
        $container->removeAlias('translator');
        $this->extension->load(array($this->config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $compilerPass->process($container);
        $container->compile();
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
        $this->assertSame(false, $dashboardGroupsSettings['sonata_group_one']['on_top']);
        $this->assertSame(true, $dashboardGroupsSettings['sonata_group_three']['on_top']);
        $this->assertArrayHasKey('admin', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        $this->assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        $this->assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        $this->assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        $this->assertContains('sonata_post_admin', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        $this->assertArrayHasKey('admin', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertContains('blog_name', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertContains('Blog', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertSame('', $dashboardGroupsSettings['sonata_group_one']['items'][1]['admin']);
        $this->assertSame('blog_name', $dashboardGroupsSettings['sonata_group_one']['items'][1]['route']);
        $this->assertSame('Blog', $dashboardGroupsSettings['sonata_group_one']['items'][1]['label']);
        $this->assertSame(array(), $dashboardGroupsSettings['sonata_group_one']['items'][1]['route_params']);
        $this->assertArrayHasKey('admin', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertContains('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertContains('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertSame('', $dashboardGroupsSettings['sonata_group_one']['items'][2]['admin']);
        $this->assertSame('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['route']);
        $this->assertSame('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['label']);
        $this->assertSame(array('articleId' => 3), $dashboardGroupsSettings['sonata_group_one']['items'][2]['route_params']);
        $this->assertContains('sonata_news_admin', $dashboardGroupsSettings['sonata_group_one']['item_adds']);
        $this->assertContains('ROLE_ONE', $dashboardGroupsSettings['sonata_group_one']['roles']);

        $this->assertArrayHasKey('sonata_group_two', $dashboardGroupsSettings);
        $this->assertArrayHasKey('provider', $dashboardGroupsSettings['sonata_group_two']);
        $this->assertContains('my_menu', $dashboardGroupsSettings['sonata_group_two']['provider']);
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
        $this->assertSame(false, $adminGroups['sonata_group_one']['on_top']);
        $this->assertSame(true, $adminGroups['sonata_group_three']['on_top']);
        $this->assertContains('sonata_post_admin', $adminGroups['sonata_group_one']['items'][0]['admin']);
        $this->assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['items']);
        $this->assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['item_adds']);
        $this->assertFalse(in_array('sonata_article_admin', $adminGroups['sonata_group_one']['items']));
        $this->assertContains('ROLE_ONE', $adminGroups['sonata_group_one']['roles']);

        $this->assertArrayHasKey('sonata_group_two', $adminGroups);
        $this->assertArrayHasKey('provider', $adminGroups['sonata_group_two']);
        $this->assertContains('my_menu', $adminGroups['sonata_group_two']['provider']);

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

    public function testProcessSortAdmins()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['options']['sort_admins'] = true;
        unset($config['dashboard']['groups']);

        $this->extension->load(array($config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $compilerPass->process($container);
        $container->compile();

        // use array_values to check groups position
        $adminGroups = array_values($container->get('sonata.admin.pool')->getAdminGroups());

        $this->assertSame('sonata_group_one', $adminGroups['0']['label'], 'second group in configuration, first in list');
        $this->assertSame('1 Entry', $adminGroups[0]['items'][0]['label'], 'second entry for group in configuration, first in list');
    }

    public function testProcessGroupNameAsParameter()
    {
        $config = array(
            'dashboard' => array(
                'groups' => array(
                    '%sonata.admin.parameter.groupname%' => array(),
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

    public function testApplyTemplatesConfiguration()
    {
        $container = $this->getContainer();

        $this->extension->load(array($this->getConfig()), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $compilerPass->process($container);

        $callsPostAdmin = $container->getDefinition('sonata_post_admin')->getMethodCalls();

        foreach ($callsPostAdmin as $call) {
            list($name, $parameters) = $call;

            switch ($name) {
                case 'setTemplates':
                    $this->assertSame('foobar.twig.html', $parameters[0]['user_block']);
                    $this->assertSame('SonataAdminBundle:Pager:results.html.twig', $parameters[0]['pager_results']);
                    break;

                case 'setLabel':
                    $this->assertSame('-', $parameters[0]);
                    break;

                case 'setPagerType':
                    $this->assertSame('default', $parameters[0]);
                    break;
            }
        }

        $callsNewsAdmin = $container->getDefinition('sonata_news_admin')->getMethodCalls();

        foreach ($callsNewsAdmin as $call) {
            list($name, $parameters) = $call;

            switch ($name) {
                case 'setTemplates':
                    $this->assertSame('foo.twig.html', $parameters[0]['user_block']);
                    $this->assertSame('SonataAdminBundle:Pager:simple_pager_results.html.twig', $parameters[0]['pager_results']);
                    break;

                case 'setLabel':
                    $this->assertSame('Foo', $parameters[0]);
                    break;

                case 'setPagerType':
                    $this->assertSame('simple', $parameters[0]);
                    break;
            }
        }
    }

    public function testProcessMultipleOnTopOptions()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $this->assertArrayHasKey('sonata_group_four', $config['dashboard']['groups']);

        $config['dashboard']['groups']['sonata_group_four']['items'][] = array(
            'route' => 'blog_article',
            'label' => 'Article',
            'route_params' => array('articleId' => 3),
        );

        $this->extension->load(array($config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();

        try {
            $compilerPass->process($container);
        } catch (\RuntimeException $e) {
            $this->assertSame('You can\'t use "on_top" option with multiple same name groups.', $e->getMessage());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testProcessMultipleOnTopOptionsAdditionalGroup()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['dashboard']['groups']['sonata_group_five'] = array(
            'label' => 'Group One Label',
            'label_catalogue' => 'SonataAdminBundle',
            'on_top' => true,
            'items' => array(
                'sonata_post_admin',
                array(
                    'route' => 'blog_name',
                    'label' => 'Blog',
                ),
                array(
                    'route' => 'blog_article',
                    'label' => 'Article',
                    'route_params' => array('articleId' => 3),
                ),
            ),
            'item_adds' => array(
                'sonata_news_admin',
            ),
            'roles' => array('ROLE_ONE'),
        );

        $this->extension->load(array($config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();

        try {
            $compilerPass->process($container);
        } catch (\RuntimeException $e) {
            $this->assertSame('You can\'t use "on_top" option with multiple same name groups.', $e->getMessage());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testProcessMultipleOnTopOptionsInServiceDefinition()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = array();

        $this->extension->load(array($config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $container
            ->register('sonata_report_one_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportOne', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => true));

        try {
            $compilerPass->process($container);
        } catch (\RuntimeException $e) {
            $this->assertSame('You can\'t use "on_top" option with multiple same name groups.', $e->getMessage());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testProcessMultipleOnTopOptionsInServiceDefinition1()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = array();

        $this->extension->load(array($config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $container
            ->register('sonata_report_two_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportOne', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => false));

        try {
            $compilerPass->process($container);
        } catch (\RuntimeException $e) {
            $this->assertSame('You can\'t use "on_top" option with multiple same name groups.', $e->getMessage());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testProcessMultipleOnTopOptionsInServiceDefinition2()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = array();

        $this->extension->load(array($config), $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $container
            ->register('sonata_document_one_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportOne', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false));
        $container
            ->register('sonata_document_two_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportOne', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false));

        try {
            $compilerPass->process($container);
        } catch (\RuntimeException $e) {
            $this->fail('An expected exception has been raised.');
        }
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
                            'sonata_post_admin',
                            array(
                                'route' => 'blog_name',
                                'label' => 'Blog',
                            ),
                            array(
                                'route' => 'blog_article',
                                'label' => 'Article',
                                'route_params' => array('articleId' => 3),
                            ),
                        ),
                        'item_adds' => array(
                            'sonata_news_admin',
                        ),
                        'roles' => array('ROLE_ONE'),
                    ),
                    'sonata_group_two' => array(
                        'provider' => 'my_menu',
                    ),
                    'sonata_group_three' => array(
                        'on_top' => true,
                    ),
                    'sonata_group_four' => array(
                        'on_top' => true,
                        'label' => 'Group Four Label',
                        'label_catalogue' => 'SonataAdminBundle',
                        'items' => array(
                            'sonata_post_admin',
                        ),
                    ),
                ),
            ),
            'admin_services' => array(
                'sonata_post_admin' => array(
                    'templates' => array(
                        'view' => array('user_block' => 'foobar.twig.html'),
                    ),
                ),
                'sonata_news_admin' => array(
                    'label' => 'Foo',
                    'pager_type' => 'simple',
                    'templates' => array(
                        'view' => array('user_block' => 'foo.twig.html'),
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
            'KnpMenuBundle' => true,
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
            ->register('property_accessor')
            ->setClass('Symfony\Component\PropertyAccess\PropertyAccessor');
        $container
            ->register('form.factory')
            ->setClass('Symfony\Component\Form\FormFactoryInterface');
        foreach (array(
            'doctrine_phpcr' => 'PHPCR',
            'orm' => 'ORM', ) as $key => $bundleSubstring) {
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
            ->register('knp_menu.menu_provider')
            ->setClass('Knp\Menu\Provider\MenuProviderInterface');
        $container
            ->register('knp_menu.matcher')
            ->setClass('Knp\Menu\Matcher\MatcherInterface');
        $container
            ->register('event_dispatcher')
            ->setClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        // Add admin definition's
        $container
            ->register('sonata_news_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\News', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_group_two', 'label' => '5 Entry', 'manager_type' => 'orm'));
        $container
            ->register('sonata_post_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\Post', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_group_one', 'manager_type' => 'orm'));
        $container
            ->register('sonata_article_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\Article', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_group_one', 'label' => '1 Entry', 'manager_type' => 'doctrine_phpcr'));
        $container
            ->register('sonata_report_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\Report', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin', array('group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => true));

        // translator
        $container
            ->register('translator.default')
            ->setClass('Symfony\Bundle\FrameworkBundle\Translation\Translator');
        $container->setAlias('translator', 'translator.default');

        return $container;
    }
}
