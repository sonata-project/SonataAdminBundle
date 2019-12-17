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
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;
use Sonata\AdminBundle\Twig\GlobalVariables;

class SonataAdminExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @var string[]
     */
    private $defaultStylesheets = [];

    /**
     * @var string[]
     */
    private $defaultJavascripts = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->container->setParameter('kernel.bundles', []);
        $this->load();
        $this->defaultStylesheets = $this->container
            ->getDefinition('sonata.admin.pool')->getArgument(3)['stylesheets']
        ;
        $this->defaultJavascripts = $this->container
            ->getDefinition('sonata.admin.pool')->getArgument(3)['javascripts']
        ;
    }

    public function testHasCoreServicesAlias(): void
    {
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
        $this->assertContainerBuilderHasService(GlobalVariables::class);
        $this->assertContainerBuilderHasService(SessionFilterPersister::class);
        $this->assertContainerBuilderHasService(
            FilterPersisterInterface::class,
            SessionFilterPersister::class
        );
        $this->assertContainerBuilderHasService(TemplateRegistry::class);
        $this->assertContainerBuilderHasService(
            MutableTemplateRegistryInterface::class,
            TemplateRegistry::class
        );
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
        $stylesheets = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['stylesheets'];

        $this->assertSame(array_merge($this->defaultStylesheets, $extraStylesheets), $stylesheets);
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
        $stylesheets = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['stylesheets'];

        $this->assertSame(array_values(array_diff($this->defaultStylesheets, $removeStylesheets)), $stylesheets);
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
        $javascripts = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['javascripts'];

        $this->assertSame(array_merge($this->defaultJavascripts, $extraJavascripts), $javascripts);
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
        $javascripts = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['javascripts'];

        $this->assertSame(array_values(array_diff($this->defaultJavascripts, $removeJavascripts)), $javascripts);
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
        $stylesheets = $this->container
            ->getDefinition('sonata.admin.pool')->getArgument(3)['stylesheets']
        ;

        $this->assertSame(
            array_merge(array_diff($this->defaultStylesheets, $removeStylesheets), $extraStylesheets),
            $stylesheets
        );

        $javascripts = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['javascripts'];

        $this->assertSame(
            array_merge(array_diff($this->defaultJavascripts, $removeJavascripts), $extraJavascripts),
            $javascripts
        );
    }

    protected function getContainerExtensions()
    {
        return [new SonataAdminExtension()];
    }
}
