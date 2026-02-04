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

namespace SensioLabs\AdminBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use SensioLabs\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Bridge\Exporter\AdminExporter;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AddAuditReadersCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Configuration;
use SensioLabs\AdminBundle\DependencyInjection\SensioLabsAdminExtension;
use SensioLabs\AdminBundle\Filter\FilterFactoryInterface;
use SensioLabs\AdminBundle\Filter\Persister\FilterPersisterInterface;
use SensioLabs\AdminBundle\Model\AuditManagerInterface;
use SensioLabs\AdminBundle\Model\AuditReaderInterface;
use SensioLabs\AdminBundle\Model\ModelManagerInterface;
use SensioLabs\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @phpstan-import-type SonataAdminConfiguration from Configuration
 * @phpstan-import-type SonataAdminAsset from Configuration
 */
final class SensioLabsAdminExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @var array<string, mixed>
     *
     * @phpstan-var SonataAdminConfiguration
     */
    private array $defaultConfiguration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container->setParameter('kernel.bundles', []);

        /** @phpstan-var SonataAdminConfiguration $config */
        $config = (new Processor())->processConfiguration(new Configuration(), []);
        $this->defaultConfiguration = $config;
    }

    public function testHasCoreServicesAlias(): void
    {
        $this->load();

        self::assertContainerBuilderHasService(Pool::class);
        self::assertContainerBuilderHasService(FilterFactoryInterface::class);
        self::assertContainerBuilderHasService(BreadcrumbsBuilderInterface::class);
        self::assertContainerBuilderHasService(LabelTranslatorStrategyInterface::class);
        self::assertContainerBuilderHasService(AuditManagerInterface::class);
        self::assertContainerBuilderHasService(FilterPersisterInterface::class);
    }

    public function testHasServiceDefinitionForLockExtension(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load(['options' => ['lock_protection' => true]]);
        self::assertContainerBuilderHasService('sonata.admin.lock.extension');
    }

    public function testNotHasServiceDefinitionForLockExtension(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load(['options' => ['lock_protection' => false]]);
        self::assertContainerBuilderNotHasService('sonata.admin.lock.extension');
    }

    public function testLoadsExporterServiceDefinitionWhenExporterBundleIsRegistered(): void
    {
        $this->container->setParameter('kernel.bundles', ['SonataExporterBundle' => 'whatever']);
        $this->load();
        self::assertContainerBuilderHasService(
            'sonata.admin.admin_exporter',
            AdminExporter::class
        );
    }

    public function testHasSecurityRoleParameters(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();

        self::assertContainerBuilderHasParameter('sonata.admin.configuration.security.role_admin');
        self::assertContainerBuilderHasParameter('sonata.admin.configuration.security.role_super_admin');
    }

    public function testHasDefaultServiceParameters(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();

        self::assertContainerBuilderHasParameter('sonata.admin.configuration.default_group');
        self::assertContainerBuilderHasParameter('sonata.admin.configuration.default_label_catalogue');
        self::assertContainerBuilderHasParameter('sonata.admin.configuration.default_translation_domain');
        self::assertContainerBuilderHasParameter('sonata.admin.configuration.default_icon');
        self::assertContainerBuilderHasParameter('sonata.admin.configuration.default_controller');
    }

    public function testExtraStylesheetsGetAdded(): void
    {
        $this->container->setParameter('kernel.bundles', []);

        $extraStylesheets = [
            'foo/bar.css',
            'bar/quux.css',
            ['path' => 'foo/bazz.css', 'package_name' => 'another_package'],
            ['path' => 'bar/asd.css', 'package_name' => null],
        ];

        $extraStylesheetsNormalized = [
            ['path' => 'foo/bar.css', 'package_name' => 'sonata_admin'],
            ['path' => 'bar/quux.css', 'package_name' => 'sonata_admin'],
            ['path' => 'foo/bazz.css', 'package_name' => 'another_package'],
            ['path' => 'bar/asd.css', 'package_name' => null],
        ];

        $this->load([
            'assets' => [
                'extra_stylesheets' => $extraStylesheets,
            ],
        ]);

        $options = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2);
        static::assertIsArray($options);

        $stylesheets = $options['stylesheets'];
        static::assertSame(
            array_merge($this->getDefaultStylesheets(), $extraStylesheetsNormalized),
            $stylesheets
        );
    }

    public function testRemoveStylesheetsGetRemoved(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $removeStylesheets = [
            'bundles/sonataadmin/app.css',
            'bundles/sonataadmin/admin-lte-skins/skin-black.min.css',
        ];
        $this->load([
            'assets' => [
                'remove_stylesheets' => $removeStylesheets,
            ],
        ]);

        $options = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2);
        static::assertIsArray($options);

        $stylesheets = $options['stylesheets'];
        static::assertIsArray($stylesheets);

        $expected = array_values(
            array_filter(
                $this->defaultConfiguration['assets']['stylesheets'],
                static fn (array $item) => !\in_array($item['path'], $removeStylesheets, true)
            )
        );

        static::assertSame($expected, $stylesheets);
    }

    public function testExtraJavascriptsGetAdded(): void
    {
        $this->container->setParameter('kernel.bundles', []);

        $extraJavascripts = [
            'foo/bar.js',
            'bar/quux.js',
            ['path' => 'foo/bazz.js', 'package_name' => 'another_package'],
            ['path' => 'bar/asd.js', 'package_name' => null],
        ];

        $extraJavascriptsNormalized = [
            ['path' => 'foo/bar.js', 'package_name' => 'sonata_admin'],
            ['path' => 'bar/quux.js', 'package_name' => 'sonata_admin'],
            ['path' => 'foo/bazz.js', 'package_name' => 'another_package'],
            ['path' => 'bar/asd.js', 'package_name' => null],
        ];

        $this->load([
            'assets' => [
                'extra_javascripts' => $extraJavascripts,
            ],
        ]);

        $options = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2);
        static::assertIsArray($options);

        $javascripts = $options['javascripts'];
        static::assertSame(
            [...$this->defaultConfiguration['assets']['javascripts'], ...$extraJavascriptsNormalized],
            $javascripts
        );
    }

    public function testRemoveJavascriptsGetRemoved(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $removeJavascripts = [
            'bundles/sonataadmin/app.js',
        ];
        $this->load([
            'assets' => [
                'remove_javascripts' => $removeJavascripts,
            ],
        ]);

        $options = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2);
        static::assertIsArray($options);

        $javascripts = $options['javascripts'];
        static::assertIsArray($javascripts);

        $expected = array_values(
            array_filter(
                $this->defaultConfiguration['assets']['javascripts'],
                static fn (array $item) => !\in_array($item['path'], $removeJavascripts, true)
            )
        );

        static::assertSame($expected, $javascripts);
    }

    public function testAssetsCanBeAddedAndRemoved(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $extraStylesheets = [
            'foo/bar.css',
            'bar/quux.css',
            ['path' => 'foo/bazz.css', 'package_name' => 'another_package'],
            ['path' => 'bar/asd.css', 'package_name' => null],
        ];
        $extraStylesheetsNormalized = [
            ['path' => 'foo/bar.css', 'package_name' => 'sonata_admin'],
            ['path' => 'bar/quux.css', 'package_name' => 'sonata_admin'],
            ['path' => 'foo/bazz.css', 'package_name' => 'another_package'],
            ['path' => 'bar/asd.css', 'package_name' => null],
        ];
        $extraJavascripts = [
            'foo/bar.js',
            'bar/quux.js',
            ['path' => 'foo/bazz.js', 'package_name' => 'another_package'],
            ['path' => 'bar/asd.js', 'package_name' => null],
        ];
        $extraJavascriptsNormalized = [
            ['path' => 'foo/bar.js', 'package_name' => 'sonata_admin'],
            ['path' => 'bar/quux.js', 'package_name' => 'sonata_admin'],
            ['path' => 'foo/bazz.js', 'package_name' => 'another_package'],
            ['path' => 'bar/asd.js', 'package_name' => null],
        ];
        $removeStylesheets = [
            'bundles/sonataadmin/app.css',
            'bundles/sonataadmin/admin-lte-skins/skin-black.min.css',
        ];
        $removeJavascripts = [
            'bundles/sonataadmin/app.js',
        ];
        $this->load([
            'assets' => [
                'extra_stylesheets' => $extraStylesheets,
                'remove_stylesheets' => $removeStylesheets,
                'extra_javascripts' => $extraJavascripts,
                'remove_javascripts' => $removeJavascripts,
            ],
        ]);

        $options = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2);
        static::assertIsArray($options);

        $stylesheets = $options['stylesheets'];

        static::assertSame(
            [
                ...array_filter(
                    $this->defaultConfiguration['assets']['stylesheets'],
                    static fn (array $item) => !\in_array($item['path'], $removeStylesheets, true)
                ),
                ...$extraStylesheetsNormalized,
            ],
            $stylesheets
        );

        $javascripts = $options['javascripts'];
        static::assertSame(
            [
                ...array_filter(
                    $this->defaultConfiguration['assets']['javascripts'],
                    static fn (array $item) => !\in_array($item['path'], $removeJavascripts, true)
                ),
                ...$extraJavascriptsNormalized,
            ],
            $javascripts
        );
    }

    public function testDefaultTemplates(): void
    {
        $this->load();

        static::assertSame([
            'user_block' => '@SensioLabsAdmin/Core/user_block.html.twig',
            'add_block' => '@SensioLabsAdmin/Core/add_block.html.twig',
            'layout' => '@SensioLabsAdmin/standard_layout.html.twig',
            'ajax' => '@SensioLabsAdmin/ajax_layout.html.twig',
            'dashboard' => '@SensioLabsAdmin/Core/dashboard.html.twig',
            'search' => '@SensioLabsAdmin/Core/search.html.twig',
            'list' => '@SensioLabsAdmin/CRUD/list.html.twig',
            'filter' => '@SensioLabsAdmin/Form/filter_admin_fields.html.twig',
            'show' => '@SensioLabsAdmin/CRUD/show.html.twig',
            'show_compare' => '@SensioLabsAdmin/CRUD/show_compare.html.twig',
            'edit' => '@SensioLabsAdmin/CRUD/edit.html.twig',
            'preview' => '@SensioLabsAdmin/CRUD/preview.html.twig',
            'history' => '@SensioLabsAdmin/CRUD/history.html.twig',
            'acl' => '@SensioLabsAdmin/CRUD/acl.html.twig',
            'history_revision_timestamp' => '@SensioLabsAdmin/CRUD/history_revision_timestamp.html.twig',
            'action' => '@SensioLabsAdmin/CRUD/action.html.twig',
            'select' => '@SensioLabsAdmin/CRUD/list__select.html.twig',
            'list_block' => '@SensioLabsAdmin/Block/block_admin_list.html.twig',
            'search_result_block' => '@SensioLabsAdmin/Block/block_search_result.html.twig',
            'short_object_description' => '@SensioLabsAdmin/Helper/short-object-description.html.twig',
            'delete' => '@SensioLabsAdmin/CRUD/delete.html.twig',
            'batch' => '@SensioLabsAdmin/CRUD/list__batch.html.twig',
            'batch_confirmation' => '@SensioLabsAdmin/CRUD/batch_confirmation.html.twig',
            'inner_list_row' => '@SensioLabsAdmin/CRUD/list_inner_row.html.twig',
            'outer_list_rows_mosaic' => '@SensioLabsAdmin/CRUD/list_outer_rows_mosaic.html.twig',
            'outer_list_rows_list' => '@SensioLabsAdmin/CRUD/list_outer_rows_list.html.twig',
            'outer_list_rows_tree' => '@SensioLabsAdmin/CRUD/list_outer_rows_tree.html.twig',
            'base_list_field' => '@SensioLabsAdmin/CRUD/base_list_field.html.twig',
            'pager_links' => '@SensioLabsAdmin/Pager/links.html.twig',
            'pager_results' => '@SensioLabsAdmin/Pager/results.html.twig',
            'tab_menu_template' => '@SensioLabsAdmin/Core/tab_menu_template.html.twig',
            'knp_menu_template' => '@SensioLabsAdmin/Menu/sonata_menu.html.twig',
            'action_create' => '@SensioLabsAdmin/CRUD/dashboard__action_create.html.twig',
            'button_acl' => '@SensioLabsAdmin/Button/acl_button.html.twig',
            'button_create' => '@SensioLabsAdmin/Button/create_button.html.twig',
            'button_edit' => '@SensioLabsAdmin/Button/edit_button.html.twig',
            'button_history' => '@SensioLabsAdmin/Button/history_button.html.twig',
            'button_list' => '@SensioLabsAdmin/Button/list_button.html.twig',
            'button_show' => '@SensioLabsAdmin/Button/show_button.html.twig',
            'form_theme' => [],
            'filter_theme' => [],
        ], $this->container->getParameter('sonata.admin.configuration.templates'));
    }

    public function testLoadIntlTemplate(): void
    {
        $bundles = $this->container->getParameter('kernel.bundles');
        static::assertIsArray($bundles);

        $this->container->setParameter('kernel.bundles', array_merge($bundles, ['SonataIntlBundle' => true]));
        $this->load();

        $templates = $this->container->getParameter('sonata.admin.configuration.templates');
        static::assertIsArray($templates);
        static::assertSame('@SonataIntl/CRUD/history_revision_timestamp.html.twig', $templates['history_revision_timestamp']);
    }

    public function testDefaultSkin(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();

        $options = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2);
        static::assertIsArray($options);

        $stylesheets = $options['stylesheets'];
        static::assertSame($this->getDefaultStylesheets(), $stylesheets);

        $skin = $options['skin'];
        static::assertSame('skin-black', $skin);
    }

    public function testSetSkin(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load([
            'options' => [
                'skin' => 'skin-blue',
            ],
        ]);

        $options = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2);
        static::assertIsArray($options);

        $stylesheets = $options['stylesheets'];
        static::assertSame($this->getDefaultStylesheets('skin-blue'), $stylesheets);

        $skin = $options['skin'];
        static::assertSame('skin-blue', $skin);
    }

    public function testSetDefaultSkin(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load([
            'options' => [
                'skin' => 'skin-black',
            ],
        ]);

        $options = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2);
        static::assertIsArray($options);

        $stylesheets = $options['stylesheets'];
        static::assertSame($this->getDefaultStylesheets(), $stylesheets);

        $skin = $options['skin'];
        static::assertSame('skin-black', $skin);
    }

    public function testSetInvalidSkin(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The value "skin-invalid" is not allowed for path "sonata_admin.options.skin". Permissible values: "skin-black", "skin-black-light", "skin-blue", "skin-blue-light", "skin-green", "skin-green-light", "skin-purple", "skin-purple-light", "skin-red", "skin-red-light", "skin-yellow", "skin-yellow-light"');
        $this->container->setParameter('kernel.bundles', []);
        $this->load([
            'options' => [
                'skin' => 'skin-invalid',
            ],
        ]);
    }

    public function testAutoregisterAddingTagsToServices(): void
    {
        $this->load();

        $autoconfiguredInstancesOf = $this->container->getAutoconfiguredInstanceof();

        static::assertArrayHasKey(ModelManagerInterface::class, $autoconfiguredInstancesOf);
        static::assertTrue($autoconfiguredInstancesOf[ModelManagerInterface::class]->hasTag(ModelManagerCompilerPass::MANAGER_TAG));

        static::assertArrayHasKey(AuditReaderInterface::class, $autoconfiguredInstancesOf);
        static::assertTrue($autoconfiguredInstancesOf[AuditReaderInterface::class]->hasTag(AddAuditReadersCompilerPass::AUDIT_READER_TAG));
    }

    protected function getContainerExtensions(): array
    {
        return [new SensioLabsAdminExtension()];
    }

    /**
     * @return list<SonataAdminAsset>
     */
    private function getDefaultStylesheets(?string $skin = 'skin-black'): array
    {
        $this->load([
            'options' => [
                'skin' => $skin,
            ],
        ]);

        $options = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2);
        static::assertIsArray($options);

        $skin = $options['skin'];

        $defaultStylesheets = $this->defaultConfiguration['assets']['stylesheets'];
        $defaultStylesheets[] = [
            'path' => \sprintf(
                'bundles/sonataadmin/admin-lte-skins/%s.min.css',
                $skin
            ),
            'package_name' => 'sonata_admin',
        ];

        return $defaultStylesheets;
    }
}
