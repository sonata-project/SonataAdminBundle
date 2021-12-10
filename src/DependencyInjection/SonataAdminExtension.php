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

use Sonata\AdminBundle\DependencyInjection\Compiler\AddAuditReadersCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use Sonata\AdminBundle\Model\AuditReaderInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Util\AdminAclUserManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Michael Williams <michael.williams@funsational.com>
 */
final class SonataAdminExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        \assert(\is_array($bundles));

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

        if (isset($bundles['MakerBundle'])) {
            $loader->load('makers.php');
        }

        if (isset($bundles['SonataExporterBundle'])) {
            $loader->load('exporter.php');
        }

        $configuration = $this->getConfiguration($configs, $container);
        \assert(null !== $configuration);
        $config = $this->processConfiguration($configuration, $configs);

        $config['options']['javascripts'] = $this->buildJavascripts($config);
        $config['options']['stylesheets'] = $this->buildStylesheets($config);
        $config['options']['role_admin'] = $config['security']['role_admin'];
        $config['options']['role_super_admin'] = $config['security']['role_super_admin'];
        $config['options']['search'] = $config['search'];

        $sonataConfiguration = $container->getDefinition('sonata.admin.configuration');
        $sonataConfiguration->replaceArgument(0, $config['title']);
        $sonataConfiguration->replaceArgument(1, $config['title_logo']);
        $sonataConfiguration->replaceArgument(2, $config['options']);

        if (false === $config['options']['lock_protection']) {
            $container->removeDefinition('sonata.admin.lock.extension');
        }

        $container->setParameter('sonata.admin.configuration.global_search.empty_boxes', $config['global_search']['empty_boxes']);
        $container->setParameter('sonata.admin.configuration.global_search.admin_route', $config['global_search']['admin_route']);
        $container->setParameter('sonata.admin.configuration.templates', $config['templates']);
        $container->setParameter('sonata.admin.configuration.default_admin_services', $config['default_admin_services']);
        $container->setParameter('sonata.admin.configuration.default_controller', $config['default_controller']);
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

        if (null !== $config['security']['acl_user_manager']) {
            $container->setAlias('sonata.admin.security.acl_user_manager', $config['security']['acl_user_manager']);
            $container->setAlias(AdminAclUserManagerInterface::class, 'sonata.admin.security.acl_user_manager');
        }

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
                if (!$container->has('security.acl.provider')) {
                    throw new \RuntimeException(
                        'The "security.acl.provider" service is needed to use ACL as security handler.'
                        .' You MUST install and enable the "symfony/acl-bundle" bundle.'
                    );
                }

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

        // remove non-Mopa compatibility layer
        if (isset($bundles['MopaBootstrapBundle'])) {
            $container->removeDefinition('sonata.admin.form.extension.field.mopa');
        }

        // set filter persistence
        $container->setParameter('sonata.admin.configuration.filters.persist', $config['persist_filters']);
        $container->setParameter('sonata.admin.configuration.filters.persister', $config['filter_persister']);

        $container->setParameter('sonata.admin.configuration.show.mosaic.button', $config['show_mosaic_button']);

        $this->replacePropertyAccessor($container);

        $container
            ->registerForAutoconfiguration(ModelManagerInterface::class)
            ->addTag(ModelManagerCompilerPass::MANAGER_TAG);

        $container
            ->registerForAutoconfiguration(AuditReaderInterface::class)
            ->addTag(AddAuditReadersCompilerPass::AUDIT_READER_TAG);
    }

    public function getNamespace(): string
    {
        return 'https://sonata-project.org/schema/dic/admin';
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return string[]
     */
    private function buildStylesheets(array $config): array
    {
        $config['assets']['stylesheets'][] = sprintf(
            'bundles/sonataadmin/admin-lte-skins/%s.min.css',
            $config['options']['skin']
        );

        return $this->mergeArray(
            $config['assets']['stylesheets'],
            $config['assets']['extra_stylesheets'],
            $config['assets']['remove_stylesheets']
        );
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return string[]
     */
    private function buildJavascripts(array $config): array
    {
        return $this->mergeArray(
            $config['assets']['javascripts'],
            $config['assets']['extra_javascripts'],
            $config['assets']['remove_javascripts']
        );
    }

    /**
     * @param array<int, string> $array
     * @param array<int, string> $addArray
     * @param array<int, string> $removeArray
     *
     * @return array<int, string>
     */
    private function mergeArray(array $array, array $addArray, array $removeArray = []): array
    {
        foreach ($addArray as $toAdd) {
            $array[] = $toAdd;
        }
        foreach ($removeArray as $toRemove) {
            $key = array_search($toRemove, $array, true);
            if (false !== $key) {
                array_splice($array, $key, 1);
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
}
