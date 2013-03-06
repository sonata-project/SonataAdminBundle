<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * SonataAdminBundleExtension
 *
 * @author      Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author      Michael Williams <michael.williams@funsational.com>
 */
class SonataAdminExtension extends Extension
{
    /**
     *
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
                    'user_block' => 'SonataUserBundle:Admin/Core:user_block.html.twig'
                )
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

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $pool = $container->getDefinition('sonata.admin.pool');
        $pool->replaceArgument(1, $config['title']);
        $pool->replaceArgument(2, $config['title_logo']);

        $container->setParameter('sonata.admin.configuration.templates', $config['templates']);
        $container->setParameter('sonata.admin.configuration.admin_services', $config['admin_services']);
        $container->setParameter('sonata.admin.configuration.dashboard_groups', $config['dashboard']['groups']);
        $container->setParameter('sonata.admin.configuration.dashboard_blocks', $config['dashboard']['blocks']);

        $container->setAlias('sonata.admin.security.handler', $config['security']['handler']);

        switch ($config['security']['handler']) {
            case 'sonata.admin.security.handler.role':
                if (count($config['security']['information']) === 0) {
                    $config['security']['information'] = array(
                        'EDIT'      => array('EDIT'),
                        'LIST'      => array('LIST'),
                        'CREATE'    => array('CREATE'),
                        'VIEW'      => array('VIEW'),
                        'DELETE'    => array('DELETE'),
                        'EXPORT'    => array('EXPORT'),
                        'OPERATOR'  => array('OPERATOR'),
                        'MASTER'    => array('MASTER'),
                    );
                }
                break;
            case 'sonata.admin.security.handler.acl':
                if (count($config['security']['information']) === 0) {
                    $config['security']['information'] = array(
                        'GUEST'    => array('VIEW', 'LIST'),
                        'STAFF'    => array('EDIT', 'LIST', 'CREATE'),
                        'EDITOR'   => array('OPERATOR', 'EXPORT'),
                        'ADMIN'    => array('MASTER'),
                    );
                }
                break;
        }

        $container->setParameter('sonata.admin.configuration.security.information', $config['security']['information']);
        $container->setParameter('sonata.admin.configuration.security.admin_permissions', $config['security']['admin_permissions']);
        $container->setParameter('sonata.admin.configuration.security.object_permissions', $config['security']['object_permissions']);

        $loader->load('security.xml');

        /**
         * This is a work in progress, so for now it is hardcoded
         */
        $classes = array(
            'email'    => 'span5',
            'textarea' => 'span5',
            'text'     => 'span5',
            'choice'   => 'span5',
            'integer'  => 'span5',
            'datetime' => 'sonata-medium-date',
            'date'     => 'sonata-medium-date'
        );

        $container->getDefinition('sonata.admin.form.extension.field')
            ->replaceArgument(0, $classes);

        // remove non used service
        if (!isset($bundles['JMSTranslationBundle'])) {
            $container->removeDefinition('sonata.admin.translator.extractor.jms_translator_bundle');
        }

        // set filter persistence
        $container->setParameter('sonata.admin.configuration.filters.persist', $config['persist_filters']);

        $this->configureClassesToCompile();
    }

    public function configureClassesToCompile()
    {
        $this->addClassesToCompile(array(
            "Sonata\\AdminBundle\\Admin\\Admin",
            "Sonata\\AdminBundle\\Admin\\AdminExtension",
            "Sonata\\AdminBundle\\Admin\\AdminExtensionInterface",
            "Sonata\\AdminBundle\\Admin\\AdminHelper",
            "Sonata\\AdminBundle\\Admin\\AdminInterface",
            "Sonata\\AdminBundle\\Admin\\BaseFieldDescription",
            "Sonata\\AdminBundle\\Admin\\FieldDescriptionCollection",
            "Sonata\\AdminBundle\\Admin\\FieldDescriptionInterface",
            "Sonata\\AdminBundle\\Admin\\Pool",
            "Sonata\\AdminBundle\\Block\\AdminListBlockService",
            "Sonata\\AdminBundle\\Builder\\DatagridBuilderInterface",
            "Sonata\\AdminBundle\\Builder\\FormContractorInterface",
            "Sonata\\AdminBundle\\Builder\\ListBuilderInterface",
            "Sonata\\AdminBundle\\Builder\\RouteBuilderInterface",
            "Sonata\\AdminBundle\\Builder\\ShowBuilderInterface",
            "Sonata\\AdminBundle\\Datagrid\\Datagrid",
            "Sonata\\AdminBundle\\Datagrid\\DatagridInterface",
            "Sonata\\AdminBundle\\Datagrid\\DatagridMapper",
            "Sonata\\AdminBundle\\Datagrid\\ListMapper",
            "Sonata\\AdminBundle\\Datagrid\\Pager",
            "Sonata\\AdminBundle\\Datagrid\\PagerInterface",
            "Sonata\\AdminBundle\\Datagrid\\ProxyQueryInterface",
            "Sonata\\AdminBundle\\Exception\\ModelManagerException",
            "Sonata\\AdminBundle\\Exception\\NoValueException",
            "Sonata\\AdminBundle\\Export\\Exporter",
            "Sonata\\AdminBundle\\Filter\\Filter",
            "Sonata\\AdminBundle\\Filter\\FilterFactory",
            "Sonata\\AdminBundle\\Filter\\FilterFactoryInterface",
            "Sonata\\AdminBundle\\Filter\\FilterInterface",
            "Sonata\\AdminBundle\\Form\\ChoiceList\\ModelChoiceList",
            "Sonata\\AdminBundle\\Form\\DataTransformer\\ArrayToModelTransformer",
            "Sonata\\AdminBundle\\Form\\DataTransformer\\ModelsToArrayTransformer",
            "Sonata\\AdminBundle\\Form\\DataTransformer\\ModelToIdTransformer",
            "Sonata\\AdminBundle\\Form\\EventListener\\MergeCollectionListener",
            "Sonata\\AdminBundle\\Form\\EventListener\\ResizeFormListener",
            "Sonata\\AdminBundle\\Form\\Extension\\Field\\Type\\FormTypeFieldExtension",
            "Sonata\\AdminBundle\\Form\\FormMapper",
            "Sonata\\AdminBundle\\Form\\Type\\AdminType",
            "Sonata\\AdminBundle\\Form\\Type\\BooleanType",
            "Sonata\\AdminBundle\\Form\\Type\\CollectionType",
            "Sonata\\AdminBundle\\Form\\Type\\DateRangeType",
            "Sonata\\AdminBundle\\Form\\Type\\DateTimeRangeType",
            "Sonata\\AdminBundle\\Form\\Type\\EqualType",
            "Sonata\\AdminBundle\\Form\\Type\\Filter\\ChoiceType",
            "Sonata\\AdminBundle\\Form\\Type\\Filter\\DateRangeType",
            "Sonata\\AdminBundle\\Form\\Type\\Filter\\DateTimeRangeType",
            "Sonata\\AdminBundle\\Form\\Type\\Filter\\DateTimeType",
            "Sonata\\AdminBundle\\Form\\Type\\Filter\\DateType",
            "Sonata\\AdminBundle\\Form\\Type\\Filter\\DefaultType",
            "Sonata\\AdminBundle\\Form\\Type\\Filter\\NumberType",
            "Sonata\\AdminBundle\\Form\\Type\\ImmutableArrayType",
            "Sonata\\AdminBundle\\Form\\Type\\ModelReferenceType",
            "Sonata\\AdminBundle\\Form\\Type\\ModelType",
            "Sonata\\AdminBundle\\Form\\Type\\ModelTypeList",
            "Sonata\\AdminBundle\\Form\\Type\\TranslatableChoiceType",
            "Sonata\\AdminBundle\\Guesser\\TypeGuesserChain",
            "Sonata\\AdminBundle\\Guesser\\TypeGuesserInterface",
            "Sonata\\AdminBundle\\Model\\AuditManager",
            "Sonata\\AdminBundle\\Model\\AuditManagerInterface",
            "Sonata\\AdminBundle\\Model\\AuditReaderInterface",
            "Sonata\\AdminBundle\\Model\\ModelManagerInterface",
            "Sonata\\AdminBundle\\Route\\AdminPoolLoader",
            "Sonata\\AdminBundle\\Route\\DefaultRouteGenerator",
            "Sonata\\AdminBundle\\Route\\PathInfoBuilder",
            "Sonata\\AdminBundle\\Route\\QueryStringBuilder",
            "Sonata\\AdminBundle\\Route\\RouteCollection",
            "Sonata\\AdminBundle\\Route\\RouteGeneratorInterface",
            "Sonata\\AdminBundle\\Security\\Acl\\Permission\\AdminPermissionMap",
            "Sonata\\AdminBundle\\Security\\Acl\\Permission\\MaskBuilder",
            "Sonata\\AdminBundle\\Security\\Handler\\AclSecurityHandler",
            "Sonata\\AdminBundle\\Security\\Handler\\AclSecurityHandlerInterface",
            "Sonata\\AdminBundle\\Security\\Handler\\NoopSecurityHandler",
            "Sonata\\AdminBundle\\Security\\Handler\\RoleSecurityHandler",
            "Sonata\\AdminBundle\\Security\\Handler\\SecurityHandlerInterface",
            "Sonata\\AdminBundle\\Show\\ShowMapper",
            "Sonata\\AdminBundle\\Translator\\BCLabelTranslatorStrategy",
            "Sonata\\AdminBundle\\Translator\\FormLabelTranslatorStrategy",
            "Sonata\\AdminBundle\\Translator\\LabelTranslatorStrategyInterface",
            "Sonata\\AdminBundle\\Translator\\NativeLabelTranslatorStrategy",
            "Sonata\\AdminBundle\\Translator\\NoopLabelTranslatorStrategy",
            "Sonata\\AdminBundle\\Translator\\UnderscoreLabelTranslatorStrategy",
            "Sonata\\AdminBundle\\Twig\\Extension\\SonataAdminExtension",
            "Sonata\\AdminBundle\\Util\\AdminAclManipulator",
            "Sonata\\AdminBundle\\Util\\AdminAclManipulatorInterface",
            "Sonata\\AdminBundle\\Util\\FormBuilderIterator",
            "Sonata\\AdminBundle\\Util\\FormViewIterator",
            "Sonata\\AdminBundle\\Util\\ObjectAclManipulator",
            "Sonata\\AdminBundle\\Util\\ObjectAclManipulatorInterface",
            "Sonata\\AdminBundle\\Validator\\Constraints\\InlineConstraint",
            "Sonata\\AdminBundle\\Validator\\ErrorElement",
            "Sonata\\AdminBundle\\Validator\\InlineValidator",
        ));
    }
}
