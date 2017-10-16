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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * @author Tiago Garcia
 */
class AddDependencyCallsCompilerPassTest extends TestCase
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
        $this->expectException(
          'RuntimeException', 'The "translator" service is not yet enabled.
                It\'s required by SonataAdmin to display all labels properly.

                To learn how to enable the translator service please visit:
                http://symfony.com/doc/current/translation.html#configuration
             '
        );

        $container = $this->getContainer();
        $container->removeAlias('translator');
        $container->removeDefinition('translator');
        $this->extension->load([$this->config], $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $compilerPass->process($container);
        $container->compile();
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass::process
     */
    public function testProcessParsingFullValidConfig()
    {
        $container = $this->getContainer();
        $this->extension->load([$this->config], $container);

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
        $this->assertFalse($dashboardGroupsSettings['sonata_group_one']['keep_open']);
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
        $this->assertSame([], $dashboardGroupsSettings['sonata_group_one']['items'][1]['route_params']);
        $this->assertArrayHasKey('admin', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertContains('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertContains('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertSame('', $dashboardGroupsSettings['sonata_group_one']['items'][2]['admin']);
        $this->assertSame('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['route']);
        $this->assertSame('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['label']);
        $this->assertSame(['articleId' => 3], $dashboardGroupsSettings['sonata_group_one']['items'][2]['route_params']);
        $this->assertContains('sonata_news_admin', $dashboardGroupsSettings['sonata_group_one']['item_adds']);
        $this->assertContains('ROLE_ONE', $dashboardGroupsSettings['sonata_group_one']['roles']);

        $this->assertArrayHasKey('sonata_group_two', $dashboardGroupsSettings);
        $this->assertArrayHasKey('provider', $dashboardGroupsSettings['sonata_group_two']);
        $this->assertContains('my_menu', $dashboardGroupsSettings['sonata_group_two']['provider']);

        $this->assertArrayHasKey('sonata_group_five', $dashboardGroupsSettings);
        $this->assertTrue($dashboardGroupsSettings['sonata_group_five']['keep_open']);
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass::process
     */
    public function testProcessResultingConfig()
    {
        $container = $this->getContainer();
        $this->extension->load([$this->config], $container);

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
        $this->assertFalse($adminGroups['sonata_group_one']['keep_open']);
        $this->assertContains('sonata_post_admin', $adminGroups['sonata_group_one']['items'][0]['admin']);
        $this->assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['items']);
        $this->assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['item_adds']);
        $this->assertFalse(in_array('sonata_article_admin', $adminGroups['sonata_group_one']['items']));
        $this->assertContains('ROLE_ONE', $adminGroups['sonata_group_one']['roles']);

        $this->assertArrayHasKey('sonata_group_two', $adminGroups);
        $this->assertArrayHasKey('provider', $adminGroups['sonata_group_two']);
        $this->assertContains('my_menu', $adminGroups['sonata_group_two']['provider']);

        $this->assertArrayHasKey('sonata_group_five', $adminGroups);
        $this->assertTrue($adminGroups['sonata_group_five']['keep_open']);

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

        $this->extension->load([$config], $container);

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
        $config = [
            'dashboard' => [
                'groups' => [
                    '%sonata.admin.parameter.groupname%' => [],
                ],
            ],
        ];

        $container = $this->getContainer();
        $container->setParameter('sonata.admin.parameter.groupname', 'resolved_group_name');

        $this->extension->load([$config], $container);

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

        $this->extension->load([$this->getConfig()], $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $compilerPass->process($container);

        $callsPostAdmin = $container->getDefinition('sonata_post_admin')->getMethodCalls();

        foreach ($callsPostAdmin as $call) {
            list($name, $parameters) = $call;

            switch ($name) {
                case 'setTemplates':
                    $this->assertSame('foobar.twig.html', $parameters[0]['user_block']);
                    $this->assertSame('SonataAdminBundle:Pager:results.html.twig', $parameters[0]['pager_results']);
                    $this->assertSame('SonataAdminBundle:Button:create_button.html.twig', $parameters[0]['button_create']);

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

    public function testApplyShowMosaicButtonConfiguration()
    {
        $container = $this->getContainer();

        $this->extension->load([$this->getConfig()], $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $compilerPass->process($container);

        $callsReportOneAdmin = $container->getDefinition('sonata_report_one_admin')->getMethodCalls();

        foreach ($callsReportOneAdmin as $call) {
            list($name, $parameters) = $call;

            if ($name == 'showMosaicButton') {
                $this->assertSame(false, $parameters[0]);
            }
        }

        $callsReportTwoAdmin = $container->getDefinition('sonata_report_two_admin')->getMethodCalls();

        foreach ($callsReportTwoAdmin as $call) {
            list($name, $parameters) = $call;

            if ($name == 'showMosaicButton') {
                $this->assertSame(true, $parameters[0]);
            }
        }
    }

    public function testProcessMultipleOnTopOptions()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $this->assertArrayHasKey('sonata_group_four', $config['dashboard']['groups']);

        $config['dashboard']['groups']['sonata_group_four']['items'][] = [
            'route' => 'blog_article',
            'label' => 'Article',
            'route_params' => ['articleId' => 3],
        ];

        $this->extension->load([$config], $container);

        $compilerPass = new AddDependencyCallsCompilerPass();

        $this->expectException('\RuntimeException', 'You can\'t use "on_top" option with multiple same name groups.');

        $compilerPass->process($container);
    }

    public function testProcessMultipleOnTopOptionsAdditionalGroup()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['dashboard']['groups']['sonata_group_five'] = [
            'label' => 'Group One Label',
            'label_catalogue' => 'SonataAdminBundle',
            'on_top' => true,
            'items' => [
                'sonata_post_admin',
                [
                    'route' => 'blog_name',
                    'label' => 'Blog',
                ],
                [
                    'route' => 'blog_article',
                    'label' => 'Article',
                    'route_params' => ['articleId' => 3],
                ],
            ],
            'item_adds' => [
                'sonata_news_admin',
            ],
            'roles' => ['ROLE_ONE'],
        ];

        $this->extension->load([$config], $container);

        $compilerPass = new AddDependencyCallsCompilerPass();

        $this->expectException('\RuntimeException', 'You can\'t use "on_top" option with multiple same name groups.');

        $compilerPass->process($container);
    }

    public function testProcessMultipleOnTopOptionsInServiceDefinition()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $container
            ->register('sonata_report_one_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportOne', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => true]);

        $this->expectException('\RuntimeException', 'You can\'t use "on_top" option with multiple same name groups.');

        $compilerPass->process($container);
    }

    public function testProcessMultipleOnTopOptionsInServiceDefinition1()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $container
            ->register('sonata_report_two_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportOne', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => false]);

        $this->expectException('\RuntimeException', 'You can\'t use "on_top" option with multiple same name groups.');

        $compilerPass->process($container);
    }

    public function testProcessMultipleOnTopOptionsInServiceDefinition2()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $container
            ->register('sonata_document_one_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportOne', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false]);
        $container
            ->register('sonata_document_two_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportOne', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false]);

        try {
            $compilerPass->process($container);
        } catch (\RuntimeException $e) {
            $this->fail('An expected exception has been raised.');
        }
    }

    public function testProcessAbstractAdminServiceInServiceDefinition()
    {
        $container = $this->getContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $container);

        $compilerPass = new AddDependencyCallsCompilerPass();
        $container
            ->register('sonata_abstract_post_admin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\Post', ''])
            ->setAbstract(true);

        $adminDefinition = new DefinitionDecorator('sonata_abstract_post_admin');
        $adminDefinition
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAbstractServiceAdmin')
            ->setArguments([0 => 'extra_argument_1'])
            ->addTag('sonata.admin', ['group' => 'sonata_post_one_group', 'manager_type' => 'orm']);

        $adminTwoDefinition = new DefinitionDecorator('sonata_abstract_post_admin');
        $adminTwoDefinition
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAbstractServiceAdmin')
            ->setArguments([0 => 'extra_argument_2', 'index_0' => 'should_not_override'])
            ->addTag('sonata.admin', ['group' => 'sonata_post_two_group', 'manager_type' => 'orm']);

        $container->addDefinitions([
            'sonata_post_one_admin' => $adminDefinition,
            'sonata_post_two_admin' => $adminTwoDefinition,
        ]);

        $compilerPass->process($container);
        $container->compile();

        $pool = $container->get('sonata.admin.pool');
        $adminServiceIds = $pool->getAdminServiceIds();

        $this->assertContains('sonata_post_one_admin', $adminServiceIds);
        $this->assertContains('sonata_post_two_admin', $adminServiceIds);

        $this->assertTrue($container->hasDefinition('sonata_post_one_admin'));
        $this->assertTrue($container->hasDefinition('sonata_post_two_admin'));

        $definition = $container->getDefinition('sonata_post_one_admin');
        $this->assertSame('sonata_post_one_admin', $definition->getArgument(0));
        $this->assertSame('Sonata\AdminBundle\Tests\DependencyInjection\Post', $definition->getArgument(1));
        $this->assertSame('SonataAdminBundle:CRUD', $definition->getArgument(2));
        $this->assertSame('extra_argument_1', $definition->getArgument(3));

        $definition = $container->getDefinition('sonata_post_two_admin');
        $this->assertSame('sonata_post_two_admin', $definition->getArgument(0));
        $this->assertSame('Sonata\AdminBundle\Tests\DependencyInjection\Post', $definition->getArgument(1));
        $this->assertSame('SonataAdminBundle:CRUD', $definition->getArgument(2));
        $this->assertSame('extra_argument_2', $definition->getArgument(3));
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        $config = [
            'dashboard' => [
                'groups' => [
                    'sonata_group_one' => [
                        'label' => 'Group One Label',
                        'label_catalogue' => 'SonataAdminBundle',
                        'items' => [
                            'sonata_post_admin',
                            [
                                'route' => 'blog_name',
                                'label' => 'Blog',
                            ],
                            [
                                'route' => 'blog_article',
                                'label' => 'Article',
                                'route_params' => ['articleId' => 3],
                            ],
                        ],
                        'item_adds' => [
                            'sonata_news_admin',
                        ],
                        'roles' => ['ROLE_ONE'],
                    ],
                    'sonata_group_two' => [
                        'provider' => 'my_menu',
                    ],
                    'sonata_group_three' => [
                        'on_top' => true,
                    ],
                    'sonata_group_four' => [
                        'on_top' => true,
                        'label' => 'Group Four Label',
                        'label_catalogue' => 'SonataAdminBundle',
                        'items' => [
                            'sonata_post_admin',
                        ],
                    ],
                    'sonata_group_five' => [
                        'keep_open' => true,
                    ],
                ],
            ],
            'admin_services' => [
                'sonata_post_admin' => [
                    'templates' => [
                        'view' => ['user_block' => 'foobar.twig.html'],
                    ],
                ],
                'sonata_news_admin' => [
                    'label' => 'Foo',
                    'pager_type' => 'simple',
                    'templates' => [
                        'view' => ['user_block' => 'foo.twig.html'],
                    ],
                ],
            ],
        ];

        return $config;
    }

    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', [
            'SonataCoreBundle' => true,
            'KnpMenuBundle' => true,
        ]);
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
        foreach ([
            'doctrine_phpcr' => 'PHPCR',
            'orm' => 'ORM', ] as $key => $bundleSubstring) {
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
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\News', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_group_two', 'label' => '5 Entry', 'manager_type' => 'orm']);
        $container
            ->register('sonata_post_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\Post', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_group_one', 'manager_type' => 'orm']);
        $container
            ->register('sonata_article_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\Article', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_group_one', 'label' => '1 Entry', 'manager_type' => 'doctrine_phpcr']);
        $container
            ->register('sonata_report_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\Report', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => true]);
        $container
            ->register('sonata_report_one_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportOne', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_report_one_group', 'manager_type' => 'orm', 'show_mosaic_button' => false]);
        $container
            ->register('sonata_report_two_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\ReportTwo', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin', ['group' => 'sonata_report_two_group', 'manager_type' => 'orm', 'show_mosaic_button' => true]);

        // translator
        $container
            ->register('translator.default')
            ->setClass('Symfony\Bundle\FrameworkBundle\Translation\Translator');
        $container->setAlias('translator', 'translator.default');

        return $container;
    }
}
