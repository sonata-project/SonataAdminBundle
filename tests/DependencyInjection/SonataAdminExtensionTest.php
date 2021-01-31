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

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilder;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\DependencyInjection\Configuration;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\AdminBundle\Event\AdminEventExtension;
use Sonata\AdminBundle\Filter\FilterFactory;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface;
use Sonata\AdminBundle\Filter\Persister\SessionFilterPersister;
use Sonata\AdminBundle\Model\AuditManager;
use Sonata\AdminBundle\Model\AuditManagerInterface;
use Sonata\AdminBundle\Route\AdminPoolLoader;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\Extractor\AdminExtractor;
use Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class SonataAdminExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @var array
     */
    private $defaultConfiguration = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->container->setParameter('kernel.bundles', []);

        $this->defaultConfiguration = (new Processor())->processConfiguration(new Configuration(), []);
    }

    public function testHasCoreServicesAlias(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService(Pool::class);
        $this->assertContainerBuilderHasService(AdminPoolLoader::class);
        $this->assertContainerBuilderHasService(AdminHelper::class);
        $this->assertContainerBuilderHasService(FilterFactory::class);
        $this->assertContainerBuilderHasService(
            FilterFactoryInterface::class,
            FilterFactory::class
        );
        $this->assertContainerBuilderHasService(BreadcrumbsBuilder::class);
        $this->assertContainerBuilderHasService(
            BreadcrumbsBuilderInterface::class,
            BreadcrumbsBuilder::class
        );
        $this->assertContainerBuilderHasService(BCLabelTranslatorStrategy::class);
        $this->assertContainerBuilderHasService(NativeLabelTranslatorStrategy::class);
        $this->assertContainerBuilderHasService(
            LabelTranslatorStrategyInterface::class,
            NativeLabelTranslatorStrategy::class
        );
        $this->assertContainerBuilderHasService(NoopLabelTranslatorStrategy::class);
        $this->assertContainerBuilderHasService(UnderscoreLabelTranslatorStrategy::class);
        $this->assertContainerBuilderHasService(FormLabelTranslatorStrategy::class);
        $this->assertContainerBuilderHasService(AuditManager::class);
        $this->assertContainerBuilderHasService(AuditManagerInterface::class, AuditManager::class);
        $this->assertContainerBuilderHasService(SearchHandler::class);
        $this->assertContainerBuilderHasService(AdminEventExtension::class);
        $this->assertContainerBuilderHasService('sonata.admin.group.extension');
        $this->assertContainerBuilderHasService(SessionFilterPersister::class);
        $this->assertContainerBuilderHasService(
            FilterPersisterInterface::class,
            SessionFilterPersister::class
        );
        $this->assertContainerBuilderHasService(TemplateRegistry::class);
        $this->assertContainerBuilderHasService(AdminExtractor::class);
    }

    public function testHasServiceDefinitionForLockExtension(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load(['options' => ['lock_protection' => true]]);
        $this->assertContainerBuilderHasService('sonata.admin.lock.extension');
    }

    public function testNotHasServiceDefinitionForLockExtension(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load(['options' => ['lock_protection' => false]]);
        $this->assertContainerBuilderNotHasService('sonata.admin.lock.extension');
    }

    public function testLoadsExporterServiceDefinitionWhenExporterBundleIsRegistered(): void
    {
        $this->container->setParameter('kernel.bundles', ['SonataExporterBundle' => 'whatever']);
        $this->load();
        $this->assertContainerBuilderHasService(
            'sonata.admin.admin_exporter',
            AdminExporter::class
        );
    }

    public function testHasSecurityRoleParameters(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();

        $this->assertContainerBuilderHasParameter('sonata.admin.configuration.security.role_admin');
        $this->assertContainerBuilderHasParameter('sonata.admin.configuration.security.role_super_admin');
    }

    public function testHasDefaultServiceParameters(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();

        $this->assertContainerBuilderHasParameter('sonata.admin.configuration.default_group');
        $this->assertContainerBuilderHasParameter('sonata.admin.configuration.default_label_catalogue');
        $this->assertContainerBuilderHasParameter('sonata.admin.configuration.default_icon');
        $this->assertContainerBuilderHasParameter('sonata.admin.configuration.default_controller');
    }

    public function testExtraStylesheetsGetAdded(): void
    {
        $this->container->setParameter('kernel.bundles', []);

        $extraStylesheets = ['foo/bar.css', 'bar/quux.css'];
        $this->load([
            'assets' => [
                'extra_stylesheets' => $extraStylesheets,
            ],
        ]);

        $stylesheets = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['stylesheets'];

        $this->assertSame(
            array_merge($this->getDefaultStylesheets(), $extraStylesheets),
            $stylesheets
        );
    }

    public function testRemoveStylesheetsGetRemoved(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $removeStylesheets = [
            'bundles/sonataadmin/vendor/admin-lte/dist/css/skins/skin-black.min.css',
            'bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css',
        ];
        $this->load([
            'assets' => [
                'remove_stylesheets' => $removeStylesheets,
            ],
        ]);

        $stylesheets = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['stylesheets'];

        $this->assertSame(
            array_values(
                array_diff($this->defaultConfiguration['assets']['stylesheets'], $removeStylesheets)
            ),
            $stylesheets
        );
    }

    public function testExtraJavascriptsGetAdded(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $extraJavascripts = ['foo/bar.js', 'bar/quux.js'];
        $this->load([
            'assets' => [
                'extra_javascripts' => $extraJavascripts,
            ],
        ]);

        $javascripts = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['javascripts'];

        $this->assertSame(
            array_merge($this->defaultConfiguration['assets']['javascripts'], $extraJavascripts),
            $javascripts
        );
    }

    public function testRemoveJavascriptsGetRemoved(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $removeJavascripts = [
            'bundles/sonataadmin/vendor/readmore-js/readmore.min.js',
            'bundles/sonataadmin/jquery/jquery.confirmExit.js',
        ];
        $this->load([
            'assets' => [
                'remove_javascripts' => $removeJavascripts,
            ],
        ]);

        $javascripts = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['javascripts'];

        $this->assertSame(
            array_values(
                array_diff($this->defaultConfiguration['assets']['javascripts'], $removeJavascripts)
            ),
            $javascripts
        );
    }

    public function testAssetsCanBeAddedAndRemoved(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $extraStylesheets = ['foo/bar.css', 'bar/quux.css'];
        $extraJavascripts = ['foo/bar.js', 'bar/quux.js'];
        $removeStylesheets = [
            'bundles/sonataadmin/vendor/admin-lte/dist/css/skins/skin-black.min.css',
            'bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css',
        ];
        $removeJavascripts = [
            'bundles/sonataadmin/vendor/readmore-js/readmore.min.js',
            'bundles/sonataadmin/jquery/jquery.confirmExit.js',
        ];
        $this->load([
            'assets' => [
                'extra_stylesheets' => $extraStylesheets,
                'remove_stylesheets' => $removeStylesheets,
                'extra_javascripts' => $extraJavascripts,
                'remove_javascripts' => $removeJavascripts,
            ],
        ]);

        $stylesheets = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['stylesheets'];

        $this->assertSame(
            array_merge(
                array_diff($this->defaultConfiguration['assets']['stylesheets'], $removeStylesheets),
                $extraStylesheets
            ),
            $stylesheets
        );

        $javascripts = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['javascripts'];

        $this->assertSame(
            array_merge(
                array_diff($this->defaultConfiguration['assets']['javascripts'], $removeJavascripts),
                $extraJavascripts
            ),
            $javascripts
        );
    }

    public function testDefaultTemplates(): void
    {
        $this->load();

        $this->assertSame([
            'user_block' => '@SonataAdmin/Core/user_block.html.twig',
            'add_block' => '@SonataAdmin/Core/add_block.html.twig',
            'layout' => '@SonataAdmin/standard_layout.html.twig',
            'ajax' => '@SonataAdmin/ajax_layout.html.twig',
            'dashboard' => '@SonataAdmin/Core/dashboard.html.twig',
            'search' => '@SonataAdmin/Core/search.html.twig',
            'list' => '@SonataAdmin/CRUD/list.html.twig',
            'filter' => '@SonataAdmin/Form/filter_admin_fields.html.twig',
            'show' => '@SonataAdmin/CRUD/show.html.twig',
            'show_compare' => '@SonataAdmin/CRUD/show_compare.html.twig',
            'edit' => '@SonataAdmin/CRUD/edit.html.twig',
            'preview' => '@SonataAdmin/CRUD/preview.html.twig',
            'history' => '@SonataAdmin/CRUD/history.html.twig',
            'acl' => '@SonataAdmin/CRUD/acl.html.twig',
            'history_revision_timestamp' => '@SonataAdmin/CRUD/history_revision_timestamp.html.twig',
            'action' => '@SonataAdmin/CRUD/action.html.twig',
            'select' => '@SonataAdmin/CRUD/list__select.html.twig',
            'list_block' => '@SonataAdmin/Block/block_admin_list.html.twig',
            'search_result_block' => '@SonataAdmin/Block/block_search_result.html.twig',
            'short_object_description' => '@SonataAdmin/Helper/short-object-description.html.twig',
            'delete' => '@SonataAdmin/CRUD/delete.html.twig',
            'batch' => '@SonataAdmin/CRUD/list__batch.html.twig',
            'batch_confirmation' => '@SonataAdmin/CRUD/batch_confirmation.html.twig',
            'inner_list_row' => '@SonataAdmin/CRUD/list_inner_row.html.twig',
            'outer_list_rows_mosaic' => '@SonataAdmin/CRUD/list_outer_rows_mosaic.html.twig',
            'outer_list_rows_list' => '@SonataAdmin/CRUD/list_outer_rows_list.html.twig',
            'outer_list_rows_tree' => '@SonataAdmin/CRUD/list_outer_rows_tree.html.twig',
            'base_list_field' => '@SonataAdmin/CRUD/base_list_field.html.twig',
            'pager_links' => '@SonataAdmin/Pager/links.html.twig',
            'pager_results' => '@SonataAdmin/Pager/results.html.twig',
            'tab_menu_template' => '@SonataAdmin/Core/tab_menu_template.html.twig',
            'knp_menu_template' => '@SonataAdmin/Menu/sonata_menu.html.twig',
            'action_create' => '@SonataAdmin/CRUD/dashboard__action_create.html.twig',
            'button_acl' => '@SonataAdmin/Button/acl_button.html.twig',
            'button_create' => '@SonataAdmin/Button/create_button.html.twig',
            'button_edit' => '@SonataAdmin/Button/edit_button.html.twig',
            'button_history' => '@SonataAdmin/Button/history_button.html.twig',
            'button_list' => '@SonataAdmin/Button/list_button.html.twig',
            'button_show' => '@SonataAdmin/Button/show_button.html.twig',
        ], $this->container->getParameter('sonata.admin.configuration.templates'));
    }

    public function testLoadIntlTemplate(): void
    {
        $bundlesWithSonataIntlBundle = array_merge($this->container->getParameter('kernel.bundles'), ['SonataIntlBundle' => true]);
        $this->container->setParameter('kernel.bundles', $bundlesWithSonataIntlBundle);
        $this->load();
        $templates = $this->container->getParameter('sonata.admin.configuration.templates');
        $this->assertSame('@SonataIntl/CRUD/history_revision_timestamp.html.twig', $templates['history_revision_timestamp']);
    }

    public function testDefaultSkin(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();

        $stylesheets = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['stylesheets'];
        $skin = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['skin'];

        $this->assertSame($this->getDefaultStylesheets(), $stylesheets);
        $this->assertSame('skin-black', $skin);
    }

    public function testSetSkin(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load([
            'options' => [
                'skin' => 'skin-blue',
            ],
        ]);

        $stylesheets = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['stylesheets'];
        $skin = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['skin'];

        $this->assertSame($this->getDefaultStylesheets('skin-blue'), $stylesheets);
        $this->assertSame('skin-blue', $skin);
    }

    public function testSetDefaultSkin(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load([
            'options' => [
                'skin' => 'skin-black',
            ],
        ]);

        $stylesheets = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['stylesheets'];
        $skin = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['skin'];

        $this->assertSame($this->getDefaultStylesheets(), $stylesheets);
        $this->assertSame('skin-black', $skin);
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

    protected function getContainerExtensions(): array
    {
        return [new SonataAdminExtension()];
    }

    private function getDefaultStylesheets(?string $skin = 'skin-black'): array
    {
        $this->load([
            'options' => [
                'skin' => $skin,
            ],
        ]);

        $skin = $this->container->getDefinition('sonata.admin.configuration')->getArgument(2)['skin'];

        $defaultStylesheets = $this->defaultConfiguration['assets']['stylesheets'];
        $defaultStylesheets[] = sprintf(
            'bundles/sonataadmin/vendor/admin-lte/dist/css/skins/%s.min.css',
            $skin
        );

        return $defaultStylesheets;
    }
}
