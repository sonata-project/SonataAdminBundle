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
        $this->load([
            'assets' => [
                'extra_stylesheets' => [
                    'foo/bar.css',
                    'bar/quux.css',
                ],
            ],
        ]);
        $stylesheets = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['stylesheets'];

        $this->assertEquals($stylesheets, [
            'bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css',
            'bundles/sonatacore/vendor/components-font-awesome/css/font-awesome.min.css',
            'bundles/sonatacore/vendor/ionicons/css/ionicons.min.css',
            'bundles/sonataadmin/vendor/admin-lte/dist/css/AdminLTE.min.css',
            'bundles/sonataadmin/vendor/admin-lte/dist/css/skins/skin-black.min.css',
            'bundles/sonataadmin/vendor/iCheck/skins/square/blue.css',
            'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
            'bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css',
            'bundles/sonatacore/vendor/select2/select2.css',
            'bundles/sonatacore/vendor/select2-bootstrap-css/select2-bootstrap.min.css',
            'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css',
            'bundles/sonataadmin/css/styles.css',
            'bundles/sonataadmin/css/layout.css',
            'bundles/sonataadmin/css/tree.css',
            'foo/bar.css',
            'bar/quux.css',
        ]);
    }

    public function testRemoveStylesheetsGetRemoved()
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load([
            'assets' => [
                'remove_stylesheets' => [
                    'bundles/sonataadmin/vendor/admin-lte/dist/css/skins/skin-black.min.css',
                    'bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css',
                ],
            ],
        ]);

        $stylesheets = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['stylesheets'];

        $this->assertEquals($stylesheets, [
            'bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css',
            'bundles/sonatacore/vendor/components-font-awesome/css/font-awesome.min.css',
            'bundles/sonatacore/vendor/ionicons/css/ionicons.min.css',
            'bundles/sonataadmin/vendor/admin-lte/dist/css/AdminLTE.min.css',
            'bundles/sonataadmin/vendor/iCheck/skins/square/blue.css',
            'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
            'bundles/sonatacore/vendor/select2/select2.css',
            'bundles/sonatacore/vendor/select2-bootstrap-css/select2-bootstrap.min.css',
            'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css',
            'bundles/sonataadmin/css/styles.css',
            'bundles/sonataadmin/css/layout.css',
            'bundles/sonataadmin/css/tree.css',
        ]);
    }

    public function testExtraJavascriptsGetAdded()
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load([
            'assets' => [
                'extra_javascripts' => [
                    'foo/bar.js',
                    'bar/quux.js',
                ],
            ],
        ]);
        $javascripts = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['javascripts'];

        $this->assertEquals($javascripts, [
            'bundles/sonatacore/vendor/jquery/dist/jquery.min.js',
            'bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js',
            'bundles/sonatacore/vendor/moment/min/moment.min.js',
            'bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js',
            'bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js',
            'bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js',
            'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
            'bundles/sonataadmin/vendor/jquery-form/jquery.form.js',
            'bundles/sonataadmin/jquery/jquery.confirmExit.js',
            'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js',
            'bundles/sonatacore/vendor/select2/select2.min.js',
            'bundles/sonataadmin/vendor/admin-lte/dist/js/app.min.js',
            'bundles/sonataadmin/vendor/iCheck/icheck.min.js',
            'bundles/sonataadmin/vendor/slimScroll/jquery.slimscroll.min.js',
            'bundles/sonataadmin/vendor/waypoints/lib/jquery.waypoints.min.js',
            'bundles/sonataadmin/vendor/waypoints/lib/shortcuts/sticky.min.js',
            'bundles/sonataadmin/vendor/readmore-js/readmore.min.js',
            'bundles/sonataadmin/vendor/masonry/dist/masonry.pkgd.min.js',
            'bundles/sonataadmin/Admin.js',
            'bundles/sonataadmin/treeview.js',
            'bundles/sonataadmin/sidebar.js',
            'foo/bar.js',
            'bar/quux.js',
        ]);
    }

    public function testRemoveJavascriptsGetRemoved()
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load([
            'assets' => [
                'remove_javascripts' => [
                    'bundles/sonataadmin/vendor/readmore-js/readmore.min.js',
                    'bundles/sonataadmin/jquery/jquery.confirmExit.js',
                ],
            ],
        ]);
        $javascripts = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['javascripts'];

        $this->assertEquals($javascripts, [
            'bundles/sonatacore/vendor/jquery/dist/jquery.min.js',
            'bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js',
            'bundles/sonatacore/vendor/moment/min/moment.min.js',
            'bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js',
            'bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js',
            'bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js',
            'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
            'bundles/sonataadmin/vendor/jquery-form/jquery.form.js',
            'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js',
            'bundles/sonatacore/vendor/select2/select2.min.js',
            'bundles/sonataadmin/vendor/admin-lte/dist/js/app.min.js',
            'bundles/sonataadmin/vendor/iCheck/icheck.min.js',
            'bundles/sonataadmin/vendor/slimScroll/jquery.slimscroll.min.js',
            'bundles/sonataadmin/vendor/waypoints/lib/jquery.waypoints.min.js',
            'bundles/sonataadmin/vendor/waypoints/lib/shortcuts/sticky.min.js',
            'bundles/sonataadmin/vendor/masonry/dist/masonry.pkgd.min.js',
            'bundles/sonataadmin/Admin.js',
            'bundles/sonataadmin/treeview.js',
            'bundles/sonataadmin/sidebar.js',
        ]);
    }

    public function testAssetsCanBeAddedAndRemoved()
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load([
            'assets' => [
                'extra_stylesheets' => [
                    'foo/bar.css',
                    'bar/quux.css',
                ],
                'remove_stylesheets' => [
                    'bundles/sonataadmin/vendor/admin-lte/dist/css/skins/skin-black.min.css',
                    'bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css',
                ],
                'extra_javascripts' => [
                    'foo/bar.js',
                    'bar/quux.js',
                ],
                'remove_javascripts' => [
                    'bundles/sonataadmin/vendor/readmore-js/readmore.min.js',
                    'bundles/sonataadmin/jquery/jquery.confirmExit.js',
                ],
            ],
        ]);
        $stylesheets = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['stylesheets'];
        $javascripts = $this->container->getDefinition('sonata.admin.pool')->getArgument(3)['javascripts'];

        $this->assertEquals($stylesheets, [
            'bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css',
            'bundles/sonatacore/vendor/components-font-awesome/css/font-awesome.min.css',
            'bundles/sonatacore/vendor/ionicons/css/ionicons.min.css',
            'bundles/sonataadmin/vendor/admin-lte/dist/css/AdminLTE.min.css',
            'bundles/sonataadmin/vendor/iCheck/skins/square/blue.css',
            'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
            'bundles/sonatacore/vendor/select2/select2.css',
            'bundles/sonatacore/vendor/select2-bootstrap-css/select2-bootstrap.min.css',
            'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css',
            'bundles/sonataadmin/css/styles.css',
            'bundles/sonataadmin/css/layout.css',
            'bundles/sonataadmin/css/tree.css',
            'foo/bar.css',
            'bar/quux.css',
        ]);

        $this->assertEquals($javascripts, [
            'bundles/sonatacore/vendor/jquery/dist/jquery.min.js',
            'bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js',
            'bundles/sonatacore/vendor/moment/min/moment.min.js',
            'bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js',
            'bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js',
            'bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js',
            'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
            'bundles/sonataadmin/vendor/jquery-form/jquery.form.js',
            'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js',
            'bundles/sonatacore/vendor/select2/select2.min.js',
            'bundles/sonataadmin/vendor/admin-lte/dist/js/app.min.js',
            'bundles/sonataadmin/vendor/iCheck/icheck.min.js',
            'bundles/sonataadmin/vendor/slimScroll/jquery.slimscroll.min.js',
            'bundles/sonataadmin/vendor/waypoints/lib/jquery.waypoints.min.js',
            'bundles/sonataadmin/vendor/waypoints/lib/shortcuts/sticky.min.js',
            'bundles/sonataadmin/vendor/masonry/dist/masonry.pkgd.min.js',
            'bundles/sonataadmin/Admin.js',
            'bundles/sonataadmin/treeview.js',
            'bundles/sonataadmin/sidebar.js',
            'foo/bar.js',
            'bar/quux.js',
        ]);
    }

    protected function getContainerExtensions()
    {
        return [new SonataAdminExtension()];
    }
}
