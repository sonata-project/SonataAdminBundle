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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\AdminBundle\Tests\Fixtures\Controller\FooAdminController;
use Sonata\BlockBundle\Cache\HttpCacheHandler;
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
    private SonataAdminExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new SonataAdminExtension();
    }

    public function testTranslatorDisabled(): void
    {
        $this->setUpContainer();
        $this->container->removeAlias('translator');
        $this->container->removeDefinition('translator');
        $this->extension->load([$this->getConfig()], $this->container);

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
        $this->extension->load([$this->getConfig()], $this->container);

        $this->compile();

        self::assertContainerBuilderHasParameter('sonata.admin.configuration.dashboard_groups');

        $dashboardGroupsSettings = $this->container->getParameter('sonata.admin.configuration.dashboard_groups');
        static::assertIsArray($dashboardGroupsSettings);

        static::assertArrayHasKey('sonata_group_one', $dashboardGroupsSettings);

        static::assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']);
        static::assertArrayHasKey('translation_domain', $dashboardGroupsSettings['sonata_group_one']);
        static::assertArrayHasKey('items', $dashboardGroupsSettings['sonata_group_one']);
        static::assertArrayHasKey('roles', $dashboardGroupsSettings['sonata_group_one']);
        static::assertSame('Group One Label', $dashboardGroupsSettings['sonata_group_one']['label']);
        static::assertSame('SonataAdminBundle', $dashboardGroupsSettings['sonata_group_one']['translation_domain']);
        static::assertFalse($dashboardGroupsSettings['sonata_group_one']['on_top']);
        static::assertTrue($dashboardGroupsSettings['sonata_group_three']['on_top']);
        static::assertFalse($dashboardGroupsSettings['sonata_group_one']['keep_open']);
        static::assertIsArray($dashboardGroupsSettings['sonata_group_one']['items'][0]);
        static::assertArrayHasKey('admin', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        static::assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        static::assertContains('sonata_post_admin', $dashboardGroupsSettings['sonata_group_one']['items'][0]);
        static::assertIsArray($dashboardGroupsSettings['sonata_group_one']['items'][1]);
        static::assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        static::assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        static::assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        static::assertContains('blog_name', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        static::assertContains('Blog', $dashboardGroupsSettings['sonata_group_one']['items'][1]);
        static::assertSame('blog_name', $dashboardGroupsSettings['sonata_group_one']['items'][1]['route']);
        static::assertSame('Blog', $dashboardGroupsSettings['sonata_group_one']['items'][1]['label']);
        static::assertSame([], $dashboardGroupsSettings['sonata_group_one']['items'][1]['route_params']);
        static::assertIsArray($dashboardGroupsSettings['sonata_group_one']['items'][2]);
        static::assertArrayHasKey('route', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        static::assertArrayHasKey('label', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        static::assertArrayHasKey('route_params', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        static::assertContains('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        static::assertContains('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]);
        static::assertSame('blog_article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['route']);
        static::assertSame('Article', $dashboardGroupsSettings['sonata_group_one']['items'][2]['label']);
        static::assertSame(['articleId' => 3], $dashboardGroupsSettings['sonata_group_one']['items'][2]['route_params']);
        static::assertContains('ROLE_ONE', $dashboardGroupsSettings['sonata_group_one']['roles']);

        static::assertArrayHasKey('sonata_group_two', $dashboardGroupsSettings);
        static::assertArrayHasKey('provider', $dashboardGroupsSettings['sonata_group_two']);
        static::assertStringContainsString('my_menu', $dashboardGroupsSettings['sonata_group_two']['provider']);

        static::assertArrayHasKey('sonata_group_five', $dashboardGroupsSettings);
        static::assertTrue($dashboardGroupsSettings['sonata_group_five']['keep_open']);
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass::process
     */
    public function testProcessResultingConfig(): void
    {
        $this->setUpContainer();
        $this->extension->load([$this->getConfig()], $this->container);

        $this->compile();

        self::assertContainerBuilderHasService('sonata.admin.pool');
        self::assertContainerBuilderHasService('sonata_post_admin');
        self::assertContainerBuilderHasService('sonata_article_admin');
        self::assertContainerBuilderHasService('sonata_news_admin');

        $poolDefinition = $this->container->findDefinition('sonata.admin.pool');
        $adminServiceIds = $poolDefinition->getArgument(1);
        static::assertIsArray($adminServiceIds);
        $adminGroups = $poolDefinition->getArgument(2);
        static::assertIsArray($adminGroups);
        $adminClasses = $poolDefinition->getArgument(3);
        static::assertIsArray($adminClasses);

        static::assertContains('sonata_post_admin', $adminServiceIds);
        static::assertContains('sonata_article_admin', $adminServiceIds);
        static::assertContains('sonata_news_admin', $adminServiceIds);

        static::assertArrayHasKey('sonata_group_one', $adminGroups);
        static::assertArrayHasKey('label', $adminGroups['sonata_group_one']);
        static::assertArrayHasKey('translation_domain', $adminGroups['sonata_group_one']);
        static::assertArrayHasKey('items', $adminGroups['sonata_group_one']);
        static::assertArrayHasKey('roles', $adminGroups['sonata_group_one']);
        static::assertSame('Group One Label', $adminGroups['sonata_group_one']['label']);
        static::assertSame('SonataAdminBundle', $adminGroups['sonata_group_one']['translation_domain']);
        static::assertFalse($adminGroups['sonata_group_one']['on_top']);
        static::assertTrue($adminGroups['sonata_group_three']['on_top']);
        static::assertFalse($adminGroups['sonata_group_one']['keep_open']);
        static::assertStringContainsString(
            'sonata_post_admin',
            $adminGroups['sonata_group_one']['items'][0]['admin']
        );
        static::assertNotContains('sonata_article_admin', $adminGroups['sonata_group_one']['items']);
        static::assertContains('ROLE_ONE', $adminGroups['sonata_group_one']['roles']);

        static::assertArrayHasKey('sonata_group_two', $adminGroups);
        static::assertArrayHasKey('provider', $adminGroups['sonata_group_two']);
        static::assertStringContainsString('my_menu', $adminGroups['sonata_group_two']['provider']);

        static::assertArrayHasKey('sonata_group_five', $adminGroups);
        static::assertTrue($adminGroups['sonata_group_five']['keep_open']);

        static::assertArrayHasKey(PostEntity::class, $adminClasses);
        static::assertContains('sonata_post_admin', $adminClasses[PostEntity::class]);
        static::assertArrayHasKey(ArticleEntity::class, $adminClasses);
        static::assertContains('sonata_article_admin', $adminClasses[ArticleEntity::class]);
        static::assertArrayHasKey(NewsEntity::class, $adminClasses);
        static::assertContains('sonata_news_admin', $adminClasses[NewsEntity::class]);

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

        $config = $this->getConfig();
        $config['options']['sort_admins'] = true;
        unset($config['dashboard']['groups']);

        $this->extension->load([$config], $this->container);

        $this->compile();

        $adminGroups = $this->container->findDefinition('sonata.admin.pool')->getArgument(2);
        static::assertIsArray($adminGroups);

        // use array_values to check groups position
        $adminGroups = array_values($adminGroups);
        $firstGroup = $adminGroups[0];

        static::assertSame('sonata_group_one', $firstGroup['label'], 'second group in configuration, first in list');
        static::assertSame('1 Entry', $firstGroup['items'][0]['label'], 'second entry for group in configuration, first in list');
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
        static::assertIsArray($adminGroups);
        static::assertArrayHasKey('resolved_group_name', $adminGroups);
        static::assertArrayNotHasKey('%sonata.admin.parameter.groupname%', $adminGroups);
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

        static::assertIsArray($postAdminTemplates);
        static::assertSame('@SonataAdmin/Pager/simple_pager_results.html.twig', $postAdminTemplates['pager_results']);
        static::assertSame('@SonataAdmin/Button/create_button.html.twig', $postAdminTemplates['button_create']);
    }

    public function testApplyShowMosaicButtonConfiguration(): void
    {
        $this->setUpContainer();

        $this->extension->load([$this->getConfig()], $this->container);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_report_one_admin',
            'setListModes',
            [['list' => [
                'icon' => '<i class="fas fa-list fa-fw" aria-hidden="true"></i>',
                // NEXT_MAJOR: Remove the class part.
                'class' => 'fas fa-list fa-fw',
            ]]]
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

        $config = $this->getConfig();
        static::assertArrayHasKey('sonata_group_four', $config['dashboard']['groups']);
        static::assertIsArray($config['dashboard']['groups']['sonata_group_four']['items']);

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

        $config = $this->getConfig();
        $config['dashboard']['groups']['sonata_group_five'] = [
            'label' => 'Group One Label',
            'translation_domain' => 'SonataAdminBundle',
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

        $config = $this->getConfig();
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $this->container);

        $this->container
            ->register('sonata_report_one_admin')
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => ReportOne::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => true]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can\'t use "on_top" option with multiple same name groups.');

        $this->compile();
    }

    public function testProcessMultipleOnTopOptionsInServiceDefinition1(): void
    {
        $this->setUpContainer();

        $config = $this->getConfig();
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $this->container);

        $this->container
            ->register('sonata_report_two_admin')
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => ReportOne::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => false]);

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

        $config = $this->getConfig();
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $this->container);

        $this->container
            ->register('sonata_document_one_admin')
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => ReportOne::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false]);
        $this->container
            ->register('sonata_document_two_admin')
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => ReportOne::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_document_group', 'manager_type' => 'orm', 'on_top' => false]);

        try {
            $this->compile();
        } catch (\RuntimeException) {
            static::fail('An expected exception has been raised.');
        }
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testProcessAbstractAdminServiceInServiceDefinition(): void
    {
        $this->setUpContainer();

        $config = $this->getConfig();
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

        static::assertIsArray($adminServiceIds);
        static::assertContains('sonata_post_one_admin', $adminServiceIds);
        static::assertContains('sonata_post_two_admin', $adminServiceIds);

        self::assertContainerBuilderHasService('sonata_post_one_admin');
        self::assertContainerBuilderHasService('sonata_post_two_admin');

        $definition = $this->container->findDefinition('sonata_post_one_admin');
        static::assertSame('sonata_post_one_admin', $definition->getArgument(0));
        static::assertSame(PostEntity::class, $definition->getArgument(1));
        static::assertSame('sonata.admin.controller.crud', $definition->getArgument(2));
        static::assertSame('extra_argument_1', $definition->getArgument(3));

        $definition = $this->container->findDefinition('sonata_post_two_admin');
        static::assertSame('sonata_post_two_admin', $definition->getArgument(0));
        static::assertSame(PostEntity::class, $definition->getArgument(1));
        static::assertSame('sonata.admin.controller.crud', $definition->getArgument(2));
        static::assertSame('extra_argument_2', $definition->getArgument(3));
    }

    public function testDefaultControllerCanBeChanged(): void
    {
        $this->setUpContainer();

        $config = $this->getConfig();
        $config['default_controller'] = FooAdminController::class;

        $this->container
            ->register('sonata_without_controller')
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => ReportTwo::class, 'group' => 'sonata_report_two_group', 'manager_type' => 'orm']);

        $this->extension->load([$config], $this->container);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_without_controller',
            'setBaseControllerName',
            [FooAdminController::class]
        );
    }

    public function testMultipleDefaultAdmin(): void
    {
        $this->setUpContainer();
        $this->container
            ->register('sonata_post_admin_2')
            ->setClass(CustomAdmin::class)
            ->setPublic(true)
            ->addTag('sonata.admin', ['model_class' => PostEntity::class, 'controller' => 'sonata.admin.controller.crud', 'default' => true, 'group' => 'sonata_group_one', 'manager_type' => 'orm']);

        $config = $this->getConfig();

        $this->extension->load([$config], $this->container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The class Sonata\AdminBundle\Tests\DependencyInjection\Compiler\PostEntity has two admins sonata_post_admin and sonata_post_admin_2 with the "default" attribute set to true. Only one is allowed.');

        $this->compile();
    }

    public function testProcessAdminItemPriorityDefinition(): void
    {
        $this->setUpContainer();

        foreach ($this->container->findTaggedServiceIds('sonata.admin') as $id => $_) {
            $this->container->removeDefinition($id);
        }

        $config = $this->getConfig();
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $this->container);

        $priorities = [200, 100, 450, 3000, 620, 330];
        foreach ($priorities as $priority) {
            $this->container
                ->register('sonata_admin_'.$priority)
                ->setPublic(true)
                ->setClass(CustomAdmin::class)
                ->addTag('sonata.admin', ['model_class' => NewsEntity::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_priority_1', 'label' => 'Entry', 'manager_type' => 'orm', 'priority' => $priority]);
        }

        $this->compile();

        rsort($priorities);
        $adminGroups = $this->container->findDefinition('sonata.admin.pool')->getArgument(2);
        static::assertCount(\count($priorities), $adminGroups['sonata_group_priority_1']['items']);
        foreach ($adminGroups['sonata_group_priority_1']['items'] as $item) {
            $priority = array_shift($priorities);
            static::assertSame('sonata_admin_'.$priority, $item['admin']);
        }
    }

    public function testGroupOrderingWithAdminItemPriorityDefinition(): void
    {
        $this->setUpContainer();

        foreach ($this->container->findTaggedServiceIds('sonata.admin') as $id => $_) {
            $this->container->removeDefinition($id);
        }

        $config = $this->getConfig();
        $config['dashboard']['groups'] = [];

        $this->extension->load([$config], $this->container);

        $this->container
            ->register('sonata_admin_1')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => NewsEntity::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_priority_1', 'label' => 'Entry', 'manager_type' => 'orm', 'priority' => 1000]);

        $this->container
            ->register('sonata_admin_2')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => NewsEntity::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_priority_3', 'label' => 'Entry', 'manager_type' => 'orm', 'priority' => 3000]);

        $this->container
            ->register('sonata_admin_3')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => NewsEntity::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_priority_2', 'label' => 'Entry', 'manager_type' => 'orm', 'priority' => 4000]);

        $this->compile();

        $adminGroups = $this->container->findDefinition('sonata.admin.pool')->getArgument(2);
        static::assertCount(3, $adminGroups);
        static::assertSame(['sonata_group_priority_2', 'sonata_group_priority_3', 'sonata_group_priority_1'], array_keys($adminGroups));
    }

    public function testAdminCodeShouldBeInjectedToPool(): void
    {
        $this->setUpContainer();

        $this->container
            ->register('sonata_foo_admin')
            ->setClass(CustomAdmin::class)
            ->setPublic(true)
            ->addTag('sonata.admin', ['model_class' => FooEntity::class, 'code' => 'sonata_bar_admin', 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_one', 'manager_type' => 'test']);

        $this->container
            ->register('sonata_baz_admin')
            ->setClass(CustomAdmin::class)
            ->setPublic(true)
            ->addTag('sonata.admin', ['model_class' => BazEntity::class, 'default' => true, 'code' => 'sonata_qux_admin', 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_one', 'manager_type' => 'test']);

        $config = $this->getConfig();
        $config['options']['sort_admins'] = true;
        unset($config['dashboard']['groups']);

        $this->extension->load([$config], $this->container);
        $this->container->getDefinition('sonata.admin.pool')->setPublic(true);

        $this->compile();

        self::assertContainerBuilderHasService('sonata.admin.pool');

        $pool = $this->container->get('sonata.admin.pool');
        static::assertInstanceOf(Pool::class, $pool);

        $serviceCodes = $pool->getAdminServiceCodes();

        static::assertContains('sonata_bar_admin', $serviceCodes);
        static::assertNotContains('sonata_foo_admin', $serviceCodes);

        static::assertContains('sonata_qux_admin', $serviceCodes);
        static::assertNotContains('sonata_baz_admin', $serviceCodes);

        $classes = $pool->getAdminClasses();

        static::assertArrayHasKey(FooEntity::class, $classes);
        static::assertCount(1, $classes[FooEntity::class]);
        static::assertArrayHasKey(0, $classes[FooEntity::class]);
        static::assertSame('sonata_bar_admin', $classes[FooEntity::class][0]);

        static::assertArrayHasKey(BazEntity::class, $classes);
        static::assertCount(1, $classes[BazEntity::class]);
        static::assertArrayHasKey(Pool::DEFAULT_ADMIN_KEY, $classes[BazEntity::class]);
        static::assertSame('sonata_qux_admin', $classes[BazEntity::class][Pool::DEFAULT_ADMIN_KEY]);
    }

    /**
     * NEXT_MAJOR: Enable this test.
     */
    // public function testTaggingAdminClassMoreThanOnce(): void
    // {
    //    $this->setUpContainer();
    //
    //    $this->container
    //        ->register('sonata_foo_admin')
    //        ->setClass(CustomAdmin::class)
    //        ->setPublic(true)
    //        ->addTag('sonata.admin', ['model_class' => PostEntity::class, 'code' => 'sonata_post_admin', 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_one', 'manager_type' => 'test'])
    //        ->addTag('sonata.admin', ['model_class' => ArticleEntity::class, 'code' => 'sonata_article_admin', 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_two', 'manager_type' => 'test']);
    //
    //    $this->extension->load([$this->getConfig()], $this->container);
    //
    //    $this->expectException(\RuntimeException::class);
    //    $this->expectExceptionMessage(
    //        'Found multiple sonata.admin tags in service sonata_foo_admin. Tagging a service with sonata.admin more
    //                than once is not supported. Consider defining multiple services with different sonata.admin tag
    //                parameters if this is really needed.'
    //    );
    //
    //    $this->compile();
    // }

    /**
     * @return array<string, mixed>
     *
     * @phpstan-return array{
     *     dashboard: array{groups: array<string, array<string, mixed>>},
     *     default_admin_services: array{pager_type: string},
     *     templates: array{filter_theme: list<string>, form_theme: list<string>},
     * }
     */
    protected function getConfig(): array
    {
        return [
            'dashboard' => [
                'groups' => [
                    'sonata_group_one' => [
                        'label' => 'Group One Label',
                        'translation_domain' => 'SonataAdminBundle',
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
                        'translation_domain' => 'SonataAdminBundle',
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
            ->addTag('sonata.admin', ['model_class' => NewsEntity::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_two', 'label' => '5 Entry', 'manager_type' => 'orm'])
            ->addMethodCall('setModelManager', [new Reference('my.model.manager')]);
        $this->container
            ->register('sonata_post_admin')
            ->setClass(CustomAdmin::class)
            ->setPublic(true)
            ->addTag('sonata.admin', ['model_class' => PostEntity::class, 'controller' => 'sonata.admin.controller.crud', 'default' => true, 'group' => 'sonata_group_one', 'manager_type' => 'orm']);
        $this->container
            ->register('sonata_article_admin')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => ArticleEntity::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_group_one', 'label' => '1 Entry', 'manager_type' => 'doctrine_mongodb'])
            ->addMethodCall('setFormTheme', [['custom_form_theme.twig']])
            ->addMethodCall('setFilterTheme', [['custom_filter_theme.twig']]);
        $this->container
            ->register('sonata_report_admin')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => Report::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_report_group', 'manager_type' => 'orm', 'on_top' => true]);
        $this->container
            ->register('sonata_report_one_admin')
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => ReportOne::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_report_one_group', 'manager_type' => 'orm', 'show_mosaic_button' => false]);
        $this->container
            ->register('sonata_report_two_admin')
            ->setClass(CustomAdmin::class)
            ->addTag('sonata.admin', ['model_class' => ReportTwo::class, 'controller' => 'sonata.admin.controller.crud', 'group' => 'sonata_report_two_group', 'manager_type' => 'orm', 'show_mosaic_button' => true]);

        // translator
        $this->container
            ->register('translator.default')
            ->setClass(Translator::class);
        $this->container->setAlias('translator', 'translator.default');

        $blockExtension = new SonataBlockExtension();
        /*
         * TODO: remove "http_cache" parameter when support for SonataBlockBundle 4 is dropped.
         */
        $blockExtension->load(
            [
                'sonata_block' => class_exists(HttpCacheHandler::class) ? ['http_cache' => false] : [],
            ],
            $this->container
        );
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
class FooEntity
{
}
class BazEntity
{
}
