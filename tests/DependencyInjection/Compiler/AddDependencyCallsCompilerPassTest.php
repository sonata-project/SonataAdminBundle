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
use Sonata\AdminBundle\Admin\AbstractAdmin;
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
     *  @var array<string, mixed>
     */
    private $config = [];

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

        self::assertContainerBuilderHasParameter('sonata.admin.configuration.dashboard_groups');

        $dashboardGroupsSettings = $this->container->getParameter('sonata.admin.configuration.dashboard_groups');
        self::assertIsArray($dashboardGroupsSettings);

        self::assertArrayHasKey('sonata_group_one', $dashboardGroupsSettings);

        self::assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']);
        self::assertArrayHasKey('label_catalogue', $dashboardGroupsSettings['sonata_group_one']);
        self::assertArrayHasKey('items', $dashboardGroupsSettings['sonata_group_one']);
        self::assertArrayHasKey('item_adds', $dashboardGroupsSettings['sonata_group_one']);
        self::assertArrayHasKey('roles', $dashboardGroupsSettings['sonata_group_one']);
        self::assertSame('Group One Label', $dashboardGroupsSettings['sonata_group_one']['label']);
        self::assertSame('SonataAdminBundle', $dashboardGroupsSettings['sonata_group_one']['label_catalogue']);
        self::assertFalse($dashboardGroupsSettings['sonata_group_one']['on_top']);
        self::assertTrue($dashboardGroupsSettings['sonata_group_three']['on_top']);
        self::assertFalse($dashboardGroupsSettings['sonata_group_one']['keep_open']);
        self::assertArrayHasKey('admin', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        self::assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        self::assertContains('sonata_post_admin', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        self::assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        self::assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        self::assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        self::assertContains('blog_name', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        self::assertContains('Blog', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        self::assertSame('blog_name', $dashboardGroupsSettings['sonata_group_one']['items'][1]['route']);
        self::assertSame('Blog', $dashboardGroupsSettings['sonata_group_one']['items'][1]['label']);
        self::assertSame([], $dashboardGroupsSettings['sonata_group_one']['items'][1]['route_params']);
        self::assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        self::assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        self::assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        self::assertContains('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        self::assertContains('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        self::assertSame('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['route']);
        self::assertSame('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['label']);
        self::assertSame(['articleId' => 3], $dashboardGroupsSettings['sonata_group_one']['items'][2]['route_params']);
        self::assertContains('sonata_news_admin', $dashboardGroupsSettings['sonata_group_one']['item_adds']);
        self::assertContains('ROLE_ONE', $dashboardGroupsSettings['sonata_group_one']['roles']);

        self::assertArrayHasKey('sonata_group_two', $dashboardGroupsSettings);
        self::assertArrayHasKey('provider', $dashboardGroupsSettings['sonata_group_two']);
        self::assertStringContainsString('my_menu', $dashboardGroupsSettings['sonata_group_two']['provider']);

        self::assertArrayHasKey('sonata_group_five', $dashboardGroupsSettings);
        self::assertTrue($dashboardGroupsSettings['sonata_group_five']['keep_open']);
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass::process
     */
    public function testProcessResultingConfig(): void
    {
        $this->setUpContainer();
        $this->extension->load([$this->config], $this->container);

        $this->compile();

        self::assertContainerBuilderHasService('sonata.admin.pool');
        self::assertContainerBuilderHasService('sonata_post_admin');
        self::assertContainerBuilderHasService('sonata_article_admin');
        self::assertContainerBuilderHasService('sonata_news_admin');

        $poolDefinition = $this->container->findDefinition('sonata.admin.pool');
        $adminServiceIds = $poolDefinition->getArgument(1);
        $adminGroups = $poolDefinition->getArgument(2);
        $adminClasses = $poolDefinition->getArgument(3);

        self::assertContains('sonata_post_admin', $adminServiceIds);
        self::assertContains('sonata_article_admin', $adminServiceIds);
        self::assertContains('sonata_news_admin', $adminServiceIds);

        self::assertContains('sonata_post_admin', $poolDefinition->getArgument(1));
        self::assertArrayHasKey('sonata_group_one', $poolDefinition->getArgument(2));
        self::assertArrayHasKey(NewsEntity::class, $poolDefinition->getArgument(3));

        self::assertArrayHasKey('sonata_group_one', $adminGroups);
        self::assertArrayHasKey('label', $adminGroups['sonata_group_one']);
        self::assertArrayHasKey('label_catalogue', $adminGroups['sonata_group_one']);
        self::assertArrayHasKey('items', $adminGroups['sonata_group_one']);
        self::assertArrayHasKey('item_adds', $adminGroups['sonata_group_one']);
        self::assertArrayHasKey('roles', $adminGroups['sonata_group_one']);
        self::assertSame('Group One Label', $adminGroups['sonata_group_one']['label']);
        self::assertSame('SonataAdminBundle', $adminGroups['sonata_group_one']['label_catalogue']);
        self::assertFalse($adminGroups['sonata_group_one']['on_top']);
        self::assertTrue($adminGroups['sonata_group_three']['on_top']);
        self::assertFalse($adminGroups['sonata_group_one']['keep_open']);
        self::assertStringContainsString(
            'sonata_post_admin',
            $adminGroups['sonata_group_one']['items'][0]['admin']
        );
        self::assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['items']);
        self::assertContains('sonata_news_admin', $adminGroups['sonata_group_one']['item_adds']);
        self::assertNotContains('sonata_article_admin', $adminGroups['sonata_group_one']['items']);
        self::assertContains('ROLE_ONE', $adminGroups['sonata_group_one']['roles']);

        self::assertArrayHasKey('sonata_group_two', $adminGroups);
        self::assertArrayHasKey('provider', $adminGroups['sonata_group_two']);
        self::assertStringContainsString('my_menu', $adminGroups['sonata_group_two']['provider']);

        self::assertArrayHasKey('sonata_group_five', $adminGroups);
        self::assertTrue($adminGroups['sonata_group_five']['keep_open']);

        self::assertArrayHasKey(PostEntity::class, $adminClasses);
        self::assertContains('sonata_post_admin', $adminClasses[PostEntity::class]);
        self::assertArrayHasKey(ArticleEntity::class, $adminClasses);
        self::assertContains('sonata_article_admin', $adminClasses[ArticleEntity::class]);
        self::assertArrayHasKey(NewsEntity::class, $adminClasses);
        self::assertContains('sonata_news_admin', $adminClasses[NewsEntity::class]);

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setRouteBuilder',
            ['sonata.admin.route.path_info']
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setPagerType',
            ['simple']
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setFormTheme',
            [['some_form_template.twig']]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setFilterTheme',
            [['some_filter_template.twig']]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setModelManager',
            [new Reference('my.model.manager')]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_article_admin',
            'setPagerType',
            ['simple']
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_article_admin',
            'setFormTheme',
            [['custom_form_theme.twig']]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_article_admin',
            'setFilterTheme',
            [['custom_filter_theme.twig']]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_post_admin',
            'setPagerType',
            ['simple']
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_post_admin',
            'setFormTheme',
            [['some_form_template.twig']]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
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

        self::assertSame('sonata_group_one', $adminGroups['0']['label'], 'second group in configuration, first in list');
        self::assertSame('1 Entry', $adminGroups[0]['items'][0]['label'], 'second entry for group in configuration, first in list');
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
        self::assertArrayHasKey('resolved_group_name', $adminGroups);
        self::assertArrayNotHasKey('%sonata.admin.parameter.groupname%', $adminGroups);
    }

    public function testApplyTemplatesConfiguration(): void
    {
        $this->setUpContainer();

        $this->extension->load([$this->getConfig()], $this->container);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_post_admin',
            'setLabel',
            [null]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_post_admin',
            'setPagerType',
            ['simple']
        );

        $postAdminTemplates = $this->container->findDefinition('sonata_post_admin.template_registry')->getArgument(0);

        self::assertSame('@SonataAdmin/Pager/simple_pager_results.html.twig', $postAdminTemplates['pager_results']);
        self::assertSame('@SonataAdmin/Button/create_button.html.twig', $postAdminTemplates['button_create']);
    }

    public function testApplyShowMosaicButtonConfiguration(): void
    {
        $this->setUpContainer();

        $this->extension->load([$this->getConfig()], $this->container);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_report_one_admin',
            'setListModes',
            [['list' => ['class' => 'fas fa-list fa-fw']]]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_report_two_admin',
            'setListModes',
            [TaggedAdminInterface::DEFAULT_LIST_MODES]
        );
    }

    public function testProcessMultipleOnTopOptions(): void
    {
        $this->setUpContainer();

        $config = $this->config;
        self::assertArrayHasKey('sonata_group_four', $config['dashboard']['groups']);

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
            ->setClass(CustomAdmin::class)
            ->setArguments(['', ReportOne::class, 'sonata.admin.controller.crud'])
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
            ->setClass(CustomAdmin::class)
            ->setArguments(['', ReportOne::class, 'sonata.admin.controller.crud'])
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
            ->setClass(CustomAdmin::class)
            ->setArguments(['', ReportOne::class, 'sonata.admin.controller.crud'])
            ->addTag('sonata.admin', ['group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false]);
        $this->container
            ->register('sonata_document_two_admin')
            ->setClass(CustomAdmin::class)
            ->setArguments(['', ReportOne::class, 'sonata.admin.controller.crud'])
            ->addTag('sonata.admin', ['group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false]);

        try {
            $this->compile();
        } catch (\RuntimeException $e) {
            self::fail('An expected exception has been raised.');
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
            ->setArguments(['', PostEntity::class, ''])
            ->setAbstract(true);

        $adminDefinition = new ChildDefinition('sonata_abstract_post_admin');
        $adminDefinition
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->setArguments([0 => 'extra_argument_1'])
            ->addTag('sonata.admin', ['group' => 'sonata_post_one_group', 'manager_type' => 'orm']);

        $adminTwoDefinition = new ChildDefinition('sonata_abstract_post_admin');
        $adminTwoDefinition
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
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

        self::assertContains('sonata_post_one_admin', $adminServiceIds);
        self::assertContains('sonata_post_two_admin', $adminServiceIds);

        self::assertContainerBuilderHasService('sonata_post_one_admin');
        self::assertContainerBuilderHasService('sonata_post_two_admin');

        $definition = $this->container->findDefinition('sonata_post_one_admin');
        self::assertSame('sonata_post_one_admin', $definition->getArgument(0));
        self::assertSame(PostEntity::class, $definition->getArgument(1));
        self::assertSame('sonata.admin.controller.crud', $definition->getArgument(2));
        self::assertSame('extra_argument_1', $definition->getArgument(3));

        $definition = $this->container->findDefinition('sonata_post_two_admin');
        self::assertSame('sonata_post_two_admin', $definition->getArgument(0));
        self::assertSame(PostEntity::class, $definition->getArgument(1));
        self::assertSame('sonata.admin.controller.crud', $definition->getArgument(2));
        self::assertSame('extra_argument_2', $definition->getArgument(3));
    }

    public function testDefaultControllerCanBeChanged(): void
    {
        $this->setUpContainer();

        $config = $this->config;
        $config['default_controller'] = FooAdminController::class;

        $this->container
            ->register('sonata_without_controller')
            ->setClass(CustomAdmin::class)
            ->setArguments(['', ReportTwo::class, ''])
            ->addTag('sonata.admin', ['group' => 'sonata_report_two_group', 'manager_type' => 'orm']);

        $this->extension->load([$config], $this->container);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithArgument(
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
            ->setClass(CustomAdmin::class)
            ->setPublic(true)
            ->setArguments(['', PostEntity::class, 'sonata.admin.controller.crud'])
            ->addTag('sonata.admin', ['default' => true, 'group' => 'sonata_group_one', 'manager_type' => 'orm']);

        $config = $this->config;

        $this->extension->load([$config], $this->container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The class Sonata\AdminBundle\Tests\DependencyInjection\Compiler\PostEntity has two admins sonata_post_admin and sonata_post_admin_2 with the "default" attribute set to true. Only one is allowed.');

        $this->compile();
    }

    /**
     * @return array<string, mixed>
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
            'templates' => [
                'form_theme' => ['some_form_template.twig'],
                'filter_theme' => ['some_filter_template.twig'],
            ],
            'default_admin_services' => [
                'pager_type' => 'simple',
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
            'KnpMenuBundle' => true,
        ]);
        $this->container->setParameter('kernel.cache_dir', '/tmp');
        $this->container->setParameter('kernel.debug', true);

        // Add admin definition's
        $this->container
            ->register('sonata_news_admin')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->setArguments(['', NewsEntity::class, 'sonata.admin.controller.crud'])
            ->addTag('sonata.admin', ['group' => 'sonata_group_two', 'label' => '5 Entry', 'manager_type' => 'orm'])
            ->addMethodCall('setModelManager', [new Reference('my.model.manager')]);
        $this->container
            ->register('sonata_post_admin')
            ->setClass(CustomAdmin::class)
            ->setPublic(true)
            ->setArguments(['', PostEntity::class, 'sonata.admin.controller.crud'])
            ->addTag('sonata.admin', ['default' => true, 'group' => 'sonata_group_one', 'manager_type' => 'orm']);
        $this->container
            ->register('sonata_article_admin')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->setArguments(['', ArticleEntity::class, 'sonata.admin.controller.crud'])
            ->addTag('sonata.admin', ['group' => 'sonata_group_one', 'label' => '1 Entry', 'manager_type' => 'doctrine_mongodb'])
            ->addMethodCall('setFormTheme', [['custom_form_theme.twig']])
            ->addMethodCall('setFilterTheme', [['custom_filter_theme.twig']]);
        $this->container
            ->register('sonata_report_admin')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->setArguments(['', Report::class, 'sonata.admin.controller.crud'])
            ->addTag('sonata.admin', ['group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => true]);
        $this->container
            ->register('sonata_report_one_admin')
            ->setClass(CustomAdmin::class)
            ->setArguments(['', ReportOne::class, 'sonata.admin.controller.crud'])
            ->addTag('sonata.admin', ['group' => 'sonata_report_one_group', 'manager_type' => 'orm', 'show_mosaic_button' => false]);
        $this->container
            ->register('sonata_report_two_admin')
            ->setClass(CustomAdmin::class)
            ->setArguments(['', ReportTwo::class, 'sonata.admin.controller.crud'])
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

/** @phpstan-extends AbstractAdmin<object> */
class CustomAdmin extends AbstractAdmin
{
}

class Report
{
}
class ReportOne
{
}
class ReportTwo
{
}
class NewsEntity
{
}
class PostEntity
{
}
class ArticleEntity
{
}
