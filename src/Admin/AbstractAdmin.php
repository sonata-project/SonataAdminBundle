<?php

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
use Knp\Menu\FactoryInterface as MenuFactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
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
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\Constraints\InlineConstraint;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as RoutingUrlGeneratorInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class AbstractAdmin implements AdminInterface, DomainObjectInterface, AdminTreeInterface
{
    const CONTEXT_MENU = 'menu';
    const CONTEXT_DASHBOARD = 'dashboard';

    const CLASS_REGEX =
        '@
        (?:([A-Za-z0-9]*)\\\)?        # vendor name / app name
        (Bundle\\\)?                  # optional bundle directory
        ([A-Za-z0-9]+?)(?:Bundle)?\\\ # bundle name, with optional suffix
        (
            Entity|Document|Model|PHPCR|CouchDocument|Phpcr|
            Doctrine\\\Orm|Doctrine\\\Phpcr|Doctrine\\\MongoDB|Doctrine\\\CouchDB
        )\\\(.*)@x';

    const MOSAIC_ICON_CLASS = 'fa fa-th-large fa-fw';

    /**
     * The list FieldDescription constructed from the configureListField method.
     *
     * @var array
     */
    protected $listFieldDescriptions = [];

    /**
     * The show FieldDescription constructed from the configureShowFields method.
     *
     * @var array
     */
    protected $showFieldDescriptions = [];

    /**
     * The list FieldDescription constructed from the configureFormField method.
     *
     * @var array
     */
    protected $formFieldDescriptions = [];

    /**
     * The filter FieldDescription constructed from the configureFilterField method.
     *
     * @var array
     */
    protected $filterFieldDescriptions = [];

    /**
     * The number of result to display in the list.
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
     * Default values to the datagrid.
     *
     * @var array
     */
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
    ];

    /**
     * Predefined per page options.
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
     * @deprecated since 3.34, to be removed in 4.0.
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
     * Reference the parent collection.
     *
     * @var AdminInterface|null
     */
    protected $parent = null;

    /**
     * The base code route refer to the prefix used to generate the route name.
     *
     * NEXT_MAJOR: remove this attribute.
     *
     * @deprecated This attribute is deprecated since 3.24 and will be removed in 4.0
     *
     * @var string
     */
    protected $baseCodeRoute = '';

    /**
     * NEXT_MAJOR: should be default array and private.
     *
     * @var string|array
     */
    protected $parentAssociationMapping = null;

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
     * @var \Symfony\Component\Translation\TranslatorInterface
     *
     * @deprecated since 3.9, to be removed with 4.0
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
    protected $securityHandler = null;

    /**
     * @var ValidatorInterface
     */
    protected $validator = null;

    /**
     * The configuration pool.
     *
     * @var Pool
     */
    protected $configurationPool;

    /**
     * @var MenuItemInterface
     */
    protected $menu;

    /**
     * @var MenuFactoryInterface
     */
    protected $menuFactory;

    /**
     * @var array
     */
    protected $loaded = [
        'view_fields' => false,
        'view_groups' => false,
        'routes' => false,
        'tab_menu' => false,
    ];

    /**
     * @var array
     */
    protected $formTheme = [];

    /**
     * @var array
     */
    protected $filterTheme = [];

    /**
     * @var array
     *
     * @deprecated since 3.34, will be dropped in 4.0. Use TemplateRegistry services instead
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
     * @var array
     */
    private $subClasses = [];

    /**
     * The list collection.
     *
     * @var array
     */
    private $list;

    /**
     * @var FieldDescriptionCollection|null
     */
    private $show;

    /**
     * @var Form|null
     */
    private $form;

    /**
     * @var DatagridInterface
     */
    private $filter;

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
     * @var array|bool
     */
    private $formGroups = false;

    /**
     * The form tabs disposition.
     *
     * @var array|bool
     */
    private $formTabs = false;

    /**
     * The view group disposition.
     *
     * @var array|bool
     */
    private $showGroups = false;

    /**
     * The view tab disposition.
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
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName)
    {
        $this->code = $code;
        $this->class = $class;
        $this->baseControllerName = $baseControllerName;

        $this->predefinePerPageOptions();
        $this->datagridValues['_per_page'] = $this->maxPerPage;
    }

    /**
     * {@inheritdoc}
     *
     * NEXT_MAJOR: return null to indicate no override
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
            $transLabel = $this->trans($label);

            // NEXT_MAJOR: Remove this hack, because all field labels will be translated with the major release
            // No translation key exists
            if ($transLabel == $label) {
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
            $this->classnameLabel = substr($this->getClass(), strrpos($this->getClass(), '\\') + 1);
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
            $filters = $this->request->query->get('filter', []);

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
                $this->datagridValues,
                $this->getDefaultFilterValues(),
                $filters
            );

            if (!$this->determinedPerPageValue($parameters['_per_page'])) {
                $parameters['_per_page'] = $this->maxPerPage;
            }

            // always force the parent value
            if ($this->isChild() && $this->getParentAssociationMapping()) {
                $name = str_replace('.', '__', $this->getParentAssociationMapping());
                $parameters[$name] = ['value' => $this->request->get($this->getParent()->getIdParameter())];
            }
        }

        return $parameters;
    }

    public function buildDatagrid()
    {
        if ($this->datagrid) {
            return;
        }

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
     * @return null|string
     */
    public function getParentAssociationMapping()
    {
        // NEXT_MAJOR: remove array check
        if (\is_array($this->parentAssociationMapping) && $this->getParent()) {
            $parent = $this->getParent()->getCode();

            if (array_key_exists($parent, $this->parentAssociationMapping)) {
                return $this->parentAssociationMapping[$parent];
            }

            throw new \InvalidArgumentException(sprintf(
                "There's no association between %s and %s.",
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
                    throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', \get_class($this)));
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
                throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', \get_class($this)));
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
                    throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', \get_class($this)));
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
                throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', \get_class($this)));
            }

            $this->cachedBaseRouteName = sprintf('admin_%s%s_%s',
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
            if ($this->getParentFieldDescription()) {
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
            'Method "%s" is deprecated since 3.30 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!\in_array($subClass, $this->subClasses)) {
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
            return;
        }

        return $this->getSubClass($this->getActiveSubclassCode());
    }

    public function getActiveSubclassCode()
    {
        if (!$this->hasActiveSubClass()) {
            return;
        }

        $subClass = $this->getRequest()->query->get('subclass');

        if (!$this->hasSubClass($subClass)) {
            return;
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
            // TODO: remove method check in next major release
            if (method_exists($extension, 'configureBatchActions')) {
                $actions = $extension->configureBatchActions($this, $actions);
            }
        }

        foreach ($actions  as $name => &$action) {
            if (!array_key_exists('label', $action)) {
                $action['label'] = $this->getTranslationLabel($name, 'batch', 'label');
            }

            if (!array_key_exists('translation_domain', $action)) {
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
        return '{'.$this->getIdParameter().'}';
    }

    public function getIdParameter()
    {
        $parameter = 'id';

        for ($i = 0; $i < $this->getChildDepth(); ++$i) {
            $parameter = 'child'.ucfirst($parameter);
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

        return ($admin->getBaseRouteName().'_'.$name) == $route;
    }

    public function generateObjectUrl($name, $object, array $parameters = [], $absolute = RoutingUrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $parameters['id'] = $this->getUrlsafeIdentifier($object);

        return $this->generateUrl($name, $parameters, $absolute);
    }

    public function generateUrl($name, array $parameters = [], $absolute = RoutingUrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->routeGenerator->generateUrl($this, $name, $parameters, $absolute);
    }

    public function generateMenuUrl($name, array $parameters = [], $absolute = RoutingUrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->routeGenerator->generateMenuUrl($this, $name, $parameters, $absolute);
    }

    final public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry)
    {
        $this->templateRegistry = $templateRegistry;
    }

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
     * @deprecated since 3.34, will be dropped in 4.0. Use TemplateRegistry services instead
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->getTemplateRegistry()->getTemplates();
    }

    /**
     * @deprecated since 3.34, will be dropped in 4.0. Use TemplateRegistry services instead
     *
     * @param string $name
     *
     * @return null|string
     */
    public function getTemplate($name)
    {
        return $this->getTemplateRegistry()->getTemplate($name);
    }

    public function getNewInstance()
    {
        $object = $this->getModelManager()->getModelInstance($this->getClass());
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
            $admin = $pool->getAdminByAdminCode($adminCode);
        } else {
            $admin = $pool->getAdminByClass($fieldDescription->getTargetEntity());
        }

        if (!$admin) {
            return;
        }

        if ($this->hasRequest()) {
            $admin->setRequest($this->getRequest());
        }

        $fieldDescription->setAssociationAdmin($admin);
    }

    public function getObject($id)
    {
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

    public function createQuery($context = 'list')
    {
        if (\func_num_args() > 0) {
            @trigger_error(
                'The $context argument of '.__METHOD__.' is deprecated since 3.3, to be removed in 4.0.',
                E_USER_DEPRECATED
            );
        }
        $query = $this->getModelManager()->createQuery($this->getClass());

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

    public function buildTabMenu($action, AdminInterface $childAdmin = null)
    {
        if ($this->loaded['tab_menu']) {
            return;
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
    }

    public function buildSideMenu($action, AdminInterface $childAdmin = null)
    {
        return $this->buildTabMenu($action, $childAdmin);
    }

    /**
     * @param string $action
     *
     * @return ItemInterface
     */
    public function getSideMenu($action, AdminInterface $childAdmin = null)
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
     * @return AbstractAdmin the root admin class
     */
    public function getRoot()
    {
        $parentFieldDescription = $this->getParentFieldDescription();

        if (!$parentFieldDescription) {
            return $this;
        }

        return $parentFieldDescription->getAdmin()->getRoot();
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
     * @deprecated since 3.34, to be removed in 4.0.
     */
    public function setPersistFilters($persist)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.34 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $this->persistFilters = $persist;
    }

    /**
     * @param FilterPersisterInterface|null $filterPersister
     */
    public function setFilterPersister(FilterPersisterInterface $filterPersister = null)
    {
        $this->filterPersister = $filterPersister;
        // NEXT_MAJOR remove the deprecated property will be removed. Needed for persisted filter condition.
        $this->persistFilters = true;
    }

    /**
     * @param int $maxPerPage
     */
    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;
    }

    /**
     * @return int
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
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
     * @param array $group
     */
    public function reorderFormGroup($group, array $keys)
    {
        $formGroups = $this->getFormGroups();
        $formGroups[$group]['fields'] = array_merge(array_flip($keys), $formGroups[$group]['fields']);
        $this->setFormGroups($formGroups);
    }

    public function getFormTabs()
    {
        return $this->formTabs;
    }

    public function setFormTabs(array $formTabs)
    {
        $this->formTabs = $formTabs;
    }

    public function getShowTabs()
    {
        return $this->showTabs;
    }

    public function setShowTabs(array $showTabs)
    {
        $this->showTabs = $showTabs;
    }

    public function getShowGroups()
    {
        return $this->showGroups;
    }

    public function setShowGroups(array $showGroups)
    {
        $this->showGroups = $showGroups;
    }

    public function reorderShowGroup($group, array $keys)
    {
        $showGroups = $this->getShowGroups();
        $showGroups[$group]['fields'] = array_merge(array_flip($keys), $showGroups[$group]['fields']);
        $this->setShowGroups($showGroups);
    }

    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription)
    {
        $this->parentFieldDescription = $parentFieldDescription;
    }

    public function getParentFieldDescription()
    {
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

            @trigger_error(
                sprintf($message, \get_class($subject), $this->getClass()),
                E_USER_DEPRECATED
            ); // NEXT_MAJOR : throw an exception instead
        }

        $this->subject = $subject;
    }

    public function getSubject()
    {
        if (null === $this->subject && $this->request && !$this->hasParentFieldDescription()) {
            $id = $this->request->get($this->getIdParameter());

            if (null !== $id) {
                $this->subject = $this->getObject($id);
            }
        }

        return $this->subject;
    }

    public function hasSubject()
    {
        return (bool) $this->getSubject();
    }

    public function getFormFieldDescriptions()
    {
        $this->buildForm();

        return $this->formFieldDescriptions;
    }

    public function getFormFieldDescription($name)
    {
        return $this->hasFormFieldDescription($name) ? $this->formFieldDescriptions[$name] : null;
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
        return array_key_exists($name, $this->formFieldDescriptions) ? true : false;
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
     * @return array collection of form FieldDescription
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

        return $this->hasShowFieldDescription($name) ? $this->showFieldDescriptions[$name] : null;
    }

    public function hasShowFieldDescription($name)
    {
        return array_key_exists($name, $this->showFieldDescriptions);
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
        return $this->hasListFieldDescription($name) ? $this->listFieldDescriptions[$name] : null;
    }

    public function hasListFieldDescription($name)
    {
        $this->buildList();

        return array_key_exists($name, $this->listFieldDescriptions) ? true : false;
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
        return $this->hasFilterFieldDescription($name) ? $this->filterFieldDescriptions[$name] : null;
    }

    public function hasFilterFieldDescription($name)
    {
        return array_key_exists($name, $this->filterFieldDescriptions) ? true : false;
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
        for ($parentAdmin = $this; null !== $parentAdmin; $parentAdmin = $parentAdmin->getParent()) {
            if ($parentAdmin->getCode() !== $child->getCode()) {
                continue;
            }

            throw new \RuntimeException(sprintf(
                'Circular reference detected! The child admin `%s` is already in the parent tree of the `%s` admin.',
                $child->getCode(), $this->getCode()
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
                'Calling "addChild" without second argument is deprecated since 3.35'
                .' and will not be allowed in 4.0.',
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
        return $this->hasChild($code) ? $this->children[$code] : null;
    }

    public function setParent(AdminInterface $parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
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
            return;
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
            $this->uniqid = 's'.substr(md5($this->getBaseCodeRoute()), 0, 10);
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
                throw new \RuntimeException(sprintf('The %s::getPersistentParameters must return an array', \get_class($extension)));
            }

            $parameters = array_merge($parameters, $params);
        }

        return $parameters;
    }

    /**
     * @param string $name
     *
     * @return null|mixed
     */
    public function getPersistentParameter($name)
    {
        $parameters = $this->getPersistentParameters();

        return isset($parameters[$name]) ? $parameters[$name] : null;
    }

    public function getBreadcrumbs($action)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.2 and will be removed in 4.0.'.
            ' Use Sonata\AdminBundle\Admin\BreadcrumbsBuilder::getBreadcrumbs instead.',
            E_USER_DEPRECATED
        );

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
    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.2 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

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
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.2 and will be removed in 4.0.'.
            ' Use the sonata.admin.breadcrumbs_builder service instead.',
            E_USER_DEPRECATED
        );
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
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.2 and will be removed in 4.0.'.
            ' Use the sonata.admin.breadcrumbs_builder service instead.',
            E_USER_DEPRECATED
        );
        $this->breadcrumbsBuilder = $value;

        return $this;
    }

    public function setCurrentChild($currentChild)
    {
        $this->currentChild = $currentChild;
    }

    public function getCurrentChild()
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
            if ($children->getCurrentChild()) {
                return $children;
            }
        }
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.9 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

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
     * @deprecated since 3.9, to be removed with 4.0
     */
    public function transChoice($id, $count, array $parameters = [], $domain = null, $locale = null)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.9 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

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
     * @deprecated since 3.9, to be removed with 4.0
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $args = \func_get_args();
        if (isset($args[1]) && $args[1]) {
            @trigger_error(
                'The '.__METHOD__.' method is deprecated since version 3.9 and will be removed in 4.0.',
                E_USER_DEPRECATED
            );
        }

        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * NEXT_MAJOR: remove this method
     *
     * @deprecated since 3.9, to be removed with 4.0
     */
    public function getTranslator()
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.9 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

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
     * @deprecated This method is deprecated since 3.24 and will be removed in 4.0
     *
     * @param string $baseCodeRoute
     */
    public function setBaseCodeRoute($baseCodeRoute)
    {
        @trigger_error(
            'The '.__METHOD__.' is deprecated since 3.24 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $this->baseCodeRoute = $baseCodeRoute;
    }

    public function getBaseCodeRoute()
    {
        // NEXT_MAJOR: Uncomment the following lines.
        // if ($this->isChild()) {
        //     return $this->getParent()->getBaseCodeRoute().'|'.$this->getCode();
        // }
        //
        // return $this->getCode();

        // NEXT_MAJOR: Remove all the code below.
        if ($this->isChild()) {
            $parentCode = $this->getParent()->getCode();

            if ($this->getParent()->isChild()) {
                $parentCode = $this->getParent()->getBaseCodeRoute();
            }

            return $parentCode.'|'.$this->getCode();
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
        switch ($context) {
            case self::CONTEXT_DASHBOARD:
            case self::CONTEXT_MENU:
            default:
                return ['LIST'];
        }
    }

    public function showIn($context)
    {
        switch ($context) {
            case self::CONTEXT_DASHBOARD:
            case self::CONTEXT_MENU:
            default:
                return $this->isGranted($this->getPermissionsShow($context));
        }
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
        $key = md5(json_encode($name).($object ? '/'.spl_object_hash($object) : ''));

        if (!array_key_exists($key, $this->cacheIsGranted)) {
            $this->cacheIsGranted[$key] = $this->securityHandler->isGranted($this, $name, $object ?: $this);
        }

        return $this->cacheIsGranted[$key];
    }

    public function getUrlsafeIdentifier($entity)
    {
        return $this->getModelManager()->getUrlsafeIdentifier($entity);
    }

    public function getNormalizedIdentifier($entity)
    {
        return $this->getModelManager()->getNormalizedIdentifier($entity);
    }

    public function id($entity)
    {
        return $this->getNormalizedIdentifier($entity);
    }

    public function setValidator($validator)
    {
        // NEXT_MAJOR: Move ValidatorInterface check to method signature
        if (!$validator instanceof ValidatorInterface) {
            throw new \InvalidArgumentException(
                'Argument 1 must be an instance of Symfony\Component\Validator\Validator\ValidatorInterface'
            );
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

    public function setMenuFactory(MenuFactoryInterface $menuFactory)
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
        if (!\is_object($object)) {
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
     * Set custom per page options.
     */
    public function setPerPageOptions(array $options)
    {
        $this->perPageOptions = $options;
    }

    /**
     * Returns predefined per page options.
     *
     * @return array
     */
    public function getPerPageOptions()
    {
        return $this->perPageOptions;
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
        return \in_array($perPage, $this->perPageOptions);
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

        if (!array_key_exists($action, $access)) {
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

        if (!array_key_exists($action, $access)) {
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

    public function configureActionButtons($action, $object = null)
    {
        $list = [];

        if (\in_array($action, ['tree', 'show', 'edit', 'delete', 'list', 'batch'])
            && $this->hasAccess('create')
            && $this->hasRoute('create')
        ) {
            $list['create'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_create'),
//                'template' => $this->getTemplateRegistry()->getTemplate('button_create'),
            ];
        }

        if (\in_array($action, ['show', 'delete', 'acl', 'history'])
            && $this->canAccessObject('edit', $object)
            && $this->hasRoute('edit')
        ) {
            $list['edit'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_edit'),
                //'template' => $this->getTemplateRegistry()->getTemplate('button_edit'),
            ];
        }

        if (\in_array($action, ['show', 'edit', 'acl'])
            && $this->canAccessObject('history', $object)
            && $this->hasRoute('history')
        ) {
            $list['history'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_history'),
                // 'template' => $this->getTemplateRegistry()->getTemplate('button_history'),
            ];
        }

        if (\in_array($action, ['edit', 'history'])
            && $this->isAclEnabled()
            && $this->canAccessObject('acl', $object)
            && $this->hasRoute('acl')
        ) {
            $list['acl'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_acl'),
                // 'template' => $this->getTemplateRegistry()->getTemplate('button_acl'),
            ];
        }

        if (\in_array($action, ['edit', 'history', 'acl'])
            && $this->canAccessObject('show', $object)
            && \count($this->getShow()) > 0
            && $this->hasRoute('show')
        ) {
            $list['show'] = [
                // NEXT_MAJOR: Remove this line and use commented line below it instead
                'template' => $this->getTemplate('button_show'),
                // 'template' => $this->getTemplateRegistry()->getTemplate('button_show'),
            ];
        }

        if (\in_array($action, ['show', 'edit', 'delete', 'acl', 'batch'])
            && $this->hasAccess('list')
            && $this->hasRoute('list')
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
     * @param mixed  $object
     *
     * @return array
     */
    public function getActionButtons($action, $object = null)
    {
        $list = $this->configureActionButtons($action, $object);

        foreach ($this->getExtensions() as $extension) {
            // TODO: remove method check in next major release
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
    }

    /**
     * Checks if a filter type is set to a default value.
     *
     * @param string $name
     *
     * @return bool
     */
    final public function isDefaultFilter($name)
    {
        $filter = $this->getFilterParameters();
        $default = $this->getDefaultFilterValues();

        if (!array_key_exists($name, $filter) || !array_key_exists($name, $default)) {
            return false;
        }

        return $filter[$name] == $default[$name];
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
        return $object && $this->id($object) && $this->hasAccess($action, $object);
    }

    /**
     * @return MutableTemplateRegistryInterface
     */
    final protected function getTemplateRegistry()
    {
        return $this->templateRegistry;
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
            // NEXT_MAJOR: remove method check in next major release
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
     * @param array $actions List of actions
     *
     * @return array
     */
    protected function configureBatchActions($actions)
    {
        return $actions;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @return mixed
     *
     * @deprecated Use configureTabMenu instead
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
    }

    /**
     * Configures the tab menu in your admin.
     *
     * @param string $action
     *
     * @return mixed
     */
    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        // Use configureSideMenu not to mess with previous overrides
        // TODO remove once deprecation period is over
        $this->configureSideMenu($menu, $action, $childAdmin);
    }

    /**
     * build the view FieldDescription array.
     */
    protected function buildShow()
    {
        if ($this->show) {
            return;
        }

        $this->show = new FieldDescriptionCollection();
        $mapper = new ShowMapper($this->showBuilder, $this->show, $this);

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
        if ($this->list) {
            return;
        }

        $this->list = $this->getListBuilder()->getBaseList();

        $mapper = new ListMapper($this->getListBuilder(), $this->list, $this);

        if (\count($this->getBatchActions()) > 0) {
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

            $mapper->add($fieldDescription, 'batch');
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

            $mapper->add($fieldDescription, 'select');
        }
    }

    /**
     * Build the form FieldDescription collection.
     */
    protected function buildForm()
    {
        if ($this->form) {
            return;
        }

        // append parent object if any
        // todo : clean the way the Admin class can retrieve set the object
        if ($this->isChild() && $this->getParentAssociationMapping()) {
            $parent = $this->getParent()->getObject($this->request->get($this->getParent()->getIdParameter()));

            $propertyAccessor = $this->getConfigurationPool()->getPropertyAccessor();
            $propertyPath = new PropertyPath($this->getParentAssociationMapping());

            $object = $this->getSubject();

            $value = $propertyAccessor->getValue($object, $propertyPath);

            if (\is_array($value) || ($value instanceof \Traversable && $value instanceof \ArrayAccess)) {
                $value[] = $parent;
                $propertyAccessor->setValue($object, $propertyPath, $value);
            } else {
                $propertyAccessor->setValue($object, $propertyPath, $parent);
            }
        }

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

        throw new \RuntimeException(sprintf(
            'Unable to find the subclass `%s` for admin `%s`',
            $name,
            \get_class($this)
        ));
    }

    /**
     * Attach the inline validator to the model metadata, this must be done once per admin.
     */
    protected function attachInlineValidator()
    {
        $admin = $this;

        // add the custom inline validation option
        $metadata = $this->validator->getMetadataFor($this->getClass());

        $metadata->addConstraint(new InlineConstraint([
            'service' => $this,
            'method' => function (ErrorElement $errorElement, $object) use ($admin) {
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
     * @return array
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
            // TODO: remove method check in next major release
            if (method_exists($extension, 'getAccessMapping')) {
                $access = array_merge($access, $extension->getAccessMapping($this));
            }
        }

        return $access;
    }

    /**
     * Returns a list of default filters.
     */
    protected function configureDefaultFilterValues(array &$filterValues)
    {
    }

    /**
     * Build all the related urls to the current admin.
     */
    private function buildRoutes()
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
