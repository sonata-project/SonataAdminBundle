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

namespace Sonata\AdminBundle\Admin;

use Doctrine\Common\Util\ClassUtils;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Manipulator\ObjectManipulator;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\Form\Validator\Constraints\InlineConstraint;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as RoutingUrlGeneratorInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface as DeprecatedTranslatorInterface;
use Symfony\Component\Validator\Mapping\GenericMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class AbstractAdmin implements AdminInterface, DomainObjectInterface, AdminTreeInterface
{
    public const CONTEXT_MENU = 'menu';
    public const CONTEXT_DASHBOARD = 'dashboard';

    public const CLASS_REGEX =
        '@
        (?:([A-Za-z0-9]*)\\\)?        # vendor name / app name
        (Bundle\\\)?                  # optional bundle directory
        ([A-Za-z0-9]+?)(?:Bundle)?\\\ # bundle name, with optional suffix
        (
            Entity|Document|Model|PHPCR|CouchDocument|Phpcr|
            Doctrine\\\Orm|Doctrine\\\Phpcr|Doctrine\\\MongoDB|Doctrine\\\CouchDB
        )\\\(.*)@x';

    public const MOSAIC_ICON_CLASS = 'fa fa-th-large fa-fw';

    /**
     * The list FieldDescription constructed from the configureListField method.
     *
     * @var FieldDescriptionInterface[]
     */
    protected $listFieldDescriptions = [];

    /**
     * The show FieldDescription constructed from the configureShowFields method.
     *
     * @var FieldDescriptionInterface[]
     */
    protected $showFieldDescriptions = [];

    /**
     * The list FieldDescription constructed from the configureFormField method.
     *
     * @var FieldDescriptionInterface[]
     */
    protected $formFieldDescriptions = [];

    /**
     * The filter FieldDescription constructed from the configureFilterField method.
     *
     * @var FieldDescriptionInterface[]
     */
    protected $filterFieldDescriptions = [];

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * The number of result to display in the list.
     *
     * @deprecated since sonata-project/admin-bundle 3.67.
     *
     * @var int
     */
    protected $maxPerPage = 32;

    /**
     * The maximum number of page numbers to display in the list.
     *
     * @var int
     */
    protected $maxPageLinks = 25;

    /**
     * The base route name used to generate the routing information.
     *
     * @var string
     */
    protected $baseRouteName;

    /**
     * The base route pattern used to generate the routing information.
     *
     * @var string
     */
    protected $baseRoutePattern;

    /**
     * The base name controller used to generate the routing information.
     *
     * @var string
     */
    protected $baseControllerName;

    /**
     * The label class name  (used in the title/breadcrumb ...).
     *
     * @var string
     */
    protected $classnameLabel;

    /**
     * The translation domain to be used to translate messages.
     *
     * @var string
     */
    protected $translationDomain = 'messages';

    /**
     * Options to set to the form (ie, validation_groups).
     *
     * @var array
     */
    protected $formOptions = [];

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * Default values to the datagrid.
     *
     * @deprecated since sonata-project/admin-bundle 3.67, use configureDefaultSortValues() instead.
     *
     * @var array
     */
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
    ];

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * Predefined per page options.
     *
     * @deprecated since sonata-project/admin-bundle 3.67.
     *
     * @var array
     */
    protected $perPageOptions = [16, 32, 64, 128, 256];

    /**
     * Pager type.
     *
     * @var string
     */
    protected $pagerType = Pager::TYPE_DEFAULT;

    /**
     * The code related to the admin.
     *
     * @var string
     */
    protected $code;

    /**
     * The label.
     *
     * @var string
     */
    protected $label;

    /**
     * Whether or not to persist the filters in the session.
     *
     * NEXT_MAJOR: remove this property
     *
     * @var bool
     *
     * @deprecated since sonata-project/admin-bundle 3.34, to be removed in 4.0.
     */
    protected $persistFilters = false;

    /**
     * Array of routes related to this admin.
     *
     * @var RouteCollection
     */
    protected $routes;

    /**
     * The subject only set in edit/update/create mode.
     *
     * @var object|null
     */
    protected $subject;

    /**
     * Define a Collection of child admin, ie /admin/order/{id}/order-element/{childId}.
     *
     * @var array
     */
    protected $children = [];

    /**
     * Reference the parent admin.
     *
     * @var AdminInterface|null
     */
    protected $parent;

    /**
     * The base code route refer to the prefix used to generate the route name.
     *
     * NEXT_MAJOR: remove this attribute.
     *
     * @deprecated This attribute is deprecated since sonata-project/admin-bundle 3.24 and will be removed in 4.0
     *
     * @var string
     */
    protected $baseCodeRoute = '';

    /**
     * NEXT_MAJOR: should be default array and private.
     *
     * @var array<string, mixed>|string|null
     */
    protected $parentAssociationMapping;

    /**
     * Reference the parent FieldDescription related to this admin
     * only set for FieldDescription which is associated to an Sub Admin instance.
     *
     * @var FieldDescriptionInterface
     */
    protected $parentFieldDescription;

    /**
     * If true then the current admin is part of the nested admin set (from the url).
     *
     * @var bool
     */
    protected $currentChild = false;

    /**
     * The uniqid is used to avoid clashing with 2 admin related to the code
     * ie: a Block linked to a Block.
     *
     * @var string
     */
    protected $uniqid;

    /**
     * The Entity or Document manager.
     *
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * The current request object.
     *
     * @var Request|null
     */
    protected $request;

    /**
     * The translator component.
     *
     * NEXT_MAJOR: remove this property
     *
     * @var DeprecatedTranslatorInterface|TranslatorInterface
     *
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed with 4.0
     */
    protected $translator;

    /**
     * The related form contractor.
     *
     * @var FormContractorInterface
     */
    protected $formContractor;

    /**
     * The related list builder.
     *
     * @var ListBuilderInterface
     */
    protected $listBuilder;

    /**
     * The related view builder.
     *
     * @var ShowBuilderInterface
     */
    protected $showBuilder;

    /**
     * The related datagrid builder.
     *
     * @var DatagridBuilderInterface
     */
    protected $datagridBuilder;

    /**
     * @var RouteBuilderInterface
     */
    protected $routeBuilder;

    /**
     * The datagrid instance.
     *
     * @var DatagridInterface|null
     */
    protected $datagrid;

    /**
     * The router instance.
     *
     * @var RouteGeneratorInterface|null
     */
    protected $routeGenerator;

    /**
     * The generated breadcrumbs.
     *
     * NEXT_MAJOR : remove this property
     *
     * @var array
     */
    protected $breadcrumbs = [];

    /**
     * @var SecurityHandlerInterface
     */
    protected $securityHandler;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * The configuration pool.
     *
     * @var Pool
     */
    protected $configurationPool;

    /**
     * @var ItemInterface
     */
    protected $menu;

    /**
     * @var FactoryInterface
     */
    protected $menuFactory;

    /**
     * @var array<string, bool>
     */
    protected $loaded = [
        'view_fields' => false, // NEXT_MAJOR: Remove this unused value.
        'view_groups' => false, // NEXT_MAJOR: Remove this unused value.
        'routes' => false,
        'tab_menu' => false,
        'show' => false,
        'list' => false,
        'form' => false,
        'datagrid' => false,
    ];

    /**
     * @var string[]
     */
    protected $formTheme = [];

    /**
     * @var string[]
     */
    protected $filterTheme = [];

    /**
     * @var array<string, string>
     *
     * @deprecated since sonata-project/admin-bundle 3.34, will be dropped in 4.0. Use TemplateRegistry services instead
     */
    protected $templates = [];

    /**
     * @var AdminExtensionInterface[]
     */
    protected $extensions = [];

    /**
     * @var LabelTranslatorStrategyInterface
     */
    protected $labelTranslatorStrategy;

    /**
     * Setting to true will enable preview mode for
     * the entity and show a preview button in the
     * edit/create forms.
     *
     * @var bool
     */
    protected $supportsPreviewMode = false;

    /**
     * Roles and permissions per role.
     *
     * @var array 'role' => ['permission', 'permission']
     */
    protected $securityInformation = [];

    protected $cacheIsGranted = [];

    /**
     * Action list for the search result.
     *
     * @var string[]
     */
    protected $searchResultActions = ['edit', 'show'];

    protected $listModes = [
        'list' => [
            'class' => 'fa fa-list fa-fw',
        ],
        'mosaic' => [
            'class' => self::MOSAIC_ICON_CLASS,
        ],
    ];

    /**
     * The Access mapping.
     *
     * @var array [action1 => requiredRole1, action2 => [requiredRole2, requiredRole3]]
     */
    protected $accessMapping = [];

    /**
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * The class name managed by the admin class.
     *
     * @var string
     */
    private $class;

    /**
     * The subclasses supported by the admin class.
     *
     * @var array<string, string>
     */
    private $subClasses = [];

    /**
     * The list collection.
     *
     * @var FieldDescriptionCollection|null
     */
    private $list;

    /**
     * @var FieldDescriptionCollection|null
     */
    private $show;

    /**
     * @var FormInterface|null
     */
    private $form;

    /**
     * The cached base route name.
     *
     * @var string
     */
    private $cachedBaseRouteName;

    /**
     * The cached base route pattern.
     *
     * @var string
     */
    private $cachedBaseRoutePattern;

    /**
     * The form group disposition.
     *
     * NEXT_MAJOR: must have `[]` as default value and remove the possibility to
     * hold boolean values.
     *
     * @var array|bool
     */
    private $formGroups = false;

    /**
     * The form tabs disposition.
     *
     * NEXT_MAJOR: must have `[]` as default value and remove the possibility to
     * hold boolean values.
     *
     * @var array|bool
     */
    private $formTabs = false;

    /**
     * The view group disposition.
     *
     * NEXT_MAJOR: must have `[]` as default value and remove the possibility to
     * hold boolean values.
     *
     * @var array|bool
     */
    private $showGroups = false;

    /**
     * The view tab disposition.
     *
     * NEXT_MAJOR: must have `[]` as default value and remove the possibility to
     * hold boolean values.
     *
     * @var array|bool
     */
    private $showTabs = false;

    /**
     * The manager type to use for the admin.
     *
     * @var string
     */
    private $managerType;

    /**
     * The breadcrumbsBuilder component.
     *
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    /**
     * Component responsible for persisting filters.
     *
     * @var FilterPersisterInterface|null
     */
    private $filterPersister;

    /**
     * @param string      $code
     * @param string      $class
     * @param string|null $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName = null)
    {
        if (!\is_string($code)) {
            @trigger_error(sprintf(
                'Passing other type than string as argument 1 for method %s() is deprecated since'
                .' sonata-project/admin-bundle 3.65. It will accept only string in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }
        $this->code = $code;
        if (!\is_string($class)) {
            @trigger_error(sprintf(
                'Passing other type than string as argument 2 for method %s() is deprecated since'
                .' sonata-project/admin-bundle 3.65. It will accept only string in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }
        $this->class = $class;
        if (null !== $baseControllerName && !\is_string($baseControllerName)) {
            @trigger_error(sprintf(
                'Passing other type than string or null as argument 3 for method %s() is deprecated since'
                .' sonata-project/admin-bundle 3.65. It will accept only string and null in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }
        $this->baseControllerName = $baseControllerName;

        // NEXT_MAJOR: Remove this line.
        $this->predefinePerPageOptions();

        // NEXT_MAJOR: Remove this line.
        $this->datagridValues['_per_page'] = $this->maxPerPage;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFormats()
    {
        return [
            'json', 'xml', 'csv', 'xls',
        ];
    }

    /**
     * @return array
     */
    public function getExportFields()
    {
        $fields = $this->getModelManager()->getExportFields($this->getClass());

        foreach ($this->getExtensions() as $extension) {
            if (method_exists($extension, 'configureExportFields')) {
                $fields = $extension->configureExportFields($this, $fields);
            }
        }

        return $fields;
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $fields = [];

        foreach ($this->getExportFields() as $key => $field) {
            $label = $this->getTranslationLabel($field, 'export', 'label');

            // NEXT_MAJOR: We have to find another way to have a translated label or stop deprecating the translator.
            $transLabel = $this->trans($label);

            // NEXT_MAJOR: Remove the following code in favor of the commented one.
            // If a key is provided we use it otherwise we use the generated label.
            // $fieldKey = \is_string($key) ? $key : $transLabel;
            // $fields[$fieldKey] = $field;
            if ($transLabel === $label) {
                $fields[$key] = $field;
            } else {
                $fields[$transLabel] = $field;
            }
        }

        return $this->getModelManager()->getDataSourceIterator($datagrid, $fields);
    }

    public function validate(ErrorElement $errorElement, $object)
    {
    }

    /**
     * define custom variable.
     */
    public function initialize()
    {
        if (!$this->classnameLabel) {
            /* NEXT_MAJOR: remove cast to string, null is not supposed to be
            supported but was documented as such */
            $this->classnameLabel = substr(
                (string) $this->getClass(),
                strrpos((string) $this->getClass(), '\\') + 1
            );
        }

        // NEXT_MAJOR: Remove this line.
        $this->baseCodeRoute = $this->getCode();

        $this->configure();
    }

    public function configure()
    {
    }

    public function update($object)
    {
        $this->preUpdate($object);
        foreach ($this->extensions as $extension) {
            $extension->preUpdate($this, $object);
        }

        $result = $this->getModelManager()->update($object);
        // BC compatibility
        if (null !== $result) {
            $object = $result;
        }

        $this->postUpdate($object);
        foreach ($this->extensions as $extension) {
            $extension->postUpdate($this, $object);
        }

        return $object;
    }

    public function create($object)
    {
        $this->prePersist($object);
        foreach ($this->extensions as $extension) {
            $extension->prePersist($this, $object);
        }

        $result = $this->getModelManager()->create($object);
        // BC compatibility
        if (null !== $result) {
            $object = $result;
        }

        $this->postPersist($object);
        foreach ($this->extensions as $extension) {
            $extension->postPersist($this, $object);
        }

        $this->createObjectSecurity($object);

        return $object;
    }

    public function delete($object)
    {
        $this->preRemove($object);
        foreach ($this->extensions as $extension) {
            $extension->preRemove($this, $object);
        }

        $this->getSecurityHandler()->deleteObjectSecurity($this, $object);
        $this->getModelManager()->delete($object);

        $this->postRemove($object);
        foreach ($this->extensions as $extension) {
            $extension->postRemove($this, $object);
        }
    }

    /**
     * @param object $object
     */
    public function preValidate($object)
    {
    }

    public function preUpdate($object)
    {
    }

    public function postUpdate($object)
    {
    }

    public function prePersist($object)
    {
    }

    public function postPersist($object)
    {
    }

    public function preRemove($object)
    {
    }

    public function postRemove($object)
    {
    }

    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements)
    {
    }

    public function getFilterParameters()
    {
        $parameters = [];

        // build the values array
        if ($this->hasRequest()) {
            /** @var InputBag|ParameterBag $bag */
            $bag = $this->request->query;
            if ($bag instanceof InputBag) {
                // symfony 5.1+
                $filters = $bag->all('filter');
            } else {
                $filters = $bag->get('filter', []);
            }
            if (isset($filters['_page'])) {
                $filters['_page'] = (int) $filters['_page'];
            }
            if (isset($filters['_per_page'])) {
                $filters['_per_page'] = (int) $filters['_per_page'];
            }

            // if filter persistence is configured
            // NEXT_MAJOR: remove `$this->persistFilters !== false` from the condition
            if (false !== $this->persistFilters && null !== $this->filterPersister) {
                // if reset filters is asked, remove from storage
                if ('reset' === $this->request->query->get('filters')) {
                    $this->filterPersister->reset($this->getCode());
                }

                // if no filters, fetch from storage
                // otherwise save to storage
                if (empty($filters)) {
                    $filters = $this->filterPersister->get($this->getCode());
                } else {
                    $this->filterPersister->set($this->getCode(), $filters);
                }
            }

            $parameters = array_merge(
                $this->getModelManager()->getDefaultSortValues($this->getClass()),
                $this->datagridValues, // NEXT_MAJOR: Remove this line.
                $this->getDefaultSortValues(),
                $this->getDefaultFilterValues(),
                $filters
            );

            if (!$this->determinedPerPageValue($parameters['_per_page'])) {
                $parameters['_per_page'] = $this->getMaxPerPage();
            }

            // always force the parent value
            if ($this->isChild() && $this->getParentAssociationMapping()) {
                $name = str_replace('.', '__', $this->getParentAssociationMapping());
                $parameters[$name] = ['value' => $this->request->get($this->getParent()->getIdParameter())];
            }
        }

        return $parameters;
    }

    /**
     * NEXT_MAJOR: Change the visibility to protected (similar to buildShow, buildForm, ...).
     */
    public function buildDatagrid()
    {
        if ($this->loaded['datagrid']) {
            return;
        }

        $this->loaded['datagrid'] = true;

        $filterParameters = $this->getFilterParameters();

        // transform _sort_by from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($filterParameters['_sort_by']) && \is_string($filterParameters['_sort_by'])) {
            if ($this->hasListFieldDescription($filterParameters['_sort_by'])) {
                $filterParameters['_sort_by'] = $this->getListFieldDescription($filterParameters['_sort_by']);
            } else {
                $filterParameters['_sort_by'] = $this->getModelManager()->getNewFieldDescriptionInstance(
                    $this->getClass(),
                    $filterParameters['_sort_by'],
                    []
                );

                $this->getListBuilder()->buildField(null, $filterParameters['_sort_by'], $this);
            }
        }

        // initialize the datagrid
        $this->datagrid = $this->getDatagridBuilder()->getBaseDatagrid($this, $filterParameters);

        $this->datagrid->getPager()->setMaxPageLinks($this->maxPageLinks);

        $mapper = new DatagridMapper($this->getDatagridBuilder(), $this->datagrid, $this);

        // build the datagrid filter
        $this->configureDatagridFilters($mapper);

        // ok, try to limit to add parent filter
        if ($this->isChild() && $this->getParentAssociationMapping() && !$mapper->has($this->getParentAssociationMapping())) {
            $mapper->add($this->getParentAssociationMapping(), null, [
                'show_filter' => false,
                'label' => false,
                'field_type' => ModelHiddenType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                ],
                'operator_type' => HiddenType::class,
            ], null, null, [
                'admin_code' => $this->getParent()->getCode(),
            ]);
        }

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDatagridFilters($mapper);
        }
    }

    /**
     * Returns the name of the parent related field, so the field can be use to set the default
     * value (ie the parent object) or to filter the object.
     *
     * @throws \InvalidArgumentException
     *
     * @return string|null
     */
    public function getParentAssociationMapping()
    {
        // NEXT_MAJOR: remove array check
        if (\is_array($this->parentAssociationMapping) && $this->isChild()) {
            $parent = $this->getParent()->getCode();

            if (\array_key_exists($parent, $this->parentAssociationMapping)) {
                return $this->parentAssociationMapping[$parent];
            }

            throw new \InvalidArgumentException(sprintf(
                'There\'s no association between %s and %s.',
                $this->getCode(),
                $this->getParent()->getCode()
            ));
        }

        // NEXT_MAJOR: remove this line
        return $this->parentAssociationMapping;
    }

    /**
     * @param string $code
     * @param string $value
     */
    final public function addParentAssociationMapping($code, $value)
    {
        if (\is_string($this->parentAssociationMapping)) {
            @trigger_error(sprintf(
                'Calling "%s" when $this->parentAssociationMapping is string is deprecated since sonata-admin/admin-bundle 3.75 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $this->parentAssociationMapping[$code] = $value;
    }

    /**
     * Returns the baseRoutePattern used to generate the routing information.
     *
     * @throws \RuntimeException
     *
     * @return string the baseRoutePattern used to generate the routing information
     */
    public function getBaseRoutePattern()
    {
        if (null !== $this->cachedBaseRoutePattern) {
            return $this->cachedBaseRoutePattern;
        }

        if ($this->isChild()) { // the admin class is a child, prefix it with the parent route pattern
            $baseRoutePattern = $this->baseRoutePattern;
            if (!$this->baseRoutePattern) {
                preg_match(self::CLASS_REGEX, $this->class, $matches);

                if (!$matches) {
                    throw new \RuntimeException(sprintf(
                        'Please define a default `baseRoutePattern` value for the admin class `%s`',
                        static::class
                    ));
                }
                $baseRoutePattern = $this->urlize($matches[5], '-');
            }

            $this->cachedBaseRoutePattern = sprintf(
                '%s/%s/%s',
                $this->getParent()->getBaseRoutePattern(),
                $this->getParent()->getRouterIdParameter(),
                $baseRoutePattern
            );
        } elseif ($this->baseRoutePattern) {
            $this->cachedBaseRoutePattern = $this->baseRoutePattern;
        } else {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf(
                    'Please define a default `baseRoutePattern` value for the admin class `%s`',
                    static::class
                ));
            }

            $this->cachedBaseRoutePattern = sprintf(
                '/%s%s/%s',
                empty($matches[1]) ? '' : $this->urlize($matches[1], '-').'/',
                $this->urlize($matches[3], '-'),
                $this->urlize($matches[5], '-')
            );
        }

        return $this->cachedBaseRoutePattern;
    }

    /**
     * Returns the baseRouteName used to generate the routing information.
     *
     * @throws \RuntimeException
     *
     * @return string the baseRouteName used to generate the routing information
     */
    public function getBaseRouteName()
    {
        if (null !== $this->cachedBaseRouteName) {
            return $this->cachedBaseRouteName;
        }

        if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
            $baseRouteName = $this->baseRouteName;
            if (!$this->baseRouteName) {
                preg_match(self::CLASS_REGEX, $this->class, $matches);

                if (!$matches) {
                    throw new \RuntimeException(sprintf(
                        'Cannot automatically determine base route name,'
                        .' please define a default `baseRouteName` value for the admin class `%s`',
                        static::class
                    ));
                }
                $baseRouteName = $this->urlize($matches[5]);
            }

            $this->cachedBaseRouteName = sprintf(
                '%s_%s',
                $this->getParent()->getBaseRouteName(),
                $baseRouteName
            );
        } elseif ($this->baseRouteName) {
            $this->cachedBaseRouteName = $this->baseRouteName;
        } else {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf(
                    'Cannot automatically determine base route name,'
                    .' please define a default `baseRouteName` value for the admin class `%s`',
                    static::class
                ));
            }

            $this->cachedBaseRouteName = sprintf(
                'admin_%s%s_%s',
                empty($matches[1]) ? '' : $this->urlize($matches[1]).'_',
                $this->urlize($matches[3]),
                $this->urlize($matches[5])
            );
        }

        return $this->cachedBaseRouteName;
    }

    /**
     * urlize the given word.
     *
     * @param string $word
     * @param string $sep  the separator
     *
     * @return string
     */
    public function urlize($word, $sep = '_')
    {
        return strtolower(preg_replace('/[^a-z0-9_]/i', $sep.'$1', $word));
    }

    public function getClass()
    {
        if ($this->hasActiveSubClass()) {
            if ($this->hasParentFieldDescription()) {
                throw new \RuntimeException('Feature not implemented: an embedded admin cannot have subclass');
            }

            $subClass = $this->getRequest()->query->get('subclass');

            if (!$this->hasSubClass($subClass)) {
                throw new \RuntimeException(sprintf('Subclass "%s" is not defined.', $subClass));
            }

            return $this->getSubClass($subClass);
        }

        // see https://github.com/sonata-project/SonataCoreBundle/commit/247eeb0a7ca7211142e101754769d70bc402a5b4
        if ($this->subject && \is_object($this->subject)) {
            return ClassUtils::getClass($this->subject);
        }

        return $this->class;
    }

    public function getSubClasses()
    {
        return $this->subClasses;
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    public function addSubClass($subClass)
    {
        @trigger_error(sprintf(
            'Method "%s" is deprecated since sonata-project/admin-bundle 3.30 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!\in_array($subClass, $this->subClasses, true)) {
            $this->subClasses[] = $subClass;
        }
    }

    public function setSubClasses(array $subClasses)
    {
        $this->subClasses = $subClasses;
    }

    public function hasSubClass($name)
    {
        return isset($this->subClasses[$name]);
    }

    public function hasActiveSubClass()
    {
        if (\count($this->subClasses) > 0 && $this->request) {
            return null !== $this->getRequest()->query->get('subclass');
        }

        return false;
    }

    public function getActiveSubClass()
    {
        if (!$this->hasActiveSubClass()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no active subclass is deprecated since sonata-project/admin-bundle 3.52'
                .' and will throw an exception in 4.0.'
                .' Use %s::hasActiveSubClass() to know if there is an active subclass.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare string as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no active subclass.',
            //    static::class
            // ));

            return null;
        }

        return $this->getSubClass($this->getActiveSubclassCode());
    }

    public function getActiveSubclassCode()
    {
        if (!$this->hasActiveSubClass()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no active subclass is deprecated since sonata-project/admin-bundle 3.52'
                .' and will throw an exception in 4.0.'
                .' Use %s::hasActiveSubClass() to know if there is an active subclass.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare string as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no active subclass.',
            //    static::class
            // ));

            return null;
        }

        $subClass = $this->getRequest()->query->get('subclass');

        if (!$this->hasSubClass($subClass)) {
            @trigger_error(sprintf(
                'Calling %s() when there is no active subclass is deprecated since sonata-project/admin-bundle 3.52'
                .' and will throw an exception in 4.0.'
                .' Use %s::hasActiveSubClass() to know if there is an active subclass.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare string as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no active subclass.',
            //    static::class
            // ));

            return null;
        }

        return $subClass;
    }

    public function getBatchActions()
    {
        $actions = [];

        if ($this->hasRoute('delete') && $this->hasAccess('delete')) {
            $actions['delete'] = [
                'label' => 'action_delete',
                'translation_domain' => 'SonataAdminBundle',
                'ask_confirmation' => true, // by default always true
            ];
        }

        $actions = $this->configureBatchActions($actions);

        foreach ($this->getExtensions() as $extension) {
            // NEXT_MAJOR: remove method check
            if (method_exists($extension, 'configureBatchActions')) {
                $actions = $extension->configureBatchActions($this, $actions);
            }
        }

        foreach ($actions  as $name => &$action) {
            if (!\array_key_exists('label', $action)) {
                $action['label'] = $this->getTranslationLabel($name, 'batch', 'label');
            }

            if (!\array_key_exists('translation_domain', $action)) {
                $action['translation_domain'] = $this->getTranslationDomain();
            }
        }

        return $actions;
    }

    public function getRoutes()
    {
        $this->buildRoutes();

        return $this->routes;
    }

    public function getRouterIdParameter()
    {
        return sprintf('{%s}', $this->getIdParameter());
    }

    public function getIdParameter()
    {
        $parameter = 'id';

        for ($i = 0; $i < $this->getChildDepth(); ++$i) {
            $parameter = sprintf('child%s', ucfirst($parameter));
        }

        return $parameter;
    }

    public function hasRoute($name)
    {
        if (!$this->routeGenerator) {
            throw new \RuntimeException('RouteGenerator cannot be null');
        }

        return $this->routeGenerator->hasAdminRoute($this, $name);
    }

    /**
     * @param string      $name
     * @param string|null $adminCode
     *
     * @return bool
     */
    public function isCurrentRoute($name, $adminCode = null)
    {
        if (!$this->hasRequest()) {
            return false;
        }

        $request = $this->getRequest();
        $route = $request->get('_route');

        if ($adminCode) {
            $admin = $this->getConfigurationPool()->getAdminByAdminCode($adminCode);
        } else {
            $admin = $this;
        }

        if (!$admin) {
            return false;
        }

        return sprintf('%s_%s', $admin->getBaseRouteName(), $name) === $route;
    }

    public function generateObjectUrl($name, $object, array $parameters = [], $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $parameters['id'] = $this->getUrlSafeIdentifier($object);

        return $this->generateUrl($name, $parameters, $referenceType);
    }

    public function generateUrl($name, array $parameters = [], $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->routeGenerator->generateUrl($this, $name, $parameters, $referenceType);
    }

    public function generateMenuUrl($name, array $parameters = [], $referenceType = RoutingUrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->routeGenerator->generateMenuUrl($this, $name, $parameters, $referenceType);
    }

    final public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry)
    {
        $this->templateRegistry = $templateRegistry;
    }

    /**
     * @param array<string, string> $templates
     */
    public function setTemplates(array $templates)
    {
        // NEXT_MAJOR: Remove this line
        $this->templates = $templates;

        $this->getTemplateRegistry()->setTemplates($templates);
    }

    /**
     * @param string $name
     * @param string $template
     */
    public function setTemplate($name, $template)
    {
        // NEXT_MAJOR: Remove this line
        $this->templates[$name] = $template;

        $this->getTemplateRegistry()->setTemplate($name, $template);
    }

    /**
     * @deprecated since sonata-project/admin-bundle 3.34, will be dropped in 4.0. Use TemplateRegistry services instead
     *
     * @return array<string, string>
     */
    public function getTemplates()
    {
        return $this->getTemplateRegistry()->getTemplates();
    }

    /**
     * @deprecated since sonata-project/admin-bundle 3.34, will be dropped in 4.0. Use TemplateRegistry services instead
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getTemplate($name)
    {
        return $this->getTemplateRegistry()->getTemplate($name);
    }

    public function getNewInstance()
    {
        $object = $this->getModelManager()->getModelInstance($this->getClass());

        $this->appendParentObject($object);

        foreach ($this->getExtensions() as $extension) {
            $extension->alterNewInstance($this, $object);
        }

        return $object;
    }

    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $formBuilder = $this->getFormContractor()->getFormBuilder(
            $this->getUniqid(),
            $this->formOptions
        );

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * This method is being called by the main admin class and the child class,
     * the getFormBuilder is only call by the main admin class.
     */
    public function defineFormBuilder(FormBuilderInterface $formBuilder)
    {
        if (!$this->hasSubject()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no subject is deprecated since sonata-project/admin-bundle 3.65'
                .' and will throw an exception in 4.0. Use %s::setSubject() to set the subject.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call and uncomment the following exception
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no subject.',
            //    static::class
            // ));
        }

        $mapper = new FormMapper($this->getFormContractor(), $formBuilder, $this);

        $this->configureFormFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureFormFields($mapper);
        }

        $this->attachInlineValidator();
    }

    public function attachAdminClass(FieldDescriptionInterface $fieldDescription)
    {
        $pool = $this->getConfigurationPool();

        $adminCode = $fieldDescription->getOption('admin_code');

        if (null !== $adminCode) {
            if (!$pool->hasAdminByAdminCode($adminCode)) {
                return;
            }

            $admin = $pool->getAdminByAdminCode($adminCode);
        } else {
            // NEXT_MAJOR: Remove the check and use `getTargetModel`.
            if (method_exists($fieldDescription, 'getTargetModel')) {
                $targetModel = $fieldDescription->getTargetModel();
            } else {
                $targetModel = $fieldDescription->getTargetEntity();
            }

            if (!$pool->hasAdminByClass($targetModel)) {
                return;
            }

            $admin = $pool->getAdminByClass($targetModel);
        }

        if ($this->hasRequest()) {
            $admin->setRequest($this->getRequest());
        }

        $fieldDescription->setAssociationAdmin($admin);
    }

    public function getObject($id)
    {
        if (null === $id) {
            return null;
        }

        $object = $this->getModelManager()->find($this->getClass(), $id);
        foreach ($this->getExtensions() as $extension) {
            $extension->alterObject($this, $object);
        }

        return $object;
    }

    public function getForm()
    {
        $this->buildForm();

        return $this->form;
    }

    public function getList()
    {
        $this->buildList();

        return $this->list;
    }

    /**
     * @final since sonata-project/admin-bundle 3.63.0
     */
    public function createQuery($context = 'list')
    {
        if (\func_num_args() > 0) {
            @trigger_error(sprintf(
                'The $context argument of %s is deprecated since 3.3, to be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $query = $this->getModelManager()->createQuery($this->getClass());

        $query = $this->configureQuery($query);
        foreach ($this->extensions as $extension) {
            $extension->configureQuery($this, $query, $context);
        }

        return $query;
    }

    public function getDatagrid()
    {
        $this->buildDatagrid();

        return $this->datagrid;
    }

    public function buildTabMenu($action, ?AdminInterface $childAdmin = null)
    {
        if ($this->loaded['tab_menu']) {
            return $this->menu;
        }

        $this->loaded['tab_menu'] = true;

        $menu = $this->menuFactory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $menu->setExtra('translation_domain', $this->translationDomain);

        // Prevents BC break with KnpMenuBundle v1.x
        if (method_exists($menu, 'setCurrentUri')) {
            $menu->setCurrentUri($this->getRequest()->getBaseUrl().$this->getRequest()->getPathInfo());
        }

        $this->configureTabMenu($menu, $action, $childAdmin);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureTabMenu($this, $menu, $action, $childAdmin);
        }

        $this->menu = $menu;

        return $this->menu;
    }

    public function buildSideMenu($action, ?AdminInterface $childAdmin = null)
    {
        return $this->buildTabMenu($action, $childAdmin);
    }

    /**
     * @param string $action
     *
     * @return ItemInterface
     */
    public function getSideMenu($action, ?AdminInterface $childAdmin = null)
    {
        if ($this->isChild()) {
            return $this->getParent()->getSideMenu($action, $this);
        }

        $this->buildSideMenu($action, $childAdmin);

        return $this->menu;
    }

    /**
     * Returns the root code.
     *
     * @return string the root code
     */
    public function getRootCode()
    {
        return $this->getRoot()->getCode();
    }

    /**
     * Returns the master admin.
     *
     * @return AdminInterface the root admin class
     */
    public function getRoot()
    {
        if (!$this->hasParentFieldDescription()) {
            return $this;
        }

        return $this->getParentFieldDescription()->getAdmin()->getRoot();
    }

    public function setBaseControllerName($baseControllerName)
    {
        $this->baseControllerName = $baseControllerName;
    }

    public function getBaseControllerName()
    {
        return $this->baseControllerName;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param bool $persist
     *
     * NEXT_MAJOR: remove this method
     *
     * @deprecated since sonata-project/admin-bundle 3.34, to be removed in 4.0.
     */
    public function setPersistFilters($persist)
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.34 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $this->persistFilters = $persist;
    }

    public function setFilterPersister(?FilterPersisterInterface $filterPersister = null)
    {
        $this->filterPersister = $filterPersister;
        // NEXT_MAJOR remove the deprecated property will be removed. Needed for persisted filter condition.
        $this->persistFilters = true;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.67, to be removed in 4.0.
     *
     * @param int $maxPerPage
     */
    public function setMaxPerPage($maxPerPage)
    {
        @trigger_error(sprintf(
            'The method %s is deprecated since sonata-project/admin-bundle 3.67 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $this->maxPerPage = $maxPerPage;
    }

    /**
     * @return int
     */
    public function getMaxPerPage()
    {
        // NEXT_MAJOR: Remove this line and uncomment the following.
        return $this->maxPerPage;
        // $sortValues = $this->getModelManager()->getDefaultSortValues($this->class);

        // return $sortValues['_per_page'] ?? 25;
    }

    /**
     * @param int $maxPageLinks
     */
    public function setMaxPageLinks($maxPageLinks)
    {
        $this->maxPageLinks = $maxPageLinks;
    }

    /**
     * @return int
     */
    public function getMaxPageLinks()
    {
        return $this->maxPageLinks;
    }

    public function getFormGroups()
    {
        if (!\is_array($this->formGroups) && 'sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'Returning other type than array in method %s() is deprecated since sonata-project/admin-bundle 3.65.'
                .' It will return only array in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->formGroups;
    }

    public function setFormGroups(array $formGroups)
    {
        $this->formGroups = $formGroups;
    }

    public function removeFieldFromFormGroup($key)
    {
        foreach ($this->formGroups as $name => $formGroup) {
            unset($this->formGroups[$name]['fields'][$key]);

            if (empty($this->formGroups[$name]['fields'])) {
                unset($this->formGroups[$name]);
            }
        }
    }

    /**
     * @param string $group
     */
    public function reorderFormGroup($group, array $keys)
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        $formGroups = $this->getFormGroups('sonata_deprecation_mute');
        $formGroups[$group]['fields'] = array_merge(array_flip($keys), $formGroups[$group]['fields']);
        $this->setFormGroups($formGroups);
    }

    public function getFormTabs()
    {
        if (!\is_array($this->formTabs) && 'sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'Returning other type than array in method %s() is deprecated since sonata-project/admin-bundle 3.65.'
                .' It will return only array in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->formTabs;
    }

    public function setFormTabs(array $formTabs)
    {
        $this->formTabs = $formTabs;
    }

    public function getShowTabs()
    {
        if (!\is_array($this->showTabs) && 'sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'Returning other type than array in method %s() is deprecated since sonata-project/admin-bundle 3.65.'
                .' It will return only array in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->showTabs;
    }

    public function setShowTabs(array $showTabs)
    {
        $this->showTabs = $showTabs;
    }

    public function getShowGroups()
    {
        if (!\is_array($this->showGroups) && 'sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'Returning other type than array in method %s() is deprecated since sonata-project/admin-bundle 3.65.'
                .' It will return only array in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->showGroups;
    }

    public function setShowGroups(array $showGroups)
    {
        $this->showGroups = $showGroups;
    }

    public function reorderShowGroup($group, array $keys)
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        $showGroups = $this->getShowGroups('sonata_deprecation_mute');
        $showGroups[$group]['fields'] = array_merge(array_flip($keys), $showGroups[$group]['fields']);
        $this->setShowGroups($showGroups);
    }

    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription)
    {
        $this->parentFieldDescription = $parentFieldDescription;
    }

    public function getParentFieldDescription()
    {
        if (!$this->hasParentFieldDescription()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no parent field description is deprecated since'
                .' sonata-project/admin-bundle 3.66 and will throw an exception in 4.0.'
                .' Use %s::hasParentFieldDescription() to know if there is a parent field description.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare FieldDescriptionInterface as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no parent field description.',
            //    static::class
            // ));

            return null;
        }

        return $this->parentFieldDescription;
    }

    public function hasParentFieldDescription()
    {
        return $this->parentFieldDescription instanceof FieldDescriptionInterface;
    }

    public function setSubject($subject)
    {
        if (\is_object($subject) && !is_a($subject, $this->getClass(), true)) {
            $message = <<<'EOT'
You are trying to set entity an instance of "%s",
which is not the one registered with this admin class ("%s").
This is deprecated since 3.5 and will no longer be supported in 4.0.
EOT;

            // NEXT_MAJOR : throw an exception instead
            @trigger_error(sprintf($message, \get_class($subject), $this->getClass()), E_USER_DEPRECATED);
        }

        $this->subject = $subject;
    }

    public function getSubject()
    {
        if (!$this->hasSubject()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no subject is deprecated since sonata-project/admin-bundle 3.66'
                .' and will throw an exception in 4.0. Use %s::hasSubject() to know if there is a subject.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and update the return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no subject.',
            //    static::class
            // ));

            return null;
        }

        return $this->subject;
    }

    public function hasSubject()
    {
        if (null === $this->subject && $this->hasRequest() && !$this->hasParentFieldDescription()) {
            $id = $this->request->get($this->getIdParameter());

            if (null !== $id) {
                $this->subject = $this->getObject($id);
            }
        }

        return null !== $this->subject;
    }

    public function getFormFieldDescriptions()
    {
        $this->buildForm();

        return $this->formFieldDescriptions;
    }

    public function getFormFieldDescription($name)
    {
        $this->buildForm();

        if (!$this->hasFormFieldDescription($name)) {
            @trigger_error(sprintf(
                'Calling %s() when there is no form field description is deprecated since'
                .' sonata-project/admin-bundle 3.69 and will throw an exception in 4.0.'
                .' Use %s::hasFormFieldDescription() to know if there is a form field description.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare FieldDescriptionInterface as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no form field description for the field %s.',
            //    static::class,
            //    $name
            // ));

            return null;
        }

        return $this->formFieldDescriptions[$name];
    }

    /**
     * Returns true if the admin has a FieldDescription with the given $name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasFormFieldDescription($name)
    {
        $this->buildForm();

        return \array_key_exists($name, $this->formFieldDescriptions) ? true : false;
    }

    public function addFormFieldDescription($name, FieldDescriptionInterface $fieldDescription)
    {
        $this->formFieldDescriptions[$name] = $fieldDescription;
    }

    /**
     * remove a FieldDescription.
     *
     * @param string $name
     */
    public function removeFormFieldDescription($name)
    {
        unset($this->formFieldDescriptions[$name]);
    }

    /**
     * build and return the collection of form FieldDescription.
     *
     * @return FieldDescriptionInterface[] collection of form FieldDescription
     */
    public function getShowFieldDescriptions()
    {
        $this->buildShow();

        return $this->showFieldDescriptions;
    }

    /**
     * Returns the form FieldDescription with the given $name.
     *
     * @param string $name
     *
     * @return FieldDescriptionInterface
     */
    public function getShowFieldDescription($name)
    {
        $this->buildShow();

        if (!$this->hasShowFieldDescription($name)) {
            @trigger_error(sprintf(
                'Calling %s() when there is no show field description is deprecated since'
                .' sonata-project/admin-bundle 3.69 and will throw an exception in 4.0.'
                .' Use %s::hasFormFieldDescription() to know if there is a show field description.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare FieldDescriptionInterface as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no show field description for the field %s.',
            //    static::class,
            //    $name
            // ));

            return null;
        }

        return $this->showFieldDescriptions[$name];
    }

    public function hasShowFieldDescription($name)
    {
        $this->buildShow();

        return \array_key_exists($name, $this->showFieldDescriptions);
    }

    public function addShowFieldDescription($name, FieldDescriptionInterface $fieldDescription)
    {
        $this->showFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeShowFieldDescription($name)
    {
        unset($this->showFieldDescriptions[$name]);
    }

    public function getListFieldDescriptions()
    {
        $this->buildList();

        return $this->listFieldDescriptions;
    }

    public function getListFieldDescription($name)
    {
        $this->buildList();

        if (!$this->hasListFieldDescription($name)) {
            @trigger_error(sprintf(
                'Calling %s() when there is no list field description is deprecated since'
                .' sonata-project/admin-bundle 3.66 and will throw an exception in 4.0.'
                .' Use %s::hasListFieldDescription(\'%s\') to know if there is a list field description.',
                __METHOD__,
                __CLASS__,
                $name
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare FieldDescriptionInterface as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no list field description for %s.',
            //    static::class,
            //    $name
            // ));

            return null;
        }

        return $this->listFieldDescriptions[$name];
    }

    public function hasListFieldDescription($name)
    {
        $this->buildList();

        return \array_key_exists($name, $this->listFieldDescriptions) ? true : false;
    }

    public function addListFieldDescription($name, FieldDescriptionInterface $fieldDescription)
    {
        $this->listFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeListFieldDescription($name)
    {
        unset($this->listFieldDescriptions[$name]);
    }

    public function getFilterFieldDescription($name)
    {
        $this->buildDatagrid();

        if (!$this->hasFilterFieldDescription($name)) {
            @trigger_error(sprintf(
                'Calling %s() when there is no filter field description is deprecated since'
                .' sonata-project/admin-bundle 3.69 and will throw an exception in 4.0.'
                .' Use %s::hasFilterFieldDescription() to know if there is a filter field description.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare FieldDescriptionInterface as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no filter field description for the field %s.',
            //    static::class,
            //    $name
            // ));

            return null;
        }

        return $this->filterFieldDescriptions[$name];
    }

    public function hasFilterFieldDescription($name)
    {
        $this->buildDatagrid();

        return \array_key_exists($name, $this->filterFieldDescriptions) ? true : false;
    }

    public function addFilterFieldDescription($name, FieldDescriptionInterface $fieldDescription)
    {
        $this->filterFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeFilterFieldDescription($name)
    {
        unset($this->filterFieldDescriptions[$name]);
    }

    public function getFilterFieldDescriptions()
    {
        $this->buildDatagrid();

        return $this->filterFieldDescriptions;
    }

    public function addChild(AdminInterface $child)
    {
        $parentAdmin = $this;
        while ($parentAdmin->isChild() && $parentAdmin->getCode() !== $child->getCode()) {
            $parentAdmin = $parentAdmin->getParent();
        }

        if ($parentAdmin->getCode() === $child->getCode()) {
            throw new \RuntimeException(sprintf(
                'Circular reference detected! The child admin `%s` is already in the parent tree of the `%s` admin.',
                $child->getCode(),
                $this->getCode()
            ));
        }

        $this->children[$child->getCode()] = $child;

        $child->setParent($this);

        // NEXT_MAJOR: remove $args and add $field parameter to this function on next Major

        $args = \func_get_args();

        if (isset($args[1])) {
            $child->addParentAssociationMapping($this->getCode(), $args[1]);
        } else {
            @trigger_error(
                'Calling "addChild" without second argument is deprecated since sonata-project/admin-bundle 3.35 and will not be allowed in 4.0.',
                E_USER_DEPRECATED
            );
        }
    }

    public function hasChild($code)
    {
        return isset($this->children[$code]);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getChild($code)
    {
        if (!$this->hasChild($code)) {
            @trigger_error(sprintf(
                'Calling %s() when there is no child is deprecated since sonata-project/admin-bundle 3.69'
                .' and will throw an exception in 4.0. Use %s::hasChild() to know if the child exists.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare AdminInterface as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no child for the code %s.',
            //    static::class,
            //    $code
            // ));

            return null;
        }

        return $this->children[$code];
    }

    public function setParent(AdminInterface $parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        if (!$this->isChild()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no parent is deprecated since sonata-project/admin-bundle 3.66'
                .' and will throw an exception in 4.0. Use %s::isChild() to know if there is a parent.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement, uncomment the following exception and declare AdminInterface as return type
            // throw new \LogicException(sprintf(
            //    'Admin "%s" has no parent.',
            //    static::class
            // ));

            return null;
        }

        return $this->parent;
    }

    final public function getRootAncestor()
    {
        $parent = $this;

        while ($parent->isChild()) {
            $parent = $parent->getParent();
        }

        return $parent;
    }

    final public function getChildDepth()
    {
        $parent = $this;
        $depth = 0;

        while ($parent->isChild()) {
            $parent = $parent->getParent();
            ++$depth;
        }

        return $depth;
    }

    final public function getCurrentLeafChildAdmin()
    {
        $child = $this->getCurrentChildAdmin();

        if (null === $child) {
            return null;
        }

        for ($c = $child; null !== $c; $c = $child->getCurrentChildAdmin()) {
            $child = $c;
        }

        return $child;
    }

    public function isChild()
    {
        return $this->parent instanceof AdminInterface;
    }

    /**
     * Returns true if the admin has children, false otherwise.
     *
     * @return bool if the admin has children
     */
    public function hasChildren()
    {
        return \count($this->children) > 0;
    }

    public function setUniqid($uniqid)
    {
        $this->uniqid = $uniqid;
    }

    public function getUniqid()
    {
        if (!$this->uniqid) {
            $this->uniqid = sprintf('s%s', uniqid());
        }

        return $this->uniqid;
    }

    /**
     * Returns the classname label.
     *
     * @return string the classname label
     */
    public function getClassnameLabel()
    {
        return $this->classnameLabel;
    }

    public function getPersistentParameters()
    {
        $parameters = [];

        foreach ($this->getExtensions() as $extension) {
            $params = $extension->getPersistentParameters($this);

            if (!\is_array($params)) {
                throw new \RuntimeException(sprintf(
                    'The %s::getPersistentParameters must return an array',
                    \get_class($extension)
                ));
            }

            $parameters = array_merge($parameters, $params);
        }

        return $parameters;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getPersistentParameter($name)
    {
        $parameters = $this->getPersistentParameters();

        return $parameters[$name] ?? null;
    }

    public function getBreadcrumbs($action)
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.2 and will be removed in 4.0.'
            .' Use %s::getBreadcrumbs instead.',
            __METHOD__,
            BreadcrumbsBuilder::class
        ), E_USER_DEPRECATED);

        return $this->getBreadcrumbsBuilder()->getBreadcrumbs($this, $action);
    }

    /**
     * Generates the breadcrumbs array.
     *
     * Note: the method will be called by the top admin instance (parent => child)
     *
     * @param string $action
     *
     * @return array
     */
    public function buildBreadcrumbs($action, ?ItemInterface $menu = null)
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.2 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (isset($this->breadcrumbs[$action])) {
            return $this->breadcrumbs[$action];
        }

        return $this->breadcrumbs[$action] = $this->getBreadcrumbsBuilder()
            ->buildBreadcrumbs($this, $action, $menu);
    }

    /**
     * NEXT_MAJOR : remove this method.
     *
     * @return BreadcrumbsBuilderInterface
     */
    final public function getBreadcrumbsBuilder()
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.2 and will be removed in 4.0.'
            .' Use the sonata.admin.breadcrumbs_builder service instead.',
            __METHOD__
        ), E_USER_DEPRECATED);
        if (null === $this->breadcrumbsBuilder) {
            $this->breadcrumbsBuilder = new BreadcrumbsBuilder(
                $this->getConfigurationPool()->getContainer()->getParameter('sonata.admin.configuration.breadcrumbs')
            );
        }

        return $this->breadcrumbsBuilder;
    }

    /**
     * NEXT_MAJOR : remove this method.
     *
     * @return AbstractAdmin
     */
    final public function setBreadcrumbsBuilder(BreadcrumbsBuilderInterface $value)
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.2 and will be removed in 4.0.'
            .' Use the sonata.admin.breadcrumbs_builder service instead.',
            __METHOD__
        ), E_USER_DEPRECATED);
        $this->breadcrumbsBuilder = $value;

        return $this;
    }

    public function setCurrentChild($currentChild)
    {
        $this->currentChild = $currentChild;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.65, to be removed in 4.0
     */
    public function getCurrentChild()
    {
        @trigger_error(sprintf(
            'The %s() method is deprecated since version 3.65 and will be removed in 4.0.'
            .' Use %s::isCurrentChild() instead.',
            __METHOD__,
            __CLASS__
        ), E_USER_DEPRECATED);

        return $this->currentChild;
    }

    public function isCurrentChild(): bool
    {
        return $this->currentChild;
    }

    /**
     * Returns the current child admin instance.
     *
     * @return AdminInterface|null the current child admin instance
     */
    public function getCurrentChildAdmin()
    {
        foreach ($this->children as $children) {
            if ($children->isCurrentChild()) {
                return $children;
            }
        }

        return null;
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.9 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $domain = $domain ?: $this->getTranslationDomain();

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Translate a message id.
     *
     * NEXT_MAJOR: remove this method
     *
     * @param string      $id
     * @param int         $count
     * @param string|null $domain
     * @param string|null $locale
     *
     * @return string the translated string
     *
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed with 4.0
     */
    public function transChoice($id, $count, array $parameters = [], $domain = null, $locale = null)
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.9 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $domain = $domain ?: $this->getTranslationDomain();

        return $this->translator->transChoice($id, $count, $parameters, $domain, $locale);
    }

    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
    }

    public function getTranslationDomain()
    {
        return $this->translationDomain;
    }

    /**
     * {@inheritdoc}
     *
     * NEXT_MAJOR: remove this method
     *
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed with 4.0
     *
     * @param DeprecatedTranslatorInterface|TranslatorInterface $translator
     */
    public function setTranslator($translator)
    {
        $args = \func_get_args();
        if (isset($args[1]) && $args[1]) {
            @trigger_error(sprintf(
                'The %s method is deprecated since version 3.9 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        if (!$translator instanceof DeprecatedTranslatorInterface && !$translator instanceof TranslatorInterface) {
            throw new \TypeError(sprintf(
                'Argument 1 passed to "%s()" must be an instance of "%s" or "%s", %s given.',
                __METHOD__,
                DeprecatedTranslatorInterface::class,
                TranslatorInterface::class,
                \is_object($translator) ? 'instance of '.\get_class($translator) : \gettype($translator)
            ));
        }

        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * NEXT_MAJOR: remove this method
     *
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed with 4.0
     */
    public function getTranslator()
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since version 3.9 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return $this->translator;
    }

    public function getTranslationLabel($label, $context = '', $type = '')
    {
        return $this->getLabelTranslatorStrategy()->getLabel($label, $context, $type);
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        foreach ($this->getChildren() as $children) {
            $children->setRequest($request);
        }
    }

    public function getRequest()
    {
        if (!$this->request) {
            // NEXT_MAJOR: Throw \LogicException instead.
            throw new \RuntimeException('The Request object has not been set');
        }

        return $this->request;
    }

    public function hasRequest()
    {
        return null !== $this->request;
    }

    public function setFormContractor(FormContractorInterface $formBuilder)
    {
        $this->formContractor = $formBuilder;
    }

    /**
     * @return FormContractorInterface
     */
    public function getFormContractor()
    {
        return $this->formContractor;
    }

    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder)
    {
        $this->datagridBuilder = $datagridBuilder;
    }

    public function getDatagridBuilder()
    {
        return $this->datagridBuilder;
    }

    public function setListBuilder(ListBuilderInterface $listBuilder)
    {
        $this->listBuilder = $listBuilder;
    }

    public function getListBuilder()
    {
        return $this->listBuilder;
    }

    public function setShowBuilder(ShowBuilderInterface $showBuilder)
    {
        $this->showBuilder = $showBuilder;
    }

    /**
     * @return ShowBuilderInterface
     */
    public function getShowBuilder()
    {
        return $this->showBuilder;
    }

    public function setConfigurationPool(Pool $configurationPool)
    {
        $this->configurationPool = $configurationPool;
    }

    /**
     * @return Pool
     */
    public function getConfigurationPool()
    {
        return $this->configurationPool;
    }

    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * @return RouteGeneratorInterface
     */
    public function getRouteGenerator()
    {
        return $this->routeGenerator;
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * NEXT_MAJOR: Remove this function.
     *
     * @deprecated This method is deprecated since sonata-project/admin-bundle 3.24 and will be removed in 4.0
     *
     * @param string $baseCodeRoute
     */
    public function setBaseCodeRoute($baseCodeRoute)
    {
        @trigger_error(sprintf(
            'The %s is deprecated since 3.24 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $this->baseCodeRoute = $baseCodeRoute;
    }

    public function getBaseCodeRoute()
    {
        // NEXT_MAJOR: Uncomment the following lines.
        // if ($this->isChild()) {
        //     return sprintf('%s|%s', $this->getParent()->getBaseCodeRoute(), $this->getCode());
        // }
        //
        // return $this->getCode();

        // NEXT_MAJOR: Remove all the code below.
        if ($this->isChild()) {
            $parentCode = $this->getParent()->getCode();

            if ($this->getParent()->isChild()) {
                $parentCode = $this->getParent()->getBaseCodeRoute();
            }

            return sprintf('%s|%s', $parentCode, $this->getCode());
        }

        return $this->baseCodeRoute;
    }

    public function getModelManager()
    {
        return $this->modelManager;
    }

    public function setModelManager(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public function getManagerType()
    {
        return $this->managerType;
    }

    /**
     * @param string $type
     */
    public function setManagerType($type)
    {
        $this->managerType = $type;
    }

    public function getObjectIdentifier()
    {
        return $this->getCode();
    }

    /**
     * Set the roles and permissions per role.
     */
    public function setSecurityInformation(array $information)
    {
        $this->securityInformation = $information;
    }

    public function getSecurityInformation()
    {
        return $this->securityInformation;
    }

    /**
     * Return the list of permissions the user should have in order to display the admin.
     *
     * @param string $context
     *
     * @return array
     */
    public function getPermissionsShow($context)
    {
        return ['LIST'];
    }

    public function showIn($context)
    {
        return $this->isGranted($this->getPermissionsShow($context));
    }

    public function createObjectSecurity($object)
    {
        $this->getSecurityHandler()->createObjectSecurity($this, $object);
    }

    public function setSecurityHandler(SecurityHandlerInterface $securityHandler)
    {
        $this->securityHandler = $securityHandler;
    }

    public function getSecurityHandler()
    {
        return $this->securityHandler;
    }

    public function isGranted($name, $object = null)
    {
        $objectRef = $object ? sprintf('/%s#%s', spl_object_hash($object), $this->id($object)) : '';
        $key = md5(json_encode($name).$objectRef);

        if (!\array_key_exists($key, $this->cacheIsGranted)) {
            $this->cacheIsGranted[$key] = $this->securityHandler->isGranted($this, $name, $object ?: $this);
        }

        return $this->cacheIsGranted[$key];
    }

    public function getUrlSafeIdentifier($model)
    {
        return $this->getModelManager()->getUrlSafeIdentifier($model);
    }

    public function getNormalizedIdentifier($model)
    {
        return $this->getModelManager()->getNormalizedIdentifier($model);
    }

    public function id($model)
    {
        return $this->getNormalizedIdentifier($model);
    }

    public function setValidator($validator)
    {
        // NEXT_MAJOR: Move ValidatorInterface check to method signature
        if (!$validator instanceof ValidatorInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 must be an instance of %s',
                ValidatorInterface::class
            ));
        }

        $this->validator = $validator;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    public function getShow()
    {
        $this->buildShow();

        return $this->show;
    }

    public function setFormTheme(array $formTheme)
    {
        $this->formTheme = $formTheme;
    }

    public function getFormTheme()
    {
        return $this->formTheme;
    }

    public function setFilterTheme(array $filterTheme)
    {
        $this->filterTheme = $filterTheme;
    }

    public function getFilterTheme()
    {
        return $this->filterTheme;
    }

    public function addExtension(AdminExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function setMenuFactory(FactoryInterface $menuFactory)
    {
        $this->menuFactory = $menuFactory;
    }

    public function getMenuFactory()
    {
        return $this->menuFactory;
    }

    public function setRouteBuilder(RouteBuilderInterface $routeBuilder)
    {
        $this->routeBuilder = $routeBuilder;
    }

    public function getRouteBuilder()
    {
        return $this->routeBuilder;
    }

    public function toString($object)
    {
        // NEXT_MAJOR: Remove this check and use object as param typehint.
        if (!\is_object($object)) {
            @trigger_error(sprintf(
                'Passing %s as argument 1 for %s() is deprecated since sonata-project/admin-bundle 3.x.'
                .' Only object will be allowed in version 4.0.',
                \gettype($object),
                __METHOD__
            ), E_USER_DEPRECATED);

            return '';
        }

        if (method_exists($object, '__toString') && null !== $object->__toString()) {
            return (string) $object;
        }

        return sprintf('%s:%s', ClassUtils::getClass($object), spl_object_hash($object));
    }

    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy)
    {
        $this->labelTranslatorStrategy = $labelTranslatorStrategy;
    }

    public function getLabelTranslatorStrategy()
    {
        return $this->labelTranslatorStrategy;
    }

    public function supportsPreviewMode()
    {
        return $this->supportsPreviewMode;
    }

    /**
     * NEXT_MAJOR: Remove this.
     *
     * @deprecated since sonata-project/admin-bundle 3.67, to be removed in 4.0.
     *
     * Set custom per page options.
     */
    public function setPerPageOptions(array $options)
    {
        @trigger_error(sprintf(
            'The method %s is deprecated since sonata-project/admin-bundle 3.67 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $this->perPageOptions = $options;
    }

    /**
     * Returns predefined per page options.
     *
     * @return array
     */
    public function getPerPageOptions()
    {
        // NEXT_MAJOR: Remove this line and uncomment the following
        return $this->perPageOptions;
//        $perPageOptions = $this->getModelManager()->getDefaultPerPageOptions($this->class);
//        $perPageOptions[] = $this->getMaxPerPage();
//
//        $perPageOptions = array_unique($perPageOptions);
//        sort($perPageOptions);
//
//        return $perPageOptions;
    }

    /**
     * Set pager type.
     *
     * @param string $pagerType
     */
    public function setPagerType($pagerType)
    {
        $this->pagerType = $pagerType;
    }

    /**
     * Get pager type.
     *
     * @return string
     */
    public function getPagerType()
    {
        return $this->pagerType;
    }

    /**
     * Returns true if the per page value is allowed, false otherwise.
     *
     * @param int $perPage
     *
     * @return bool
     */
    public function determinedPerPageValue($perPage)
    {
        return \in_array($perPage, $this->getPerPageOptions(), true);
    }

    public function isAclEnabled()
    {
        return $this->getSecurityHandler() instanceof AclSecurityHandlerInterface;
    }

    public function getObjectMetadata($object)
    {
        return new Metadata($this->toString($object));
    }

    public function getListModes()
    {
        return $this->listModes;
    }

    public function setListMode($mode)
    {
        if (!$this->hasRequest()) {
            throw new \RuntimeException(sprintf('No request attached to the current admin: %s', $this->getCode()));
        }

        $this->getRequest()->getSession()->set(sprintf('%s.list_mode', $this->getCode()), $mode);
    }

    public function getListMode()
    {
        if (!$this->hasRequest()) {
            return 'list';
        }

        return $this->getRequest()->getSession()->get(sprintf('%s.list_mode', $this->getCode()), 'list');
    }

    public function getAccessMapping()
    {
        return $this->accessMapping;
    }

    public function checkAccess($action, $object = null)
    {
        $access = $this->getAccess();

        if (!\array_key_exists($action, $access)) {
            throw new \InvalidArgumentException(sprintf(
                'Action "%s" could not be found in access mapping.'
                .' Please make sure your action is defined into your admin class accessMapping property.',
                $action
            ));
        }

        if (!\is_array($access[$action])) {
            $access[$action] = [$access[$action]];
        }

        foreach ($access[$action] as $role) {
            if (false === $this->isGranted($role, $object)) {
                throw new AccessDeniedException(sprintf('Access Denied to the action %s and role %s', $action, $role));
            }
        }
    }

    /**
     * Hook to handle access authorization, without throw Exception.
     *
     * @param string $action
     * @param object $object
     *
     * @return bool
     */
    public function hasAccess($action, $object = null)
    {
        $access = $this->getAccess();

        if (!\array_key_exists($action, $access)) {
            return false;
        }

        if (!\is_array($access[$action])) {
            $access[$action] = [$access[$action]];
        }

        foreach ($access[$action] as $role) {
            if (false === $this->isGranted($role, $object)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string      $action
     * @param object|null $object
     *
     * @return array
     */
    public function configureActionButtons($action, $object = null)
    {
        $list = [];

        if (\in_array($action, ['tree', 'show', 'edit', 'delete', 'list', 'batch'], true)
            && $this->hasRoute('create')
            && $this->hasAccess('create')
        ) {
            $list['create'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_create'),
//                'template' => $this->getTemplateRegistry()->getTemplate('button_create'),
            ];
        }

        if (\in_array($action, ['show', 'delete', 'acl', 'history'], true)
            && $this->hasRoute('edit')
            && $this->canAccessObject('edit', $object)
        ) {
            $list['edit'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_edit'),
                //'template' => $this->getTemplateRegistry()->getTemplate('button_edit'),
            ];
        }

        if (\in_array($action, ['show', 'edit', 'acl'], true)
            && $this->hasRoute('history')
            && $this->canAccessObject('history', $object)
        ) {
            $list['history'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_history'),
                // 'template' => $this->getTemplateRegistry()->getTemplate('button_history'),
            ];
        }

        if (\in_array($action, ['edit', 'history'], true)
            && $this->isAclEnabled()
            && $this->hasRoute('acl')
            && $this->canAccessObject('acl', $object)
        ) {
            $list['acl'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_acl'),
                // 'template' => $this->getTemplateRegistry()->getTemplate('button_acl'),
            ];
        }

        if (\in_array($action, ['edit', 'history', 'acl'], true)
            && $this->hasRoute('show')
            && $this->canAccessObject('show', $object)
            && \count($this->getShow()) > 0
        ) {
            $list['show'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_show'),
                // 'template' => $this->getTemplateRegistry()->getTemplate('button_show'),
            ];
        }

        if (\in_array($action, ['show', 'edit', 'delete', 'acl', 'batch'], true)
            && $this->hasRoute('list')
            && $this->hasAccess('list')
        ) {
            $list['list'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_list'),
                // 'template' => $this->getTemplateRegistry()->getTemplate('button_list'),
            ];
        }

        return $list;
    }

    /**
     * @param string $action
     * @param object $object
     *
     * @return array
     */
    public function getActionButtons($action, $object = null)
    {
        $list = $this->configureActionButtons($action, $object);

        foreach ($this->getExtensions() as $extension) {
            // NEXT_MAJOR: remove method check
            if (method_exists($extension, 'configureActionButtons')) {
                $list = $extension->configureActionButtons($this, $list, $action, $object);
            }
        }

        return $list;
    }

    /**
     * Get the list of actions that can be accessed directly from the dashboard.
     *
     * @return array
     */
    public function getDashboardActions()
    {
        $actions = [];

        if ($this->hasRoute('create') && $this->hasAccess('create')) {
            $actions['create'] = [
                'label' => 'link_add',
                'translation_domain' => 'SonataAdminBundle',
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('action_create'),
                // 'template' => $this->getTemplateRegistry()->getTemplate('action_create'),
                'url' => $this->generateUrl('create'),
                'icon' => 'plus-circle',
            ];
        }

        if ($this->hasRoute('list') && $this->hasAccess('list')) {
            $actions['list'] = [
                'label' => 'link_list',
                'translation_domain' => 'SonataAdminBundle',
                'url' => $this->generateUrl('list'),
                'icon' => 'list',
            ];
        }

        return $actions;
    }

    /**
     * Setting to true will enable mosaic button for the admin screen.
     * Setting to false will hide mosaic button for the admin screen.
     *
     * @param bool $isShown
     */
    final public function showMosaicButton($isShown)
    {
        if ($isShown) {
            $this->listModes['mosaic'] = ['class' => static::MOSAIC_ICON_CLASS];
        } else {
            unset($this->listModes['mosaic']);
        }
    }

    /**
     * @param object $object
     */
    final public function getSearchResultLink($object)
    {
        foreach ($this->searchResultActions as $action) {
            if ($this->hasRoute($action) && $this->hasAccess($action, $object)) {
                return $this->generateObjectUrl($action, $object);
            }
        }

        return null;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * Checks if a filter type is set to a default value.
     *
     * @param string $name
     *
     * @return bool
     */
    final public function isDefaultFilter($name)
    {
        @trigger_error(sprintf(
            'Method "%s" is deprecated since sonata-project/admin-bundle 3.x.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $filter = $this->getFilterParameters();
        $default = $this->getDefaultFilterValues();

        if (!\array_key_exists($name, $filter) || !\array_key_exists($name, $default)) {
            return false;
        }

        return $filter[$name] === $default[$name];
    }

    /**
     * Check object existence and access, without throw Exception.
     *
     * @param string $action
     * @param object $object
     *
     * @return bool
     */
    public function canAccessObject($action, $object)
    {
        if (!\is_object($object)) {
            return false;
        }
        if (!$this->id($object)) {
            return false;
        }

        return $this->hasAccess($action, $object);
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        return $query;
    }

    /**
     * @return MutableTemplateRegistryInterface
     */
    final protected function getTemplateRegistry()
    {
        return $this->templateRegistry;
    }

    /**
     * Returns a list of default sort values.
     *
     * @return array{_page?: int, _per_page?: int, _sort_by?: string, _sort_order?: string}
     */
    final protected function getDefaultSortValues(): array
    {
        $defaultSortValues = [];

        $this->configureDefaultSortValues($defaultSortValues);

        foreach ($this->getExtensions() as $extension) {
            // NEXT_MAJOR: remove method check
            if (method_exists($extension, 'configureDefaultSortValues')) {
                $extension->configureDefaultSortValues($this, $defaultSortValues);
            }
        }

        return $defaultSortValues;
    }

    /**
     * Returns a list of default filters.
     *
     * @return array
     */
    final protected function getDefaultFilterValues()
    {
        $defaultFilterValues = [];

        $this->configureDefaultFilterValues($defaultFilterValues);

        foreach ($this->getExtensions() as $extension) {
            // NEXT_MAJOR: remove method check
            if (method_exists($extension, 'configureDefaultFilterValues')) {
                $extension->configureDefaultFilterValues($this, $defaultFilterValues);
            }
        }

        return $defaultFilterValues;
    }

    protected function configureFormFields(FormMapper $form)
    {
    }

    protected function configureListFields(ListMapper $list)
    {
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
    }

    protected function configureShowFields(ShowMapper $show)
    {
    }

    protected function configureRoutes(RouteCollection $collection)
    {
    }

    /**
     * Allows you to customize batch actions.
     *
     * @param array<string, mixed> $actions List of actions
     *
     * @return array<string, mixed>
     */
    protected function configureBatchActions($actions)
    {
        return $actions;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated Use configureTabMenu instead
     */
    protected function configureSideMenu(ItemInterface $menu, string $action, ?AdminInterface $childAdmin = null)
    {
    }

    /**
     * Configures the tab menu in your admin.
     *
     * @param string $action
     */
    protected function configureTabMenu(ItemInterface $menu, $action, ?AdminInterface $childAdmin = null)
    {
        // Use configureSideMenu not to mess with previous overrides
        // NEXT_MAJOR: remove this line
        $this->configureSideMenu($menu, $action, $childAdmin);
    }

    /**
     * build the view FieldDescription array.
     */
    protected function buildShow()
    {
        if ($this->loaded['show']) {
            return;
        }

        $this->loaded['show'] = true;

        $this->show = $this->getShowBuilder()->getBaseList();
        $mapper = new ShowMapper($this->getShowBuilder(), $this->show, $this);

        $this->configureShowFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureShowFields($mapper);
        }
    }

    /**
     * build the list FieldDescription array.
     */
    protected function buildList()
    {
        if ($this->loaded['list']) {
            return;
        }

        $this->loaded['list'] = true;

        $this->list = $this->getListBuilder()->getBaseList();
        $mapper = new ListMapper($this->getListBuilder(), $this->list, $this);

        if (\count($this->getBatchActions()) > 0 && $this->hasRequest() && !$this->getRequest()->isXmlHttpRequest()) {
            $fieldDescription = $this->getModelManager()->getNewFieldDescriptionInstance(
                $this->getClass(),
                'batch',
                [
                    'label' => 'batch',
                    'code' => '_batch',
                    'sortable' => false,
                    'virtual_field' => true,
                ]
            );

            $fieldDescription->setAdmin($this);
            // NEXT_MAJOR: Remove this line and use commented line below it instead
            $fieldDescription->setTemplate($this->getTemplate('batch'));
            // $fieldDescription->setTemplate($this->getTemplateRegistry()->getTemplate('batch'));

            $mapper->add($fieldDescription, ListMapper::TYPE_BATCH);
        }

        $this->configureListFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureListFields($mapper);
        }

        if ($this->hasRequest() && $this->getRequest()->isXmlHttpRequest()) {
            $fieldDescription = $this->getModelManager()->getNewFieldDescriptionInstance(
                $this->getClass(),
                'select',
                [
                    'label' => false,
                    'code' => '_select',
                    'sortable' => false,
                    'virtual_field' => false,
                ]
            );

            $fieldDescription->setAdmin($this);
            // NEXT_MAJOR: Remove this line and use commented line below it instead
            $fieldDescription->setTemplate($this->getTemplate('select'));
            // $fieldDescription->setTemplate($this->getTemplateRegistry()->getTemplate('select'));

            $mapper->add($fieldDescription, ListMapper::TYPE_SELECT);
        }
    }

    /**
     * Build the form FieldDescription collection.
     */
    protected function buildForm()
    {
        if ($this->loaded['form']) {
            return;
        }

        $this->loaded['form'] = true;

        $formBuilder = $this->getFormBuilder();
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $this->preValidate($event->getData());
        }, 100);

        $this->form = $formBuilder->getForm();
    }

    /**
     * Gets the subclass corresponding to the given name.
     *
     * @param string $name The name of the sub class
     *
     * @return string the subclass
     */
    protected function getSubClass($name)
    {
        if ($this->hasSubClass($name)) {
            return $this->subClasses[$name];
        }

        // NEXT_MAJOR: Throw \LogicException instead.
        throw new \RuntimeException(sprintf('Unable to find the subclass `%s` for admin `%s`', $name, static::class));
    }

    /**
     * Attach the inline validator to the model metadata, this must be done once per admin.
     */
    protected function attachInlineValidator()
    {
        $admin = $this;

        // add the custom inline validation option
        $metadata = $this->validator->getMetadataFor($this->getClass());
        if (!$metadata instanceof GenericMetadata) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Cannot add inline validator for %s because its metadata is an instance of %s instead of %s',
                    $this->getClass(),
                    \get_class($metadata),
                    GenericMetadata::class
                )
            );
        }

        $metadata->addConstraint(new InlineConstraint([
            'service' => $this,
            'method' => static function (ErrorElement $errorElement, $object) use ($admin) {
                /* @var \Sonata\AdminBundle\Admin\AdminInterface $admin */

                // This avoid the main validation to be cascaded to children
                // The problem occurs when a model Page has a collection of Page as property
                if ($admin->hasSubject() && spl_object_hash($object) !== spl_object_hash($admin->getSubject())) {
                    return;
                }

                $admin->validate($errorElement, $object);

                foreach ($admin->getExtensions() as $extension) {
                    $extension->validate($admin, $errorElement, $object);
                }
            },
            'serializingWarning' => true,
        ]));
    }

    /**
     * NEXT_MAJOR: Remove this function.
     *
     * @deprecated since sonata-project/admin-bundle 3.67, to be removed in 4.0.
     *
     * Predefine per page options.
     */
    protected function predefinePerPageOptions()
    {
        array_unshift($this->perPageOptions, $this->maxPerPage);
        $this->perPageOptions = array_unique($this->perPageOptions);
        sort($this->perPageOptions);
    }

    /**
     * Return list routes with permissions name.
     *
     * @return array<string, string|array>
     */
    protected function getAccess()
    {
        $access = array_merge([
            'acl' => 'MASTER',
            'export' => 'EXPORT',
            'historyCompareRevisions' => 'EDIT',
            'historyViewRevision' => 'EDIT',
            'history' => 'EDIT',
            'edit' => 'EDIT',
            'show' => 'VIEW',
            'create' => 'CREATE',
            'delete' => 'DELETE',
            'batchDelete' => 'DELETE',
            'list' => 'LIST',
        ], $this->getAccessMapping());

        foreach ($this->extensions as $extension) {
            // NEXT_MAJOR: remove method check
            if (method_exists($extension, 'getAccessMapping')) {
                $access = array_merge($access, $extension->getAccessMapping($this));
            }
        }

        return $access;
    }

    /**
     * Configures a list of default filters.
     *
     * @param array<string, mixed> $filterValues
     */
    protected function configureDefaultFilterValues(array &$filterValues)
    {
    }

    /**
     * Configures a list of default sort values.
     *
     * Example:
     *   $sortValues['_sort_by'] = 'foo'
     *   $sortValues['_sort_order'] = 'DESC'
     *
     * @phpstan-param array{_page?: int, _per_page?: int, _sort_by?: string, _sort_order?: string} $sortValues
     */
    protected function configureDefaultSortValues(array &$sortValues)
    {
    }

    /**
     * Set the parent object, if any, to the provided object.
     */
    final protected function appendParentObject(object $object): void
    {
        if ($this->isChild() && $this->getParentAssociationMapping()) {
            $parentAdmin = $this->getParent();
            $parentObject = $parentAdmin->getObject($this->request->get($parentAdmin->getIdParameter()));

            if (null !== $parentObject) {
                $propertyAccessor = $this->getConfigurationPool()->getPropertyAccessor();
                $propertyPath = new PropertyPath($this->getParentAssociationMapping());

                $value = $propertyAccessor->getValue($object, $propertyPath);

                if (\is_array($value) || $value instanceof \ArrayAccess) {
                    $value[] = $parentObject;
                    $propertyAccessor->setValue($object, $propertyPath, $value);
                } else {
                    $propertyAccessor->setValue($object, $propertyPath, $parentObject);
                }
            }
        } elseif ($this->hasParentFieldDescription()) {
            $parentAdmin = $this->getParentFieldDescription()->getAdmin();
            $parentObject = $parentAdmin->getObject($this->request->get($parentAdmin->getIdParameter()));

            if (null !== $parentObject) {
                ObjectManipulator::setObject($object, $parentObject, $this->getParentFieldDescription());
            }
        }
    }

    /**
     * Build all the related urls to the current admin.
     */
    private function buildRoutes(): void
    {
        if ($this->loaded['routes']) {
            return;
        }

        $this->loaded['routes'] = true;

        $this->routes = new RouteCollection(
            $this->getBaseCodeRoute(),
            $this->getBaseRouteName(),
            $this->getBaseRoutePattern(),
            $this->getBaseControllerName()
        );

        $this->routeBuilder->build($this, $this->routes);

        $this->configureRoutes($this->routes);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureRoutes($this, $this->routes);
        }
    }
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
