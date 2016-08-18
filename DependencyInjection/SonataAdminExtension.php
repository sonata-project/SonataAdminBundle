<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Michael Williams <michael.williams@funsational.com>
 */
class SonataAdminExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array            $configs   An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['SonataUserBundle'])) {
            // integrate the SonataUserBundle / FOSUserBundle if the bundle exists
            array_unshift($configs, array(
                'templates' => array(
                    'user_block' => 'SonataUserBundle:Admin/Core:user_block.html.twig',
                ),
            ));
        }

        if (isset($bundles['SonataIntlBundle'])) {
            // integrate the SonataUserBundle if the bundle exists
            array_unshift($configs, array(
                'templates' => array(
                    'history_revision_timestamp' => 'SonataIntlBundle:CRUD:history_revision_timestamp.html.twig',
                ),
            ));
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('templates.xml');
        $loader->load('twig.xml');
        $loader->load('core.xml');
        $loader->load('form_types.xml');
        $loader->load('validator.xml');
        $loader->load('route.xml');
        $loader->load('block.xml');
        $loader->load('menu.xml');

        if (isset($bundles['SonataExporterBundle'])) {
            $loader->load('exporter.xml');
        }

        // NEXT_MAJOR : remove this block
        if (method_exists('Symfony\Component\DependencyInjection\Definition', 'setDeprecated')) {
            $container->getDefinition('sonata.admin.exporter')->setDeprecated(
                'The service "%service_id%" is deprecated in favor of the "sonata.exporter.exporter" service'
            );
        }

        // TODO: Go back on xml configuration when bumping requirements to SF 2.6+
        $sidebarMenu = $container->getDefinition('sonata.admin.sidebar_menu');
        if (method_exists($sidebarMenu, 'setFactory')) {
            $sidebarMenu->setFactory(array(new Reference('sonata.admin.menu_builder'), 'createSidebarMenu'));
        } else {
            $sidebarMenu->setFactoryService('sonata.admin.menu_builder');
            $sidebarMenu->setFactoryMethod('createSidebarMenu');
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $config['options']['javascripts'] = $config['assets']['javascripts'];
        $config['options']['stylesheets'] = $config['assets']['stylesheets'];

        $pool = $container->getDefinition('sonata.admin.pool');
        $pool->replaceArgument(1, $config['title']);
        $pool->replaceArgument(2, $config['title_logo']);
        $pool->replaceArgument(3, $config['options']);

        if (false === $config['options']['lock_protection']) {
            $container->removeDefinition('sonata.admin.lock.extension');
        }

        $container->setParameter('sonata.admin.configuration.templates', $config['templates']);
        $container->setParameter('sonata.admin.configuration.admin_services', $config['admin_services']);
        $container->setParameter('sonata.admin.configuration.dashboard_groups', $config['dashboard']['groups']);
        $container->setParameter('sonata.admin.configuration.dashboard_blocks', $config['dashboard']['blocks']);
        $container->setParameter('sonata.admin.configuration.sort_admins', $config['options']['sort_admins']);
        $container->setParameter('sonata.admin.configuration.breadcrumbs', $config['breadcrumbs']);

        if (null === $config['security']['acl_user_manager'] && isset($bundles['FOSUserBundle'])) {
            $container->setParameter('sonata.admin.security.acl_user_manager', 'fos_user.user_manager');
        } else {
            $container->setParameter('sonata.admin.security.acl_user_manager', $config['security']['acl_user_manager']);
        }

        $container->setAlias('sonata.admin.security.handler', $config['security']['handler']);

        switch ($config['security']['handler']) {
            case 'sonata.admin.security.handler.role':
                if (count($config['security']['information']) === 0) {
                    $config['security']['information'] = array(
                        'EDIT' => array('EDIT'),
                        'LIST' => array('LIST'),
                        'CREATE' => array('CREATE'),
                        'VIEW' => array('VIEW'),
                        'DELETE' => array('DELETE'),
                        'EXPORT' => array('EXPORT'),
                        'ALL' => array('ALL'),
                    );
                }
                break;
            case 'sonata.admin.security.handler.acl':
                if (count($config['security']['information']) === 0) {
                    $config['security']['information'] = array(
                        'GUEST' => array('VIEW', 'LIST'),
                        'STAFF' => array('EDIT', 'LIST', 'CREATE'),
                        'EDITOR' => array('OPERATOR', 'EXPORT'),
                        'ADMIN' => array('MASTER'),
                    );
                }
                break;
        }

        $container->setParameter('sonata.admin.configuration.security.information', $config['security']['information']);
        $container->setParameter('sonata.admin.configuration.security.admin_permissions', $config['security']['admin_permissions']);
        $container->setParameter('sonata.admin.configuration.security.object_permissions', $config['security']['object_permissions']);

        $loader->load('security.xml');

        // Set the SecurityContext for Symfony <2.6
        // NEXT_MAJOR: Go back to simple xml configuration when bumping requirements to SF 2.6+
        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $tokenStorageReference = new Reference('security.token_storage');
            $authorizationCheckerReference = new Reference('security.authorization_checker');
        } else {
            $tokenStorageReference = new Reference('security.context');
            $authorizationCheckerReference = new Reference('security.context');
        }

        $container
            ->getDefinition('sonata.admin.security.handler.role')
            ->replaceArgument(0, $authorizationCheckerReference)
        ;

        $container
            ->getDefinition('sonata.admin.security.handler.acl')
            ->replaceArgument(0, $tokenStorageReference)
            ->replaceArgument(1, $authorizationCheckerReference)
        ;

        $container
            ->getDefinition('sonata.admin.menu.group_provider')
            ->replaceArgument(2, $authorizationCheckerReference)
        ;

        $container->setParameter('sonata.admin.extension.map', $config['extensions']);

        /*
         * This is a work in progress, so for now it is hardcoded
         */
        $classes = array(
            'email' => '',
            'textarea' => '',
            'text' => '',
            'choice' => '',
            'integer' => '',
            'datetime' => 'sonata-medium-date',
            'date' => 'sonata-medium-date',

            // SF3+
            'Symfony\Component\Form\Extension\Core\Type\ChoiceType' => '',
            'Symfony\Component\Form\Extension\Core\Type\DateType' => 'sonata-medium-date',
            'Symfony\Component\Form\Extension\Core\Type\DateTimeType' => 'sonata-medium-date',
            'Symfony\Component\Form\Extension\Core\Type\EmailType' => '',
            'Symfony\Component\Form\Extension\Core\Type\IntegerType' => '',
            'Symfony\Component\Form\Extension\Core\Type\TextareaType' => '',
            'Symfony\Component\Form\Extension\Core\Type\TextType' => '',
        );

        $container->getDefinition('sonata.admin.form.extension.field')
            ->replaceArgument(0, $classes)
            ->replaceArgument(1, $config['options']);

        // remove non used service
        if (!isset($bundles['JMSTranslationBundle'])) {
            $container->removeDefinition('sonata.admin.translator.extractor.jms_translator_bundle');
        }

        //remove non-Mopa compatibility layer
        if (isset($bundles['MopaBootstrapBundle'])) {
            $container->removeDefinition('sonata.admin.form.extension.field.mopa');
        }

        // set filter persistence
        $container->setParameter('sonata.admin.configuration.filters.persist', $config['persist_filters']);

        $container->setParameter('sonata.admin.configuration.show.mosaic.button', $config['show_mosaic_button']);

        $this->configureClassesToCompile();

        $this->replacePropertyAccessor($container);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * NEXT_MAJOR: remove all code that deals with JMSDiExtraBundle
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['JMSDiExtraBundle'])) {
            return;
        }

        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);
        if (!$config['options']['enable_jms_di_extra_autoregistration']) {
            return;
        }

        $sonataAdminPattern = 'Sonata\AdminBundle\Annotation';
        $annotationPatternsConfigured = false;

        $diExtraConfigs = $container->getExtensionConfig('jms_di_extra');
        foreach ($diExtraConfigs as $diExtraConfig) {
            if (isset($diExtraConfig['annotation_patterns'])) {
                // don't add our own pattern if user has already done so
                if (array_search($sonataAdminPattern, $diExtraConfig['annotation_patterns']) !== false) {
                    return;
                }
                $annotationPatternsConfigured = true;
                break;
            }
        }

        @trigger_error(
            'Automatic registration of annotations is deprecated since 3.14, to be removed in 4.0.',
            E_USER_DEPRECATED
        );

        if ($annotationPatternsConfigured) {
            $annotationPatterns = array($sonataAdminPattern);
        } else {
            // get annotation_patterns default from DiExtraBundle configuration
            $diExtraConfigDefinition = new \JMS\DiExtraBundle\DependencyInjection\Configuration();
            // FIXME: this will break if DiExtraBundle adds any mandatory configuration
            $diExtraConfig = $this->processConfiguration($diExtraConfigDefinition, array());

            $annotationPatterns = $diExtraConfig['annotation_patterns'];
            $annotationPatterns[] = $sonataAdminPattern;
        }

        $container->prependExtensionConfig(
            'jms_di_extra',
            array(
                'annotation_patterns' => $annotationPatterns,
            )
        );
    }

    public function configureClassesToCompile()
    {
        $this->addClassesToCompile(array(
            'Sonata\\AdminBundle\\Admin\\AbstractAdmin',
            'Sonata\\AdminBundle\\Admin\\AbstractAdminExtension',
            'Sonata\\AdminBundle\\Admin\\AdminExtensionInterface',
            'Sonata\\AdminBundle\\Admin\\AdminHelper',
            'Sonata\\AdminBundle\\Admin\\AdminInterface',
            'Sonata\\AdminBundle\\Admin\\BaseFieldDescription',
            'Sonata\\AdminBundle\\Admin\\FieldDescriptionCollection',
            'Sonata\\AdminBundle\\Admin\\FieldDescriptionInterface',
            'Sonata\\AdminBundle\\Admin\\Pool',
            'Sonata\\AdminBundle\\Block\\AdminListBlockService',
            'Sonata\\AdminBundle\\Builder\\DatagridBuilderInterface',
            'Sonata\\AdminBundle\\Builder\\FormContractorInterface',
            'Sonata\\AdminBundle\\Builder\\ListBuilderInterface',
            'Sonata\\AdminBundle\\Builder\\RouteBuilderInterface',
            'Sonata\\AdminBundle\\Builder\\ShowBuilderInterface',
            'Sonata\\AdminBundle\\Datagrid\\Datagrid',
            'Sonata\\AdminBundle\\Datagrid\\DatagridInterface',
            'Sonata\\AdminBundle\\Datagrid\\DatagridMapper',
            'Sonata\\AdminBundle\\Datagrid\\ListMapper',
            'Sonata\\AdminBundle\\Datagrid\\Pager',
            'Sonata\\AdminBundle\\Datagrid\\PagerInterface',
            'Sonata\\AdminBundle\\Datagrid\\ProxyQueryInterface',
            'Sonata\\AdminBundle\\Exception\\ModelManagerException',
            'Sonata\\AdminBundle\\Exception\\NoValueException',
            'Sonata\\AdminBundle\\Filter\\Filter',
            'Sonata\\AdminBundle\\Filter\\FilterFactory',
            'Sonata\\AdminBundle\\Filter\\FilterFactoryInterface',
            'Sonata\\AdminBundle\\Filter\\FilterInterface',
            'Sonata\\AdminBundle\\Form\\DataTransformer\\ArrayToModelTransformer',
            'Sonata\\AdminBundle\\Form\\DataTransformer\\ModelsToArrayTransformer',
            'Sonata\\AdminBundle\\Form\\DataTransformer\\ModelToIdTransformer',
            'Sonata\\AdminBundle\\Form\\EventListener\\MergeCollectionListener',
            'Sonata\\AdminBundle\\Form\\Extension\\Field\\Type\\FormTypeFieldExtension',
            'Sonata\\AdminBundle\\Form\\FormMapper',
            'Sonata\\AdminBundle\\Form\\Type\\AdminType',
            'Sonata\\AdminBundle\\Form\\Type\\Filter\\ChoiceType',
            'Sonata\\AdminBundle\\Form\\Type\\Filter\\DateRangeType',
            'Sonata\\AdminBundle\\Form\\Type\\Filter\\DateTimeRangeType',
            'Sonata\\AdminBundle\\Form\\Type\\Filter\\DateTimeType',
            'Sonata\\AdminBundle\\Form\\Type\\Filter\\DateType',
            'Sonata\\AdminBundle\\Form\\Type\\Filter\\DefaultType',
            'Sonata\\AdminBundle\\Form\\Type\\Filter\\NumberType',
            'Sonata\\AdminBundle\\Form\\Type\\ModelReferenceType',
            'Sonata\\AdminBundle\\Form\\Type\\ModelType',
            'Sonata\\AdminBundle\\Form\\Type\\ModelListType',
            'Sonata\\AdminBundle\\Guesser\\TypeGuesserChain',
            'Sonata\\AdminBundle\\Guesser\\TypeGuesserInterface',
            'Sonata\\AdminBundle\\Model\\AuditManager',
            'Sonata\\AdminBundle\\Model\\AuditManagerInterface',
            'Sonata\\AdminBundle\\Model\\AuditReaderInterface',
            'Sonata\\AdminBundle\\Model\\ModelManagerInterface',
            'Sonata\\AdminBundle\\Route\\AdminPoolLoader',
            'Sonata\\AdminBundle\\Route\\DefaultRouteGenerator',
            'Sonata\\AdminBundle\\Route\\PathInfoBuilder',
            'Sonata\\AdminBundle\\Route\\QueryStringBuilder',
            'Sonata\\AdminBundle\\Route\\RouteCollection',
            'Sonata\\AdminBundle\\Route\\RouteGeneratorInterface',
            'Sonata\\AdminBundle\\Security\\Acl\\Permission\\AdminPermissionMap',
            'Sonata\\AdminBundle\\Security\\Acl\\Permission\\MaskBuilder',
            'Sonata\\AdminBundle\\Security\\Handler\\AclSecurityHandler',
            'Sonata\\AdminBundle\\Security\\Handler\\AclSecurityHandlerInterface',
            'Sonata\\AdminBundle\\Security\\Handler\\NoopSecurityHandler',
            'Sonata\\AdminBundle\\Security\\Handler\\RoleSecurityHandler',
            'Sonata\\AdminBundle\\Security\\Handler\\SecurityHandlerInterface',
            'Sonata\\AdminBundle\\Show\\ShowMapper',
            'Sonata\\AdminBundle\\Translator\\BCLabelTranslatorStrategy',
            'Sonata\\AdminBundle\\Translator\\FormLabelTranslatorStrategy',
            'Sonata\\AdminBundle\\Translator\\LabelTranslatorStrategyInterface',
            'Sonata\\AdminBundle\\Translator\\NativeLabelTranslatorStrategy',
            'Sonata\\AdminBundle\\Translator\\NoopLabelTranslatorStrategy',
            'Sonata\\AdminBundle\\Translator\\UnderscoreLabelTranslatorStrategy',
            'Sonata\\AdminBundle\\Twig\\Extension\\SonataAdminExtension',
            'Sonata\\AdminBundle\\Util\\AdminAclManipulator',
            'Sonata\\AdminBundle\\Util\\AdminAclManipulatorInterface',
            'Sonata\\AdminBundle\\Util\\FormBuilderIterator',
            'Sonata\\AdminBundle\\Util\\FormViewIterator',
            'Sonata\\AdminBundle\\Util\\ObjectAclManipulator',
            'Sonata\\AdminBundle\\Util\\ObjectAclManipulatorInterface',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return 'https://sonata-project.org/schema/dic/admin';
    }

    private function replacePropertyAccessor(ContainerBuilder $container)
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
