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

use JMS\DiExtraBundle\DependencyInjection\Configuration as JMSDiExtraBundleDependencyInjectionConfiguration;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Block\AdminListBlockService;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Filter\Filter;
use Sonata\AdminBundle\Filter\FilterFactory;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Form\EventListener\MergeCollectionListener;
use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeType;
use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelReferenceType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Guesser\TypeGuesserChain;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Model\AuditManager;
use Sonata\AdminBundle\Model\AuditManagerInterface;
use Sonata\AdminBundle\Model\AuditReaderInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\AdminPoolLoader;
use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Sonata\AdminBundle\Route\PathInfoBuilder;
use Sonata\AdminBundle\Route\QueryStringBuilder;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandler;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\NoopSecurityHandler;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Translator\BCLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension as TwigSonataAdminExtension;
use Sonata\AdminBundle\Util\AdminAclManipulator;
use Sonata\AdminBundle\Util\AdminAclManipulatorInterface;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Sonata\AdminBundle\Util\FormViewIterator;
use Sonata\AdminBundle\Util\ObjectAclManipulator;
use Sonata\AdminBundle\Util\ObjectAclManipulatorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
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

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('twig.xml');
        $loader->load('core.xml');
        $loader->load('form_types.xml');
        $loader->load('validator.xml');
        $loader->load('route.xml');
        $loader->load('block.xml');
        $loader->load('menu.xml');
        $loader->load('commands.xml');
        $loader->load('actions.xml');

        if (isset($bundles['MakerBundle'])) {
            $loader->load('makers.xml');
        }

        if (isset($bundles['SonataExporterBundle'])) {
            $loader->load('exporter.xml');
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $config['options']['javascripts'] = $this->buildJavascripts($config);
        $config['options']['stylesheets'] = $this->buildStylesheets($config);
        $config['options']['role_admin'] = $config['security']['role_admin'];
        $config['options']['role_super_admin'] = $config['security']['role_super_admin'];
        $config['options']['search'] = $config['search'];

        $pool = $container->getDefinition('sonata.admin.pool');
        $pool->replaceArgument(1, $config['title']);
        $pool->replaceArgument(2, $config['title_logo']);
        $pool->replaceArgument(3, $config['options']);

        if (false === $config['options']['lock_protection']) {
            $container->removeDefinition('sonata.admin.lock.extension');
        }

        $container->setParameter('sonata.admin.configuration.global_search.empty_boxes', $config['global_search']['empty_boxes']);
        $container->setParameter('sonata.admin.configuration.templates', $config['templates'] + [
            'user_block' => '@SonataAdmin/Core/user_block.html.twig',
            'add_block' => '@SonataAdmin/Core/add_block.html.twig',
            'layout' => '@SonataAdmin/standard_layout.html.twig',
            'ajax' => '@SonataAdmin/ajax_layout.html.twig',
            'dashboard' => '@SonataAdmin/Core/dashboard.html.twig',
            'list' => '@SonataAdmin/CRUD/list.html.twig',
            'filter' => '@SonataAdmin/Form/filter_admin_fields.html.twig',
            'show' => '@SonataAdmin/CRUD/show.html.twig',
            'show_compare' => '@SonataAdmin/CRUD/show_compare.html.twig',
            'edit' => '@SonataAdmin/CRUD/edit.html.twig',
            'history' => '@SonataAdmin/CRUD/history.html.twig',
            'history_revision_timestamp' => '@SonataAdmin/CRUD/history_revision_timestamp.html.twig',
            'acl' => '@SonataAdmin/CRUD/acl.html.twig',
            'action' => '@SonataAdmin/CRUD/action.html.twig',
            'short_object_description' => '@SonataAdmin/Helper/short-object-description.html.twig',
            'preview' => '@SonataAdmin/CRUD/preview.html.twig',
            'list_block' => '@SonataAdmin/Block/block_admin_list.html.twig',
            'delete' => '@SonataAdmin/CRUD/delete.html.twig',
            'batch' => '@SonataAdmin/CRUD/list__batch.html.twig',
            'select' => '@SonataAdmin/CRUD/list__select.html.twig',
            'batch_confirmation' => '@SonataAdmin/CRUD/batch_confirmation.html.twig',
            'inner_list_row' => '@SonataAdmin/CRUD/list_inner_row.html.twig',
            'base_list_field' => '@SonataAdmin/CRUD/base_list_field.html.twig',
            'pager_links' => '@SonataAdmin/Pager/links.html.twig',
            'pager_results' => '@SonataAdmin/Pager/results.html.twig',
            'tab_menu_template' => '@SonataAdmin/Core/tab_menu_template.html.twig',
            'knp_menu_template' => '@SonataAdmin/Menu/sonata_menu.html.twig',
            'outer_list_rows_mosaic' => '@SonataAdmin/CRUD/list_outer_rows_mosaic.html.twig',
            'outer_list_rows_list' => '@SonataAdmin/CRUD/list_outer_rows_list.html.twig',
            'outer_list_rows_tree' => '@SonataAdmin/CRUD/list_outer_rows_tree.html.twig',
        ]);
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

        $loader->load('security.xml');

        $container->setParameter('sonata.admin.extension.map', $config['extensions']);

        /*
         * This is a work in progress, so for now it is hardcoded
         */
        $classes = [
            'email' => '',
            'textarea' => '',
            'text' => '',
            'choice' => '',
            'integer' => '',
            'datetime' => 'sonata-medium-date',
            'date' => 'sonata-medium-date',

            // SF3+
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
        $container->setParameter('sonata.admin.configuration.filters.persister', $config['filter_persister']);

        $container->setParameter('sonata.admin.configuration.show.mosaic.button', $config['show_mosaic_button']);

        $container->setParameter('sonata.admin.configuration.translate_group_label', $config['translate_group_label']);

        if (\PHP_VERSION_ID < 70000) {
            $this->configureClassesToCompile();
        }

        $this->replacePropertyAccessor($container);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * NEXT_MAJOR: remove all code that deals with JMSDiExtraBundle
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
                if (false !== array_search($sonataAdminPattern, $diExtraConfig['annotation_patterns'])) {
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
            $annotationPatterns = [$sonataAdminPattern];
        } else {
            // get annotation_patterns default from DiExtraBundle configuration
            $diExtraConfigDefinition = new JMSDiExtraBundleDependencyInjectionConfiguration();
            // FIXME: this will break if DiExtraBundle adds any mandatory configuration
            $diExtraConfig = $this->processConfiguration($diExtraConfigDefinition, []);

            $annotationPatterns = $diExtraConfig['annotation_patterns'];
            $annotationPatterns[] = $sonataAdminPattern;
        }

        $container->prependExtensionConfig(
            'jms_di_extra',
            [
                'annotation_patterns' => $annotationPatterns,
            ]
        );
    }

    public function configureClassesToCompile()
    {
        $this->addClassesToCompile([
            AbstractAdmin::class,
            AbstractAdminExtension::class,
            AdminExtensionInterface::class,
            AdminHelper::class,
            AdminInterface::class,
            BaseFieldDescription::class,
            FieldDescriptionCollection::class,
            FieldDescriptionInterface::class,
            Pool::class,
            AdminListBlockService::class,
            DatagridBuilderInterface::class,
            FormContractorInterface::class,
            ListBuilderInterface::class,
            RouteBuilderInterface::class,
            ShowBuilderInterface::class,
            Datagrid::class,
            DatagridInterface::class,
            DatagridMapper::class,
            ListMapper::class,
            Pager::class,
            PagerInterface::class,
            ProxyQueryInterface::class,
            ModelManagerException::class,
            NoValueException::class,
            Filter::class,
            FilterFactory::class,
            FilterFactoryInterface::class,
            FilterInterface::class,
            ArrayToModelTransformer::class,
            ModelsToArrayTransformer::class,
            ModelToIdTransformer::class,
            MergeCollectionListener::class,
            FormTypeFieldExtension::class,
            FormMapper::class,
            AdminType::class,
            ChoiceType::class,
            DateRangeType::class,
            DateTimeRangeType::class,
            DateTimeType::class,
            DateType::class,
            DefaultType::class,
            NumberType::class,
            ModelReferenceType::class,
            ModelType::class,
            ModelListType::class,
            TypeGuesserChain::class,
            TypeGuesserInterface::class,
            AuditManager::class,
            AuditManagerInterface::class,
            AuditReaderInterface::class,
            ModelManagerInterface::class,
            AdminPoolLoader::class,
            DefaultRouteGenerator::class,
            PathInfoBuilder::class,
            QueryStringBuilder::class,
            RouteCollection::class,
            RouteGeneratorInterface::class,
            AdminPermissionMap::class,
            MaskBuilder::class,
            AclSecurityHandler::class,
            AclSecurityHandlerInterface::class,
            NoopSecurityHandler::class,
            RoleSecurityHandler::class,
            SecurityHandlerInterface::class,
            ShowMapper::class,
            BCLabelTranslatorStrategy::class,
            FormLabelTranslatorStrategy::class,
            LabelTranslatorStrategyInterface::class,
            NativeLabelTranslatorStrategy::class,
            NoopLabelTranslatorStrategy::class,
            UnderscoreLabelTranslatorStrategy::class,
            TwigSonataAdminExtension::class,
            AdminAclManipulator::class,
            AdminAclManipulatorInterface::class,
            FormBuilderIterator::class,
            FormViewIterator::class,
            ObjectAclManipulator::class,
            ObjectAclManipulatorInterface::class,
        ]);
    }

    public function getNamespace()
    {
        return 'https://sonata-project.org/schema/dic/admin';
    }

    private function buildStylesheets($config)
    {
        return $this->mergeArray(
            $config['assets']['stylesheets'],
            $config['assets']['extra_stylesheets'],
            $config['assets']['remove_stylesheets']
        );
    }

    private function buildJavascripts($config)
    {
        return $this->mergeArray(
            $config['assets']['javascripts'],
            $config['assets']['extra_javascripts'],
            $config['assets']['remove_javascripts']
        );
    }

    private function mergeArray($array, $addArray, $removeArray = [])
    {
        foreach ($addArray as $toAdd) {
            array_push($array, $toAdd);
        }
        foreach ($removeArray as $toRemove) {
            if (\in_array($toRemove, $array)) {
                array_splice($array, array_search($toRemove, $array), 1);
            }
        }

        return $array;
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
