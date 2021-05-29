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

namespace Sonata\AdminBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\AdminBundle\Tests\Fixtures\Controller\FooAdminController;
use Sonata\BlockBundle\DependencyInjection\SonataBlockExtension;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveEnvPlaceholdersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Tiago Garcia
 */
final class AddDependencyCallsCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @var SonataAdminExtension
     */
    private $extension;

    /**
     * @var array
     */
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new SonataAdminExtension();
        $this->config = $this->getConfig();
    }

    public function testTranslatorDisabled(): void
    {
        $this->setUpContainer();
        $this->container->removeAlias('translator');
        $this->container->removeDefinition('translator');
        $this->extension->load([$this->config], $this->container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'The "translator" service is not yet enabled.
                It\'s required by SonataAdmin to display all labels properly.

                To learn how to enable the translator service please visit:
                http://symfony.com/doc/current/translation.html#configuration
            '
        );

        $this->compile();
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass::process
     */
    public function testProcessParsingFullValidConfig(): void
    {
        $this->setUpContainer();
        $this->extension->load([$this->config], $this->container);

        $this->compile();

        $this->assertContainerBuilderHasParameter('sonata.admin.configuration.dashboard_groups');

        $dashboardGroupsSettings = $this->container->getParameter('sonata.admin.configuration.dashboard_groups');

        $this->assertArrayHasKey('sonata_group_one', $dashboardGroupsSettings);

        $this->assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']);
        $this->assertArrayHasKey('label_catalogue', $dashboardGroupsSettings['sonata_group_one']);
        $this->assertArrayHasKey('items', $dashboardGroupsSettings['sonata_group_one']);
        $this->assertArrayHasKey('item_adds', $dashboardGroupsSettings['sonata_group_one']);
        $this->assertArrayHasKey('roles', $dashboardGroupsSettings['sonata_group_one']);
        $this->assertSame('Group One Label', $dashboardGroupsSettings['sonata_group_one']['label']);
        $this->assertSame('SonataAdminBundle', $dashboardGroupsSettings['sonata_group_one']['label_catalogue']);
        $this->assertFalse($dashboardGroupsSettings['sonata_group_one']['on_top']);
        $this->assertTrue($dashboardGroupsSettings['sonata_group_three']['on_top']);
        $this->assertFalse($dashboardGroupsSettings['sonata_group_one']['keep_open']);
        $this->assertArrayHasKey('admin', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        $this->assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        $this->assertContains('sonata_post_admin', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        $this->assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertContains('blog_name', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertContains('Blog', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        $this->assertSame('blog_name', $dashboardGroupsSettings['sonata_group_one']['items'][1]['route']);
        $this->assertSame('Blog', $dashboardGroupsSettings['sonata_group_one']['items'][1]['label']);
        $this->assertSame([], $dashboardGroupsSettings['sonata_group_one']['items'][1]['route_params']);
        $this->assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertContains('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertContains('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        $this->assertSame('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['route']);
        $this->assertSame('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['label']);
        $this->assertSame(['articleId' => 3], $dashboardGroupsSettings['sonata_group_one']['items'][2]['route_params']);
        $this->assertContains('sonata_news_admin', $dashboardGroupsSettings['sonata_group_one']['item_adds']);
        $this->assertContains('ROLE_ONE', $dashboardGroupsSettings['sonata_group_one']['roles']);

        $this->assertArrayHasKey('sonata_group_two', $dashboardGroupsSettings);
        $this->assertArrayHasKey('provider', $dashboardGroupsSettings['sonata_group_two']);
        $this->assertStringContainsString('my_menu', $dashboardGroupsSettings['sonata_group_two']['provider']);

        $this->assertArrayHasKey('sonata_group_five', $dashboardGroupsSettings);
        $this->assertTrue($dashboardGroupsSettings['sonata_group_five']['keep_open']);
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass::process
     */
    public function testProcessResultingConfig(): void
    {
        $this->setUpContainer();
        $this->extension->load([$this->config], $this->container);

        $this->compile();

        $this->assertContainerBuilderHasService('sonata.admin.pool');
        $this->assertContainerBuilderHasService('sonata_post_admin');
        $this->assertContainerBuilderHasService('sonata_article_admin');
        $this->assertContainerBuilderHasService('sonata_news_admin');

        $poolDefinition = $this->container->findDefinition('sonata.admin.pool');
        $adminServiceIds = $poolDefinition->getArgument(1);
        $adminGroups = $poolDefinition->getArgument(2);
        $adminClasses = $poolDefinition->getArgument(3);

        $this->assertContains('sonata_post_admin', $adminServiceIds);
        $this->assertContains('sonata_article_admin', $adminServiceIds);
        $this->assertContains('sonata_news_admin', $adminServiceIds);

        $this->assertContains('sonata_post_admin', $poolDefinition->getArgument(1));
        $this->assertArrayHasKey('sonata_group_one', $poolDefinition->getArgument(2));
        $this->assertArrayHasKey(News::class, $poolDefinition->getArgument(3));

        $this->assertArrayHasKey('sonata_group_one', $adminGroups);
        $this->assertArrayHasKey('label', $adminGroups['sonata_group_one']);
        $this->assertArrayHasKey('label_catalogue', $adminGroups['sonata_group_one']);
        $this->assertArrayHasKey('items', $adminGroups['sonata_group_one']);
        $this->assertArrayHasKey('item_adds', $adminGroups['sonata_group_one']);
        $this->assertArrayHasKey('roles', $adminGroups['sonata_group_one']);
        $this->assertSame('Group One Label', $adminGroups['sonata_group_one']['label']);
        $this->assertSame('SonataAdminBundle', $adminGroups['sonata_group_one']['label_catalogue']);
        $this->assertFalse($adminGroups['sonata_group_one']['on_top']);
        $this->assertTrue($adminGroups['sonata_group_three']['on_top']);
        $this->assertFalse($adminGroups['sonata_group_one']['keep_open']);
        $this->assertStringContainsString(
            'sonata_post_admin',
            $adminGroups['sonata_group_one']['items'][0]['admin']
        );
        $this->assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['items']);
        $this->assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['item_adds']);
        $this->assertNotContains('sonata_article_admin', $adminGroups['sonata_group_one']['items']);
        $this->assertContains('ROLE_ONE', $adminGroups['sonata_group_one']['roles']);

        $this->assertArrayHasKey('sonata_group_two', $adminGroups);
        $this->assertArrayHasKey('provider', $adminGroups['sonata_group_two']);
        $this->assertStringContainsString('my_menu', $adminGroups['sonata_group_two']['provider']);

        $this->assertArrayHasKey('sonata_group_five', $adminGroups);
        $this->assertTrue($adminGroups['sonata_group_five']['keep_open']);

        $this->assertArrayHasKey(Post::class, $adminClasses);
        $this->assertContains('sonata_post_admin', $adminClasses[Post::class]);
        $this->assertArrayHasKey(Article::class, $adminClasses);
        $this->assertContains('sonata_article_admin', $adminClasses[Article::class]);
        $this->assertArrayHasKey(News::class, $adminClasses);
        $this->assertContains('sonata_news_admin', $adminClasses[News::class]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setRouteBuilder',
            ['sonata.admin.route.path_info']
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_article_admin',
            'setRouteBuilder',
            ['sonata.admin.route.path_info_slashes']
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setFormTheme',
            [[]]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setFilterTheme',
            [[]]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setModelManager',
            [new Reference('my.model.manager')]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_article_admin',
            'setFormTheme',
            [['custom_form_theme.twig']]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_article_admin',
            'setFilterTheme',
            [['custom_filter_theme.twig']]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_post_admin',
            'setFormTheme',
            [['some_form_template.twig']]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_post_admin',
            'setFilterTheme',
            [['some_filter_template.twig']]
        );
    }

    public function testProcessSortAdmins(): void
    {
        $this->setUpContainer();

        $config = $this->config;
        $config['options']['sort_admins'] = true;
        unset($config['dashboard']['groups']);

        $this->extension->load([$config], $this->container);

        $this->compile();

        // use array_values to check groups position
        $adminGroups = array_values($this->container->findDefinition('sonata.admin.pool')->getArgument(2));

        $this->assertSame('sonata_group_one', $adminGroups['0']['label'], 'second group in configuration, first in list');
        $this->assertSame('1 Entry', $adminGroups[0]['items'][0]['label'], 'second entry for group in configuration, first in list');
    }

    public function testProcessGroupNameAsParameter(): void
    {
        $config = [
            'dashboard' => [
                'groups' => [
                    '%sonata.admin.parameter.groupname%' => [],
                ],
            ],
        ];

        $this->setUpContainer();
        $this->container->setParameter('sonata.admin.parameter.groupname', 'resolved_group_name');

        $this->allowToResolveParameters();

        $this->extension->load([$config], $this->container);
        $this->compile();

        $adminGroups = $this->container->findDefinition('sonata.admin.pool')->getArgument(2);
        $this->assertArrayHasKey('resolved_group_name', $adminGroups);
        $this->assertArrayNotHasKey('%sonata.admin.parameter.groupname%', $adminGroups);
    }

    public function testApplyTemplatesConfiguration(): void
    {
        $this->setUpContainer();

        $this->extension->load([$this->getConfig()], $this->container);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_post_admin',
            'setLabel',
            ['-']
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_post_admin',
            'setPagerType',
            ['default']
        );

        $postAdminTemplates = $this->container->findDefinition('sonata_post_admin.template_registry')->getArgument(0);

        $this->assertSame('foobar.twig.html', $postAdminTemplates['user_block']);
        $this->assertSame('@SonataAdmin/Pager/results.html.twig', $postAdminTemplates['pager_results']);
        $this->assertSame('@SonataAdmin/Button/create_button.html.twig', $postAdminTemplates['button_create']);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setLabel',
            ['Foo']
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setPagerType',
            ['simple']
        );

        $postAdminTemplates = $this->container->findDefinition('sonata_news_admin.template_registry')->getArgument(0);

        $this->assertSame('foo.twig.html', $postAdminTemplates['user_block']);
        $this->assertSame('@SonataAdmin/Pager/simple_pager_results.html.twig', $postAdminTemplates['pager_results']);
    }

    public function testApplyShowMosaicButtonConfiguration(): void
    {
        $this->setUpContainer();

        $this->extension->load([$this->getConfig()], $this->container);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_report_one_admin',
            'setListModes',
            [['list' => ['class' => 'fa fa-list fa-fw']]]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_report_two_admin',
            'setListModes',
            [TaggedAdminInterface::DEFAULT_LIST_MODES]
        );
    }

    public function testProcessMultipleOnTopOptions(): void
    {
        $this->setUpContainer();

        $config = $this->config;
        $this->assertArrayHasKey('sonata_group_four', $config['dashboard']['groups']);

        $config['dashboard']['groups']['sonata_group_four']['items'][] = [
            'route' => 'blog_article',
            'label' => 'Article',
            'route_params' => ['articleId' => 3],
        ];

        $this->extension->load([$config], $this->container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can\'t use "on_top" option with multiple same name groups.');

        $this->compile();
    }

    public function testProcessMultipleOnTopOptionsAdditionalGroup(): void
    {
        $this->setUpContainer();

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

        $this->extension->load([$config], $this->container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can\'t use "on_top" option with multiple same name groups.');

        $this->compile();
    }

    public function testProcessMultipleOnTopOptionsInServiceDefinition(): void
    {
        $this->setUpContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $this->container);

        $this->container
            ->register('sonata_report_one_admin')
            ->setClass(MockAdmin::class)
            ->setArguments(['', ReportOne::class, CRUDController::class])
            ->addTag('sonata.admin', ['group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => true]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can\'t use "on_top" option with multiple same name groups.');

        $this->compile();
    }

    public function testProcessMultipleOnTopOptionsInServiceDefinition1(): void
    {
        $this->setUpContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $this->container);

        $this->container
            ->register('sonata_report_two_admin')
            ->setClass(MockAdmin::class)
            ->setArguments(['', ReportOne::class, CRUDController::class])
            ->addTag('sonata.admin', ['group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => false]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can\'t use "on_top" option with multiple same name groups.');

        $this->compile();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testProcessMultipleOnTopOptionsInServiceDefinition2(): void
    {
        $this->setUpContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $this->container);

        $this->container
            ->register('sonata_document_one_admin')
            ->setClass(MockAdmin::class)
            ->setArguments(['', ReportOne::class, CRUDController::class])
            ->addTag('sonata.admin', ['group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false]);
        $this->container
            ->register('sonata_document_two_admin')
            ->setClass(MockAdmin::class)
            ->setArguments(['', ReportOne::class, CRUDController::class])
            ->addTag('sonata.admin', ['group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false]);

        try {
            $this->compile();
        } catch (\RuntimeException $e) {
            $this->fail('An expected exception has been raised.');
        }
    }

    public function testProcessAbstractAdminServiceInServiceDefinition(): void
    {
        $this->setUpContainer();

        $config = $this->config;
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $this->container);

        $this->container
            ->register('sonata_abstract_post_admin')
            ->setArguments(['', Post::class, ''])
            ->setAbstract(true);

        $adminDefinition = new ChildDefinition('sonata_abstract_post_admin');
        $adminDefinition
            ->setPublic(true)
            ->setClass(MockAbstractServiceAdmin::class)
            ->setArguments([0 => 'extra_argument_1'])
            ->addTag('sonata.admin', ['group' => 'sonata_post_one_group', 'manager_type' => 'orm']);

        $adminTwoDefinition = new ChildDefinition('sonata_abstract_post_admin');
        $adminTwoDefinition
            ->setPublic(true)
            ->setClass(MockAbstractServiceAdmin::class)
            ->setArguments([0 => 'extra_argument_2', 'index_0' => 'should_not_override'])
            ->addTag('sonata.admin', ['group' => 'sonata_post_two_group', 'manager_type' => 'orm']);

        $this->container->addDefinitions([
            'sonata_post_one_admin' => $adminDefinition,
            'sonata_post_two_admin' => $adminTwoDefinition,
        ]);

        $this->allowToResolveChildren();

        $this->compile();

        $pool = $this->container->findDefinition('sonata.admin.pool');
        $adminServiceIds = $pool->getArgument(1);

        $this->assertContains('sonata_post_one_admin', $adminServiceIds);
        $this->assertContains('sonata_post_two_admin', $adminServiceIds);

        $this->assertContainerBuilderHasService('sonata_post_one_admin');
        $this->assertContainerBuilderHasService('sonata_post_two_admin');

        $definition = $this->container->findDefinition('sonata_post_one_admin');
        $this->assertSame('sonata_post_one_admin', $definition->getArgument(0));
        $this->assertSame(Post::class, $definition->getArgument(1));
        $this->assertSame(CRUDController::class, $definition->getArgument(2));
        $this->assertSame('extra_argument_1', $definition->getArgument(3));

        $definition = $this->container->findDefinition('sonata_post_two_admin');
        $this->assertSame('sonata_post_two_admin', $definition->getArgument(0));
        $this->assertSame(Post::class, $definition->getArgument(1));
        $this->assertSame(CRUDController::class, $definition->getArgument(2));
        $this->assertSame('extra_argument_2', $definition->getArgument(3));
    }

    public function testDefaultControllerCanBeChanged(): void
    {
        $this->setUpContainer();

        $config = $this->config;
        $config['default_controller'] = FooAdminController::class;

        $this->container
            ->register('sonata_without_controller')
            ->setClass(MockAdmin::class)
            ->setArguments(['', ReportTwo::class, ''])
            ->addTag('sonata.admin', ['group' => 'sonata_report_two_group', 'manager_type' => 'orm']);

        $this->extension->load([$config], $this->container);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'sonata_without_controller',
            2,
            FooAdminController::class
        );
    }

    public function testMultipleDefaultAdmin(): void
    {
        $this->setUpContainer();
        $this->container
            ->register('sonata_post_admin_2')
            ->setClass(MockAdmin::class)
            ->setPublic(true)
            ->setArguments(['', Post::class, CRUDController::class])
            ->addTag('sonata.admin', ['default' => true, 'group' => 'sonata_group_one', 'manager_type' => 'orm']);

        $config = $this->config;

        $this->extension->load([$config], $this->container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The class Sonata\AdminBundle\Tests\DependencyInjection\Compiler\Post has two admins sonata_post_admin and sonata_post_admin_2 with the "default" attribute set to true. Only one is allowed.');

        $this->compile();
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return [
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
                        'form' => ['some_form_template.twig'],
                        'filter' => ['some_filter_template.twig'],
                    ],
                ],
                'sonata_news_admin' => [
                    'label' => 'Foo',
                    'pager_type' => 'simple',
                    'templates' => [
                        'view' => ['user_block' => 'foo.twig.html'],
                    ],
                ],
                'sonata_article_admin' => [
                    'templates' => [
                        'form' => ['some_form_template.twig'],
                        'filter' => ['some_filter_template.twig'],
                    ],
                ],
            ],
        ];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddDependencyCallsCompilerPass());
    }

    private function setUpContainer(): void
    {
        $this->container->setParameter('kernel.bundles', [
            'SonataCoreBundle' => true,
            'KnpMenuBundle' => true,
        ]);
        $this->container->setParameter('kernel.cache_dir', '/tmp');
        $this->container->setParameter('kernel.debug', true);

        // Add admin definition's
        $this->container
            ->register('sonata_news_admin')
            ->setPublic(true)
            ->setClass(MockAdmin::class)
            ->setArguments(['', News::class, CRUDController::class])
            ->addTag('sonata.admin', ['group' => 'sonata_group_two', 'label' => '5 Entry', 'manager_type' => 'orm'])
            ->addMethodCall('setModelManager', [new Reference('my.model.manager')]);
        $this->container
            ->register('sonata_post_admin')
            ->setClass(MockAdmin::class)
            ->setPublic(true)
            ->setArguments(['', Post::class, CRUDController::class])
            ->addTag('sonata.admin', ['default' => true, 'group' => 'sonata_group_one', 'manager_type' => 'orm']);
        $this->container
            ->register('sonata_article_admin')
            ->setPublic(true)
            ->setClass(MockAdmin::class)
            ->setArguments(['', Article::class, CRUDController::class])
            ->addTag('sonata.admin', ['group' => 'sonata_group_one', 'label' => '1 Entry', 'manager_type' => 'doctrine_phpcr'])
            ->addMethodCall('setFormTheme', [['custom_form_theme.twig']])
            ->addMethodCall('setFilterTheme', [['custom_filter_theme.twig']]);
        $this->container
            ->register('sonata_report_admin')
            ->setPublic(true)
            ->setClass(MockAdmin::class)
            ->setArguments(['', Report::class, CRUDController::class])
            ->addTag('sonata.admin', ['group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => true]);
        $this->container
            ->register('sonata_report_one_admin')
            ->setClass(MockAdmin::class)
            ->setArguments(['', ReportOne::class, CRUDController::class])
            ->addTag('sonata.admin', ['group' => 'sonata_report_one_group', 'manager_type' => 'orm', 'show_mosaic_button' => false]);
        $this->container
            ->register('sonata_report_two_admin')
            ->setClass(MockAdmin::class)
            ->setArguments(['', ReportTwo::class, CRUDController::class])
            ->addTag('sonata.admin', ['group' => 'sonata_report_two_group', 'manager_type' => 'orm', 'show_mosaic_button' => true]);

        // translator
        $this->container
            ->register('translator.default')
            ->setClass(Translator::class);
        $this->container->setAlias('translator', 'translator.default');

        $blockExtension = new SonataBlockExtension();
        $blockExtension->load([], $this->container);
    }

    private function allowToResolveChildren(): void
    {
        $this->container->addCompilerPass(new ResolveChildDefinitionsPass());
    }

    private function allowToResolveParameters(): void
    {
        $this->container->setParameter('kernel.project_dir', '/tmp');
        $this->container->addCompilerPass(new ResolveEnvPlaceholdersPass(), PassConfig::TYPE_AFTER_REMOVING, -1000);
    }
}
