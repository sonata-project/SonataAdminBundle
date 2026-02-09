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

namespace SensioLabs\AdminBundle\DependencyInjection;

use SensioLabs\AdminBundle\Dashboard\DashboardControllerInterface;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AddAuditReadersCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use SensioLabs\AdminBundle\Model\AuditReaderInterface;
use SensioLabs\AdminBundle\Model\ModelManagerInterface;
use SensioLabs\AdminBundle\Util\AdminAclUserManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
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
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Michael Williams <michael.williams@funsational.com>
 *
 * @phpstan-import-type SonataAdminConfiguration from Configuration
 * @phpstan-import-type SonataAdminAsset from Configuration
 */
final class SensioLabsAdminExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        // Register the bundle's assets with AssetMapper for importmap support
        if ($this->isAssetMapperAvailable($container)) {
            // Go up from src/DependencyInjection to bundle root, then into assets/
            $bundleAssetsPath = \dirname(__DIR__, 2).'/assets';

            $container->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        // The namespace must match the package name in assets/package.json
                        $bundleAssetsPath => '@sensiolabs-de/admin-bundle',
                    ],
                ],
            ]);
        }

        // Prepend Doctrine ORM mapping for user entities when user management is enabled
        $this->prependUserDoctrineMapping($container);
    }

    private function prependUserDoctrineMapping(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());

        $userEnabled = false;
        foreach ($configs as $config) {
            if (isset($config['user']['enabled']) && true === $config['user']['enabled']) {
                $userEnabled = true;

                break;
            }
        }

        if (!$userEnabled) {
            return;
        }

        $bundles = $container->getParameter('kernel.bundles');
        \assert(\is_array($bundles));

        if (!isset($bundles['DoctrineBundle'])) {
            return;
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'SensioLabsAdminUserBundle' => [
                        'type' => 'attribute',
                        'dir' => \dirname(__DIR__).'/User/Entity',
                        'prefix' => 'SensioLabs\AdminBundle\User\Entity',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);
    }

    private function isAssetMapperAvailable(ContainerBuilder $container): bool
    {
        if (!interface_exists(AssetMapperInterface::class)) {
            return false;
        }

        // Check if FrameworkBundle has AssetMapper support
        $dependencies = $container->getParameter('kernel.bundles_metadata');
        \assert(\is_array($dependencies));

        if (!isset($dependencies['FrameworkBundle'])) {
            return false;
        }

        return is_file($dependencies['FrameworkBundle']['path'].'/Resources/config/asset_mapper.php');
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        \assert(\is_array($bundles));

        if (isset($bundles['SonataIntlBundle'])) {
            // integrate the SonataIntlBundle if the bundle exists
            array_unshift($configs, [
                'templates' => [
                    'history_revision_timestamp' => '@SonataIntl/CRUD/history_revision_timestamp.html.twig',
                ],
            ]);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('actions.php');
        $loader->load('commands.php');
        $loader->load('core.php');
        $loader->load('dashboard.php');
        $loader->load('event_listener.php');
        $loader->load('form_types.php');
        $loader->load('route.php');
        $loader->load('twig.php');

        if (isset($bundles['MakerBundle'])) {
            $loader->load('makers.php');
        }

        if (isset($bundles['SonataExporterBundle'])) {
            $loader->load('exporter.php');
        }

        // Auto-configure dashboard controllers
        $container->registerForAutoconfiguration(DashboardControllerInterface::class)
            ->addTag('sensiolabs.admin.dashboard_controller')
            ->addMethodCall('setPool', [new Reference('sensiolabs.admin.pool')])
            ->addMethodCall('setTemplateRegistry', [new Reference('sensiolabs.admin.global_template_registry')])
            ->addMethodCall('setTwig', [new Reference('twig')]);

        $configuration = $this->getConfiguration($configs, $container);
        \assert(null !== $configuration);

        /** @phpstan-var SonataAdminConfiguration $config */
        $config = $this->processConfiguration($configuration, $configs);

        $javascript = $this->buildJavascripts($config);
        $stylesheet = $this->buildStylesheets($config);

        $config['options']['javascripts'] = $javascript;
        $config['options']['stylesheets'] = $stylesheet;
        $config['options']['role_admin'] = $config['security']['role_admin'];
        $config['options']['role_super_admin'] = $config['security']['role_super_admin'];

        $sonataConfiguration = $container->getDefinition('sensiolabs.admin.configuration');
        $sonataConfiguration->replaceArgument(0, $config['title']);
        $sonataConfiguration->replaceArgument(1, $config['title_logo']);
        $sonataConfiguration->replaceArgument(2, $config['options']);

        if (false === $config['options']['lock_protection']) {
            $container->removeDefinition('sensiolabs.admin.lock.extension');
        }

        $container->setParameter('sensiolabs.admin.configuration.templates', $config['templates']);
        $container->setParameter('sensiolabs.admin.configuration.default_admin_services', $config['default_admin_services']);
        $container->setParameter('sensiolabs.admin.configuration.default_controller', $config['default_controller']);
        $container->setParameter(
            'sensiolabs.admin.configuration.mosaic_background',
            $config['options']['mosaic_background']
        );
        $container->setParameter('sensiolabs.admin.configuration.default_translation_domain', $config['options']['default_translation_domain']);
        $container->setParameter('sensiolabs.admin.configuration.default_icon', $config['options']['default_icon']);
        $container->setParameter('sensiolabs.admin.configuration.breadcrumbs', $config['breadcrumbs']);

        if (null !== $config['security']['acl_user_manager']) {
            $container->setAlias('sensiolabs.admin.security.acl_user_manager', $config['security']['acl_user_manager']);
            $container->setAlias(AdminAclUserManagerInterface::class, 'sensiolabs.admin.security.acl_user_manager');
        }

        $container->setAlias('sensiolabs.admin.security.handler', $config['security']['handler']);

        switch ($config['security']['handler']) {
            case 'sensiolabs.admin.security.handler.role':
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
            case 'sensiolabs.admin.security.handler.acl':
                if (!isset($bundles['AclBundle'])) {
                    throw new \RuntimeException(
                        'The "symfony/acl-bundle" is needed to use ACL as security handler.'
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

        $container->setParameter('sensiolabs.admin.configuration.security.role_admin', $config['security']['role_admin']);
        $container->setParameter('sensiolabs.admin.configuration.security.role_super_admin', $config['security']['role_super_admin']);
        $container->setParameter('sensiolabs.admin.configuration.security.information', $config['security']['information']);
        $container->setParameter('sensiolabs.admin.configuration.security.admin_permissions', $config['security']['admin_permissions']);
        $container->setParameter('sensiolabs.admin.configuration.security.object_permissions', $config['security']['object_permissions']);

        $loader->load('security.php');

        if (interface_exists(ObjectIdentityInterface::class)) {
            // only load this in case the optional symfony/security-acl package is installed
            $loader->load('acl.php');
        }

        $container->setParameter('sensiolabs.admin.extension.map', $config['extensions']);

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

        $container->getDefinition('sensiolabs.admin.form.extension.field')
            ->replaceArgument(0, $classes)
            ->replaceArgument(1, $config['options']);

        // remove non-Mopa compatibility layer
        if (isset($bundles['MopaBootstrapBundle'])) {
            $container->removeDefinition('sensiolabs.admin.form.extension.field.mopa');
        }

        // set filter persistence
        $container->setParameter('sensiolabs.admin.configuration.filters.persist', $config['persist_filters']);
        $container->setParameter('sensiolabs.admin.configuration.filters.persister', $config['filter_persister']);

        $container->setParameter('sensiolabs.admin.configuration.show.mosaic.button', $config['show_mosaic_button']);

        $this->replacePropertyAccessor($container);

        $container
            ->registerForAutoconfiguration(ModelManagerInterface::class)
            ->addTag(ModelManagerCompilerPass::MANAGER_TAG);

        $container
            ->registerForAutoconfiguration(AuditReaderInterface::class)
            ->addTag(AddAuditReadersCompilerPass::AUDIT_READER_TAG);

        // Load ORM configuration only if DoctrineBundle is registered
        if (isset($bundles['DoctrineBundle'])) {
            $this->loadORMConfiguration($configs, $container, $bundles);
        }

        // Load User management configuration
        if (true === ($config['user']['enabled'] ?? false)) {
            $this->loadUserConfiguration($config['user'], $container);
        }
    }

    /**
     * @param array<mixed> $configs
     * @param array<string, class-string> $bundles
     */
    private function loadORMConfiguration(array $configs, ContainerBuilder $container, array $bundles): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/orm'));
        $loader->load('doctrine_orm.php');
        $loader->load('doctrine_orm_filter_types.php');

        if (isset($bundles['SimpleThingsEntityAuditBundle'])) {
            $loader->load('audit.php');
            $container->setParameter('sensiolabs_doctrine_orm_admin.audit.force', true);
        }

        if (interface_exists(ObjectIdentityInterface::class)) {
            $loader->load('security.php');
        }

        $container->setParameter('sensiolabs_doctrine_orm_admin.entity_manager', null);
        $container->setParameter('sensiolabs_doctrine_orm_admin.templates', [
            'types' => [
                'list' => [],
                'show' => [],
            ],
        ]);

        // Define the templates (empty by default, can be overridden)
        $container->getDefinition('sensiolabs.admin.builder.orm_list')
            ->replaceArgument(1, []);

        $container->getDefinition('sensiolabs.admin.builder.orm_show')
            ->replaceArgument(1, []);
    }

    public function getNamespace(): string
    {
        return 'https://sonata-project.org/schema/dic/admin';
    }

    public function getAlias(): string
    {
        return 'sensiolabs_admin';
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return SonataAdminAsset[]
     *
     * @phpstan-param SonataAdminConfiguration $config
     */
    private function buildStylesheets(array $config): array
    {
        return $this->mergeArray(
            $config['assets']['stylesheets'],
            $config['assets']['extra_stylesheets'],
            $config['assets']['remove_stylesheets']
        );
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return SonataAdminAsset[]
     *
     * @phpstan-param SonataAdminConfiguration $config
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
     * @param array<int, SonataAdminAsset> $array
     * @param array<int, SonataAdminAsset> $addArray
     * @param array<int, SonataAdminAsset> $removeArray
     *
     * @return array<int, SonataAdminAsset>
     */
    private function mergeArray(array $array, array $addArray, array $removeArray = []): array
    {
        foreach ($addArray as $toAdd) {
            $array[] = $toAdd;
        }
        foreach ($removeArray as $toRemove) {
            foreach ($array as $i => $item) {
                if (
                    $item['path'] === $toRemove['path']
                    && $item['package_name'] === $toRemove['package_name']
                ) {
                    array_splice($array, $i, 1);
                    break;
                }
            }
        }

        return $array;
    }

    private function replacePropertyAccessor(ContainerBuilder $container): void
    {
        if (!$container->has('form.property_accessor')) {
            return;
        }

        $pool = $container->getDefinition('sensiolabs.admin.pool');
        $pool->replaceArgument(4, new Reference('form.property_accessor'));

        $modelChoice = $container->getDefinition('sensiolabs.admin.form.type.model_choice');
        $modelChoice->replaceArgument(0, new Reference('form.property_accessor'));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function loadUserConfiguration(array $config, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // Set parameters
        $container->setParameter('sensiolabs.admin.user.class.user', $config['class']['user']);
        $container->setParameter('sensiolabs.admin.user.class.group', $config['class']['group']);

        $container->setParameter('sensiolabs.admin.user.admin.user.class', $config['admin']['user']['class']);
        $container->setParameter('sensiolabs.admin.user.admin.user.controller', $config['admin']['user']['controller']);
        $container->setParameter('sensiolabs.admin.user.admin.user.translation_domain', $config['admin']['user']['translation_domain']);

        $container->setParameter('sensiolabs.admin.user.admin.group.class', $config['admin']['group']['class']);
        $container->setParameter('sensiolabs.admin.user.admin.group.controller', $config['admin']['group']['controller']);
        $container->setParameter('sensiolabs.admin.user.admin.group.translation_domain', $config['admin']['group']['translation_domain']);

        $container->setParameter('sensiolabs.admin.user.resetting.ttl', $config['resetting']['ttl']);
        $container->setParameter('sensiolabs.admin.user.resetting.from_email', $config['resetting']['from_email']);
        $container->setParameter('sensiolabs.admin.user.resetting.email_template', $config['resetting']['email_template']);

        $container->setParameter('sensiolabs.admin.user.profile.default_avatar', $config['profile']['default_avatar']);

        // Override user_block template to use the user-aware version
        $templates = $container->getParameter('sensiolabs.admin.configuration.templates');
        \assert(\is_array($templates));
        $templates['user_block'] = '@SensioLabsAdmin/User/Core/user_block.html.twig';
        $container->setParameter('sensiolabs.admin.configuration.templates', $templates);

        // Load service configurations
        $loader->load('user.php');
        $loader->load('user_actions.php');
        $loader->load('user_admin.php');
        $loader->load('user_mailer.php');

        // Wire user admin service ID into global variables
        $globalDef = $container->getDefinition('sensiolabs.admin.user.twig.global');
        $globalDef->addMethodCall('setUserAdminServiceId', ['sensiolabs.admin.user.admin.user']);
    }
}
