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

namespace Sonata\AdminBundle\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use Sonata\AdminBundle\Model\ModelManagerInterface;
// NEXT_MAJOR: Uncomment this line.
//use Sonata\AdminBundle\Util\AdminAclUserManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as SymfonyDateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType as SymfonyDateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType as SymfonyEmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType as SymfonyIntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType as SymfonyTextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType as SymfonyTextType;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Michael Williams <michael.williams@funsational.com>
 */
class SonataAdminExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['SonataUserBundle'])) {
            // integrate the SonataUserBundle / FOSUserBundle if the bundle exists
            array_unshift($configs, [
                'templates' => [
                    'user_block' => '@SonataUser/Admin/Core/user_block.html.twig',
                ],
            ]);
        }

        if (isset($bundles['SonataIntlBundle'])) {
            // integrate the SonataUserBundle if the bundle exists
            array_unshift($configs, [
                'templates' => [
                    'history_revision_timestamp' => '@SonataIntl/CRUD/history_revision_timestamp.html.twig',
                ],
            ]);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('actions.php');
        $loader->load('block.php');
        $loader->load('commands.php');
        $loader->load('core.php');
        $loader->load('event_listener.php');
        $loader->load('form_types.php');
        $loader->load('menu.php');
        $loader->load('route.php');
        $loader->load('twig.php');
        $loader->load('validator.php');

        if (isset($bundles['MakerBundle'])) {
            $loader->load('makers.php');
        }

        if (isset($bundles['SonataExporterBundle'])) {
            $loader->load('exporter.php');
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->configureTwigTextExtension($container, $loader, $config);

        $config['options']['javascripts'] = $this->buildJavascripts($config);
        $config['options']['stylesheets'] = $this->buildStylesheets($config);
        $config['options']['role_admin'] = $config['security']['role_admin'];
        $config['options']['role_super_admin'] = $config['security']['role_super_admin'];
        $config['options']['search'] = $config['search'];

        // NEXT_MAJOR: Remove this Pool configuration.
        $pool = $container->getDefinition('sonata.admin.pool');
        $pool->replaceArgument(1, $config['title']);
        $pool->replaceArgument(2, $config['title_logo']);
        $pool->replaceArgument(3, $config['options']);

        $sonataConfiguration = $container->getDefinition('sonata.admin.configuration');
        $sonataConfiguration->replaceArgument(0, $config['title']);
        $sonataConfiguration->replaceArgument(1, $config['title_logo']);
        $sonataConfiguration->replaceArgument(2, $config['options']);

        if (false === $config['options']['lock_protection']) {
            $container->removeDefinition('sonata.admin.lock.extension');
        }

        $container->setParameter('sonata.admin.configuration.global_search.empty_boxes', $config['global_search']['empty_boxes']);
        $container->setParameter('sonata.admin.configuration.global_search.case_sensitive', $config['global_search']['case_sensitive']);
        $container->setParameter('sonata.admin.configuration.templates', $config['templates']);
        $container->setParameter('sonata.admin.configuration.admin_services', $config['admin_services']);
        $container->setParameter('sonata.admin.configuration.dashboard_groups', $config['dashboard']['groups']);
        $container->setParameter('sonata.admin.configuration.dashboard_blocks', $config['dashboard']['blocks']);
        $container->setParameter('sonata.admin.configuration.sort_admins', $config['options']['sort_admins']);
        $container->setParameter(
            'sonata.admin.configuration.mosaic_background',
            $config['options']['mosaic_background']
        );
        $container->setParameter('sonata.admin.configuration.default_group', $config['options']['default_group']);
        $container->setParameter('sonata.admin.configuration.default_label_catalogue', $config['options']['default_label_catalogue']);
        $container->setParameter('sonata.admin.configuration.default_icon', $config['options']['default_icon']);
        $container->setParameter('sonata.admin.configuration.breadcrumbs', $config['breadcrumbs']);

        // NEXT_MAJOR: Remove this block and uncomment the one below.
        if (null === $config['security']['acl_user_manager'] && isset($bundles['FOSUserBundle'])) {
            $container->setParameter('sonata.admin.security.fos_user_autoconfigured', true);
            $container->setParameter('sonata.admin.security.acl_user_manager', 'fos_user.user_manager');
        } else {
            $container->setParameter('sonata.admin.security.fos_user_autoconfigured', false);
            $container->setParameter('sonata.admin.security.acl_user_manager', $config['security']['acl_user_manager']);
        }

        // NEXT_MAJOR: Uncomment this code.
        //if (null !== $config['security']['acl_user_manager']) {
        //    $container->setAlias('sonata.admin.security.acl_user_manager', $config['security']['acl_user_manager']);
        //    $container->setAlias(AdminAclUserManagerInterface::class, 'sonata.admin.security.acl_user_manager');
        //}

        $container->setAlias('sonata.admin.security.handler', $config['security']['handler']);

        switch ($config['security']['handler']) {
            case 'sonata.admin.security.handler.role':
                if (0 === \count($config['security']['information'])) {
                    $config['security']['information'] = [
                        'EDIT' => ['EDIT'],
                        'LIST' => ['LIST'],
                        'CREATE' => ['CREATE'],
                        'VIEW' => ['VIEW'],
                        'DELETE' => ['DELETE'],
                        'EXPORT' => ['EXPORT'],
                        'ALL' => ['ALL'],
                    ];
                }

                break;
            case 'sonata.admin.security.handler.acl':
                if (0 === \count($config['security']['information'])) {
                    $config['security']['information'] = [
                        'GUEST' => ['VIEW', 'LIST'],
                        'STAFF' => ['EDIT', 'LIST', 'CREATE'],
                        'EDITOR' => ['OPERATOR', 'EXPORT'],
                        'ADMIN' => ['MASTER'],
                    ];
                }

                break;
        }

        $container->setParameter('sonata.admin.configuration.security.role_admin', $config['security']['role_admin']);
        $container->setParameter('sonata.admin.configuration.security.role_super_admin', $config['security']['role_super_admin']);
        $container->setParameter('sonata.admin.configuration.security.information', $config['security']['information']);
        $container->setParameter('sonata.admin.configuration.security.admin_permissions', $config['security']['admin_permissions']);
        $container->setParameter('sonata.admin.configuration.security.object_permissions', $config['security']['object_permissions']);

        $loader->load('security.php');

        $container->setParameter('sonata.admin.extension.map', $config['extensions']);

        /*
         * This is a work in progress, so for now it is hardcoded
         */
        $classes = [
            SymfonyChoiceType::class => '',
            SymfonyDateType::class => 'sonata-medium-date',
            SymfonyDateTimeType::class => 'sonata-medium-date',
            SymfonyEmailType::class => '',
            SymfonyIntegerType::class => '',
            SymfonyTextareaType::class => '',
            SymfonyTextType::class => '',
        ];

        $container->getDefinition('sonata.admin.form.extension.field')
            ->replaceArgument(0, $classes)
            ->replaceArgument(1, $config['options']);

        // NEXT_MAJOR: Remove this block
        if (!isset($bundles['JMSTranslationBundle'])) {
            $container->removeDefinition('sonata.admin.translator.extractor.jms_translator_bundle');
        }

        // remove non-Mopa compatibility layer
        if (isset($bundles['MopaBootstrapBundle'])) {
            $container->removeDefinition('sonata.admin.form.extension.field.mopa');
        }

        // set filter persistence
        $container->setParameter('sonata.admin.configuration.filters.persist', $config['persist_filters']);
        $container->setParameter('sonata.admin.configuration.filters.persister', $config['filter_persister']);

        $container->setParameter('sonata.admin.configuration.show.mosaic.button', $config['show_mosaic_button']);

        $container->setParameter('sonata.admin.configuration.translate_group_label', $config['translate_group_label']);

        $this->replacePropertyAccessor($container);

        $container
            ->registerForAutoconfiguration(ModelManagerInterface::class)
            ->addTag(ModelManagerCompilerPass::MANAGER_TAG);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container)
    {
    }

    /**
     * NEXT_MAJOR: remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.56
     */
    public function configureClassesToCompile()
    {
    }

    public function getNamespace()
    {
        return 'https://sonata-project.org/schema/dic/admin';
    }

    private function buildStylesheets(array $config): array
    {
        $config['assets']['stylesheets'][] = sprintf(
            'bundles/sonataadmin/vendor/admin-lte/dist/css/skins/%s.min.css',
            $config['options']['skin']
        );

        return $this->mergeArray(
            $config['assets']['stylesheets'],
            $config['assets']['extra_stylesheets'],
            $config['assets']['remove_stylesheets']
        );
    }

    private function buildJavascripts(array $config): array
    {
        return $this->mergeArray(
            $config['assets']['javascripts'],
            $config['assets']['extra_javascripts'],
            $config['assets']['remove_javascripts']
        );
    }

    private function mergeArray(array $array, array $addArray, array $removeArray = []): array
    {
        foreach ($addArray as $toAdd) {
            $array[] = $toAdd;
        }
        foreach ($removeArray as $toRemove) {
            if (\in_array($toRemove, $array, true)) {
                array_splice($array, array_search($toRemove, $array, true), 1);
            }
        }

        return $array;
    }

    private function replacePropertyAccessor(ContainerBuilder $container): void
    {
        if (!$container->has('form.property_accessor')) {
            return;
        }

        $pool = $container->getDefinition('sonata.admin.pool');
        $pool->replaceArgument(4, new Reference('form.property_accessor'));

        $modelChoice = $container->getDefinition('sonata.admin.form.type.model_choice');
        $modelChoice->replaceArgument(0, new Reference('form.property_accessor'));
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    private function configureTwigTextExtension(ContainerBuilder $container, PhpFileLoader $loader, array $config): void
    {
        $container->setParameter('sonata.admin.configuration.legacy_twig_text_extension', $config['options']['legacy_twig_text_extension']);
        $loader->load('twig_string.php');

        if (false !== $config['options']['legacy_twig_text_extension']) {
            $container
                ->getDefinition('sonata.string.twig.extension')
                ->replaceArgument(0, new Reference('sonata.deprecated_text.twig.extension'));
        }
    }
}
