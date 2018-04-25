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

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;

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

    protected function setUp()
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

    /**
     * @group legacy
     */
    public function testContainerCompileWithJMSDiExtraBundle()
    {
        $this->container->setParameter('kernel.bundles', [
            'JMSDiExtraBundle' => true,
        ]);

        $this->container->compile();
    }

    public function testHasServiceDefinitionForLockExtension()
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load(['options' => ['lock_protection' => true]]);
        $this->assertContainerBuilderHasService('sonata.admin.lock.extension');
    }

    public function testNotHasServiceDefinitionForLockExtension()
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load(['options' => ['lock_protection' => false]]);
        $this->assertContainerBuilderNotHasService('sonata.admin.lock.extension');
    }

    public function testLoadsExporterServiceDefinitionWhenExporterBundleIsRegistered()
    {
        $this->container->setParameter('kernel.bundles', ['SonataExporterBundle' => 'whatever']);
        $this->load();
        $this->assertContainerBuilderHasService(
            'sonata.admin.admin_exporter',
            AdminExporter::class
        );
    }

    public function testHasSecurityRoleParameters()
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();

        $this->assertContainerBuilderHasParameter('sonata.admin.configuration.security.role_admin');
        $this->assertContainerBuilderHasParameter('sonata.admin.configuration.security.role_super_admin');
    }

    public function testExtraStylesheetsGetAdded()
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

    public function testRemoveStylesheetsGetRemoved()
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

    public function testExtraJavascriptsGetAdded()
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

    public function testRemoveJavascriptsGetRemoved()
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

    public function testAssetsCanBeAddedAndRemoved()
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
