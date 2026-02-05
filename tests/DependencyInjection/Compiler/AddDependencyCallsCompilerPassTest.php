<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\SensioLabsAdminExtension;
use SensioLabs\AdminBundle\Tests\Fixtures\Controller\FooAdminController;
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
#[CoversMethod(AddDependencyCallsCompilerPass::class, 'process')]
final class AddDependencyCallsCompilerPassTest extends AbstractCompilerPassTestCase
{
    private SensioLabsAdminExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new SensioLabsAdminExtension();
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

    public function testProcessResultingConfig(): void
    {
        $this->setUpContainer();
        $this->extension->load([$this->getConfig()], $this->container);

        $this->compile();

        self::assertContainerBuilderHasService('sensiolabs.admin.pool');
        self::assertContainerBuilderHasService('sonata_post_admin');
        self::assertContainerBuilderHasService('sonata_article_admin');
        self::assertContainerBuilderHasService('sonata_news_admin');

        $poolDefinition = $this->container->findDefinition('sensiolabs.admin.pool');
        $adminServiceIds = $poolDefinition->getArgument(1);
        static::assertIsArray($adminServiceIds);
        $adminClasses = $poolDefinition->getArgument(2);
        static::assertIsArray($adminClasses);

        static::assertContains('sonata_post_admin', $adminServiceIds);
        static::assertContains('sonata_article_admin', $adminServiceIds);
        static::assertContains('sonata_news_admin', $adminServiceIds);

        static::assertArrayHasKey(PostEntity::class, $adminClasses);
        static::assertContains('sonata_post_admin', $adminClasses[PostEntity::class]);
        static::assertArrayHasKey(ArticleEntity::class, $adminClasses);
        static::assertContains('sonata_article_admin', $adminClasses[ArticleEntity::class]);
        static::assertArrayHasKey(NewsEntity::class, $adminClasses);
        static::assertContains('sonata_news_admin', $adminClasses[NewsEntity::class]);

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_news_admin',
            'setRouteBuilder',
            ['sensiolabs.admin.route.path_info']
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
        static::assertSame('@SensioLabsAdmin/Pager/simple_pager_results.html.twig', $postAdminTemplates['pager_results']);
        static::assertSame('@SensioLabsAdmin/Button/create_button.html.twig', $postAdminTemplates['button_create']);
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
                'icon' => 'lucide:list',
            ]]]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata_report_two_admin',
            'setListModes',
            [TaggedAdminInterface::DEFAULT_LIST_MODES]
        );
    }

    /**
     * NEXT_MAJOR: Remove this test.
     */
    #[IgnoreDeprecations]
    public function testProcessAbstractAdminServiceInServiceDefinition(): void
    {
        $this->setUpContainer();

        $this->extension->load([$this->getConfig()], $this->container);

        $this->container
            ->register('sonata_abstract_post_admin')
            ->setArguments(['', PostEntity::class, ''])
            ->setAbstract(true);

        $adminDefinition = new ChildDefinition('sonata_abstract_post_admin');
        $adminDefinition
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->setArguments([0 => 'extra_argument_1'])
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['manager_type' => 'orm']);

        $adminTwoDefinition = new ChildDefinition('sonata_abstract_post_admin');
        $adminTwoDefinition
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->setArguments([0 => 'extra_argument_2', 'index_0' => 'should_not_override'])
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['manager_type' => 'orm']);

        $this->container->addDefinitions([
            'sonata_post_one_admin' => $adminDefinition,
            'sonata_post_two_admin' => $adminTwoDefinition,
        ]);

        $this->allowToResolveChildren();

        $this->compile();

        $pool = $this->container->findDefinition('sensiolabs.admin.pool');
        $adminServiceIds = $pool->getArgument(1);

        static::assertIsArray($adminServiceIds);
        static::assertContains('sonata_post_one_admin', $adminServiceIds);
        static::assertContains('sonata_post_two_admin', $adminServiceIds);

        self::assertContainerBuilderHasService('sonata_post_one_admin');
        self::assertContainerBuilderHasService('sonata_post_two_admin');

        $definition = $this->container->findDefinition('sonata_post_one_admin');
        static::assertSame('sonata_post_one_admin', $definition->getArgument(0));
        static::assertSame(PostEntity::class, $definition->getArgument(1));
        static::assertSame('sensiolabs.admin.controller.crud', $definition->getArgument(2));
        static::assertSame('extra_argument_1', $definition->getArgument(3));

        $definition = $this->container->findDefinition('sonata_post_two_admin');
        static::assertSame('sonata_post_two_admin', $definition->getArgument(0));
        static::assertSame(PostEntity::class, $definition->getArgument(1));
        static::assertSame('sensiolabs.admin.controller.crud', $definition->getArgument(2));
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
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => ReportTwo::class, 'manager_type' => 'orm']);

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
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => PostEntity::class, 'controller' => 'sensiolabs.admin.controller.crud', 'default' => true, 'manager_type' => 'orm']);

        $config = $this->getConfig();

        $this->extension->load([$config], $this->container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The class SensioLabs\AdminBundle\Tests\DependencyInjection\Compiler\PostEntity has two admins sonata_post_admin and sonata_post_admin_2 with the "default" attribute set to true. Only one is allowed.');

        $this->compile();
    }

    public function testAdminCodeShouldBeInjectedToPool(): void
    {
        $this->setUpContainer();

        $this->container
            ->register('sonata_foo_admin')
            ->setClass(CustomAdmin::class)
            ->setPublic(true)
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => FooEntity::class, 'code' => 'sonata_bar_admin', 'controller' => 'sensiolabs.admin.controller.crud', 'manager_type' => 'test']);

        $this->container
            ->register('sonata_baz_admin')
            ->setClass(CustomAdmin::class)
            ->setPublic(true)
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => BazEntity::class, 'default' => true, 'code' => 'sonata_qux_admin', 'controller' => 'sensiolabs.admin.controller.crud', 'manager_type' => 'test']);

        $this->extension->load([$this->getConfig()], $this->container);
        $this->container->getDefinition('sensiolabs.admin.pool')->setPublic(true);

        $this->compile();

        self::assertContainerBuilderHasService('sensiolabs.admin.pool');

        $pool = $this->container->get('sensiolabs.admin.pool');
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
     * @return array<string, mixed>
     *
     * @phpstan-return array{
     *     default_admin_services: array{pager_type: string},
     *     templates: array{filter_theme: list<string>, form_theme: list<string>},
     * }
     */
    protected function getConfig(): array
    {
        return [
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
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => NewsEntity::class, 'controller' => 'sensiolabs.admin.controller.crud', 'label' => '5 Entry', 'manager_type' => 'orm'])
            ->addMethodCall('setModelManager', [new Reference('my.model.manager')]);
        $this->container
            ->register('sonata_post_admin')
            ->setClass(CustomAdmin::class)
            ->setPublic(true)
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => PostEntity::class, 'controller' => 'sensiolabs.admin.controller.crud', 'default' => true, 'manager_type' => 'orm']);
        $this->container
            ->register('sonata_article_admin')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => ArticleEntity::class, 'controller' => 'sensiolabs.admin.controller.crud', 'label' => '1 Entry', 'manager_type' => 'doctrine_mongodb'])
            ->addMethodCall('setFormTheme', [['custom_form_theme.twig']])
            ->addMethodCall('setFilterTheme', [['custom_filter_theme.twig']]);
        $this->container
            ->register('sonata_report_admin')
            ->setPublic(true)
            ->setClass(CustomAdmin::class)
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => Report::class, 'controller' => 'sensiolabs.admin.controller.crud', 'manager_type' => 'orm']);
        $this->container
            ->register('sonata_report_one_admin')
            ->setClass(CustomAdmin::class)
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => ReportOne::class, 'controller' => 'sensiolabs.admin.controller.crud', 'manager_type' => 'orm', 'show_mosaic_button' => false]);
        $this->container
            ->register('sonata_report_two_admin')
            ->setClass(CustomAdmin::class)
            ->addTag(TaggedAdminInterface::ADMIN_TAG, ['model_class' => ReportTwo::class, 'controller' => 'sensiolabs.admin.controller.crud', 'manager_type' => 'orm', 'show_mosaic_button' => true]);

        // translator
        $this->container
            ->register('translator.default')
            ->setClass(Translator::class);
        $this->container->setAlias('translator', 'translator.default');
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
final class CustomAdmin extends AbstractAdmin
{
}

final class Report
{
}
final class ReportOne
{
}
final class ReportTwo
{
}
final class NewsEntity
{
}
final class PostEntity
{
}
final class ArticleEntity
{
}
final class FooEntity
{
}
final class BazEntity
{
}
