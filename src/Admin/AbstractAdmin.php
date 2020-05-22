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
     * NEXT_MAJOR: Remove this property.
     *
     * The number of result to display in the list.
     *
     * @deprecated since sonata-project/admin-bundle 3.x.
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
     * @deprecated since sonata-project/admin-bundle 3.x, use configureDefaultSortValues() instead.
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
     * @deprecated since sonata-project/admin-bundle 3.x.
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
     * The related parent association, ie if OrderElement has a parent property named order,
     * then the $parentAssociationMapping must be a string named `order`.
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
     * @var string|array
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
     * @var \Symfony\Component\Translation\TranslatorInterface
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
        'view_fields' => false,
        'view_groups' => false,
        'routes' => false,
        'tab_menu' => false,
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
     * @var FieldDescriptionCollection
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
                'Passing other type than string as argument 1 for method %s() is deprecated since sonata-project/admin-bundle 3.65. It will accept only string in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }
        $this->code = $code;
        if (!\is_string($class)) {
            @trigger_error(sprintf(
                'Passing other type than string as argument 2 for method %s() is deprecated since sonata-project/admin-bundle 3.65. It will accept only string in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }
        $this->class = $class;
        if (null !== $baseControllerName && !\is_string($baseControllerName)) {
            @trigger_error(sprintf(
                'Passing other type than string or null as argument 3 for method %s() is deprecated since sonata-project/admin-bundle 3.65. It will accept only string and null in version 4.0.',
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
     * {@inheritdoc}
     */
    public function getExportFields(): array
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
            if ($transLabel === $label) {
                $fields[$key] = $field;
            } else {
                $fields[$transLabel] = $field;
            }
        }

        return $this->getModelManager()->getDataSourceIterator($datagrid, $fields);
    }

    public function validate(ErrorElement $errorElement, $object): void
    {
    }

    /**
     * define custom variable.
     */
    public function initialize(): void
    {
        if (!$this->classnameLabel) {
            /* NEXT_MAJOR: remove cast to string, null is not supposed to be
            supported but was documented as such */
            $this->classnameLabel = substr(
                (string) $this->getClass(),
                strrpos((string) $this->getClass(), '\\') + 1
            );
        }

        $this->configure();
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

    public function delete($object): void
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

    public function preValidate(object $object): void
    {
    }

    public function preUpdate($object): void
    {
    }

    public function postUpdate($object): void
    {
    }

    public function prePersist($object): void
    {
    }

    public function postPersist($object): void
    {
    }

    public function preRemove($object): void
    {
    }

    public function postRemove($object): void
    {
    }

    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements): void
    {
    }

    public function getFilterParameters()
    {
        $parameters = [];

        // build the values array
        if ($this->hasRequest()) {
            $filters = $this->request->query->get('filter', []);
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
    final public function addParentAssociationMapping($code, $value): void
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
                    throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', static::class));
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
                throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', static::class));
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
                    throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', static::class));
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
                throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', static::class));
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

    public function getSubClasses(): array
    {
        return $this->subClasses;
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    public function addSubClass($subClass): void
    {
        @trigger_error(sprintf(
            'Method "%s" is deprecated since sonata-project/admin-bundle 3.30 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!\in_array($subClass, $this->subClasses, true)) {
            $this->subClasses[] = $subClass;
        }
    }

    public function setSubClasses(array $subClasses): void
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
                'Calling %s() when there is no active subclass is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0. '.
                'Use %s::hasActiveSubClass() to know if there is an active subclass.',
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
                'Calling %s() when there is no active subclass is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0. '.
                'Use %s::hasActiveSubClass() to know if there is an active subclass.',
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
                'Calling %s() when there is no active subclass is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0. '.
                'Use %s::hasActiveSubClass() to know if there is an active subclass.',
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
            $actions = $extension->configureBatchActions($this, $actions);
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

    public function isCurrentRoute(string $name, ?string $adminCode = null): bool
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

        return ($admin->getBaseRouteName().'_'.$name) === $route;
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

    final public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry): void
    {
        $this->templateRegistry = $templateRegistry;
    }

    /**
     * @param array<string, string> $templates
     */
    public function setTemplates(array $templates): void
    {
        $this->getTemplateRegistry()->setTemplates($templates);
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplate($name, $template): void
    {
        $this->getTemplateRegistry()->setTemplate($name, $template);
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
    public function defineFormBuilder(FormBuilderInterface $formBuilder): void
    {
        if (!$this->hasSubject()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no subject is deprecated since sonata-project/admin-bundle 3.65 and will throw an exception in 4.0. '.
                'Use %s::setSubject() to set the subject.',
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

    public function attachAdminClass(FieldDescriptionInterface $fieldDescription): void
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

    /**
     * @final since sonata-project/admin-bundle 3.63.0
     */
    public function createQuery($context = 'list')
    {
        if (\func_num_args() > 0) {
            @trigger_error(
                'The $context argument of '.__METHOD__.' is deprecated since 3.3, to be removed in 4.0.',
                E_USER_DEPRECATED
            );
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

    public function buildTabMenu($action, ?AdminInterface $childAdmin = null): ItemInterface
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

        $this->buildTabMenu($action, $childAdmin);

        return $this->menu;
    }

    public function getRootCode(): string
    {
        return $this->getRoot()->getCode();
    }

    public function getRoot(): AdminInterface
    {
        if (!$this->hasParentFieldDescription()) {
            return $this;
        }

        return $this->getParentFieldDescription()->getAdmin()->getRoot();
    }

    public function setBaseControllerName($baseControllerName): void
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
    public function setLabel($label): void
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
    public function setPersistFilters($persist): void
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.34 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $this->persistFilters = $persist;
    }

    public function setFilterPersister(?FilterPersisterInterface $filterPersister = null): void
    {
        $this->filterPersister = $filterPersister;
        // NEXT_MAJOR remove the deprecated property will be removed. Needed for persisted filter condition.
        $this->persistFilters = true;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
     *
     * @param int $maxPerPage
     */
    public function setMaxPerPage($maxPerPage): void
    {
        @trigger_error(sprintf(
            'The method %s is deprecated since sonata-project/admin-bundle 3.x and will be removed in 4.0.',
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
    public function setMaxPageLinks($maxPageLinks): void
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
                'Returning other type than array in method %s() is deprecated since sonata-project/admin-bundle 3.65. It will return only array in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->formGroups;
    }

    public function setFormGroups(array $formGroups): void
    {
        $this->formGroups = $formGroups;
    }

    public function removeFieldFromFormGroup($key): void
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
    public function reorderFormGroup($group, array $keys): void
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
                'Returning other type than array in method %s() is deprecated since sonata-project/admin-bundle 3.65. It will return only array in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->formTabs;
    }

    public function setFormTabs(array $formTabs): void
    {
        $this->formTabs = $formTabs;
    }

    public function getShowTabs()
    {
        if (!\is_array($this->showTabs) && 'sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'Returning other type than array in method %s() is deprecated since sonata-project/admin-bundle 3.65. It will return only array in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->showTabs;
    }

    public function setShowTabs(array $showTabs): void
    {
        $this->showTabs = $showTabs;
    }

    public function getShowGroups()
    {
        if (!\is_array($this->showGroups) && 'sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'Returning other type than array in method %s() is deprecated since sonata-project/admin-bundle 3.65. It will return only array in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->showGroups;
    }

    public function setShowGroups(array $showGroups): void
    {
        $this->showGroups = $showGroups;
    }

    public function reorderShowGroup($group, array $keys): void
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        $showGroups = $this->getShowGroups('sonata_deprecation_mute');
        $showGroups[$group]['fields'] = array_merge(array_flip($keys), $showGroups[$group]['fields']);
        $this->setShowGroups($showGroups);
    }

    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription): void
    {
        $this->parentFieldDescription = $parentFieldDescription;
    }

    public function getParentFieldDescription()
    {
        if (!$this->hasParentFieldDescription()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no parent field description is deprecated since sonata-project/admin-bundle 3.66 and will throw an exception in 4.0. '.
                'Use %s::hasParentFieldDescription() to know if there is a parent field description.',
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

    public function setSubject($subject): void
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
        if (!$this->hasSubject()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no subject is deprecated since sonata-project/admin-bundle 3.66 and will throw an exception in 4.0. '.
                'Use %s::hasSubject() to know if there is a subject.',
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
        return \array_key_exists($name, $this->formFieldDescriptions) ? true : false;
    }

    public function addFormFieldDescription($name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->formFieldDescriptions[$name] = $fieldDescription;
    }

    /**
     * remove a FieldDescription.
     *
     * @param string $name
     */
    public function removeFormFieldDescription($name): void
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
        return \array_key_exists($name, $this->showFieldDescriptions);
    }

    public function addShowFieldDescription($name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->showFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeShowFieldDescription($name): void
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
        if (!$this->hasListFieldDescription($name)) {
            @trigger_error(sprintf(
                'Calling %s() when there is no list field description is deprecated since sonata-project/admin-bundle 3.66 and will throw an exception in 4.0. '.
                'Use %s::hasListFieldDescription(\'%s\') to know if there is a list field description.',
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

    public function addListFieldDescription($name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->listFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeListFieldDescription($name): void
    {
        unset($this->listFieldDescriptions[$name]);
    }

    public function getFilterFieldDescription($name)
    {
        return $this->hasFilterFieldDescription($name) ? $this->filterFieldDescriptions[$name] : null;
    }

    public function hasFilterFieldDescription($name)
    {
        return \array_key_exists($name, $this->filterFieldDescriptions) ? true : false;
    }

    public function addFilterFieldDescription($name, FieldDescriptionInterface $fieldDescription): void
    {
        $this->filterFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeFilterFieldDescription($name): void
    {
        unset($this->filterFieldDescriptions[$name]);
    }

    public function getFilterFieldDescriptions()
    {
        $this->buildDatagrid();

        return $this->filterFieldDescriptions;
    }

    public function addChild(AdminInterface $child): void
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
                'Calling "addChild" without second argument is deprecated since'
                .' sonata-project/admin-bundle 3.35 and will not be allowed in 4.0.',
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

    public function setParent(AdminInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        if (!$this->isChild()) {
            @trigger_error(sprintf(
                'Calling %s() when there is no parent is deprecated since sonata-project/admin-bundle 3.66 and will throw an exception in 4.0. '.
                'Use %s::isChild() to know if there is a parent.',
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

    public function setUniqid($uniqid): void
    {
        $this->uniqid = $uniqid;
    }

    public function getUniqid()
    {
        if (!$this->uniqid) {
            $this->uniqid = 's'.uniqid();
        }

        return $this->uniqid;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getPersistentParameter(string $name)
    {
        $parameters = $this->getPersistentParameters();

        return $parameters[$name] ?? null;
    }

    public function setCurrentChild($currentChild): void
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
        @trigger_error(
            sprintf(
                'The %s() method is deprecated since version 3.65 and will be removed in 4.0. Use %s::isCurrentChild() instead.',
                __METHOD__,
                __CLASS__
            ),
            E_USER_DEPRECATED
        );

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
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed with 4.0
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

    public function setTranslationDomain($translationDomain): void
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
     */
    public function setTranslator(TranslatorInterface $translator): void
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
     * @deprecated since sonata-project/admin-bundle 3.9, to be removed with 4.0
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

    public function setRequest(Request $request): void
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

    public function setFormContractor(FormContractorInterface $formBuilder): void
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

    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder): void
    {
        $this->datagridBuilder = $datagridBuilder;
    }

    public function getDatagridBuilder()
    {
        return $this->datagridBuilder;
    }

    public function setListBuilder(ListBuilderInterface $listBuilder): void
    {
        $this->listBuilder = $listBuilder;
    }

    public function getListBuilder()
    {
        return $this->listBuilder;
    }

    public function setShowBuilder(ShowBuilderInterface $showBuilder): void
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

    public function setConfigurationPool(Pool $configurationPool): void
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

    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator): void
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

    public function getBaseCodeRoute()
    {
        if ($this->isChild()) {
            return $this->getParent()->getBaseCodeRoute().'|'.$this->getCode();
        }

        return $this->getCode();
    }

    public function getModelManager()
    {
        return $this->modelManager;
    }

    public function setModelManager(ModelManagerInterface $modelManager): void
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
    public function setManagerType($type): void
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
    public function setSecurityInformation(array $information): void
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

    public function createObjectSecurity($object): void
    {
        $this->getSecurityHandler()->createObjectSecurity($this, $object);
    }

    public function setSecurityHandler(SecurityHandlerInterface $securityHandler): void
    {
        $this->securityHandler = $securityHandler;
    }

    public function getSecurityHandler()
    {
        return $this->securityHandler;
    }

    public function isGranted($name, $object = null)
    {
        $objectRef = $object ? '/'.spl_object_hash($object).'#'.$this->id($object) : '';
        $key = md5(json_encode($name).$objectRef);

        if (!\array_key_exists($key, $this->cacheIsGranted)) {
            $this->cacheIsGranted[$key] = $this->securityHandler->isGranted($this, $name, $object ?: $this);
        }

        return $this->cacheIsGranted[$key];
    }

    public function getUrlSafeIdentifier($entity)
    {
        return $this->getModelManager()->getUrlSafeIdentifier($entity);
    }

    public function getNormalizedIdentifier($entity)
    {
        return $this->getModelManager()->getNormalizedIdentifier($entity);
    }

    public function id($entity)
    {
        return $this->getNormalizedIdentifier($entity);
    }

    public function setValidator(ValidatorInterface $validator): void
    {
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

    public function setFormTheme(array $formTheme): void
    {
        $this->formTheme = $formTheme;
    }

    public function getFormTheme()
    {
        return $this->formTheme;
    }

    public function setFilterTheme(array $filterTheme): void
    {
        $this->filterTheme = $filterTheme;
    }

    public function getFilterTheme()
    {
        return $this->filterTheme;
    }

    public function addExtension(AdminExtensionInterface $extension): void
    {
        $this->extensions[] = $extension;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function setMenuFactory(FactoryInterface $menuFactory): void
    {
        $this->menuFactory = $menuFactory;
    }

    public function getMenuFactory()
    {
        return $this->menuFactory;
    }

    public function setRouteBuilder(RouteBuilderInterface $routeBuilder): void
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

    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy): void
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
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
     *
     * Set custom per page options.
     */
    public function setPerPageOptions(array $options): void
    {
        @trigger_error(sprintf(
            'The method %s is deprecated since sonata-project/admin-bundle 3.x and will be removed in 4.0.',
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
    public function setPagerType($pagerType): void
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

    public function setListMode($mode): void
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

    public function checkAccess($action, $object = null): void
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
     * {@inheritdoc}
     */
    public function hasAccess(string $action, ?object $object = null): bool
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
     * @param object|null $object
     */
    final public function getActionButtons(string $action, $object = null): array
    {
        $buttonList = [];

        if (\in_array($action, ['tree', 'show', 'edit', 'delete', 'list', 'batch'], true)
            && $this->hasAccess('create')
            && $this->hasRoute('create')
        ) {
            $buttonList['create'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_create'),
            ];
        }

        if (\in_array($action, ['show', 'delete', 'acl', 'history'], true)
            && $this->canAccessObject('edit', $object)
            && $this->hasRoute('edit')
        ) {
            $buttonList['edit'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_edit'),
            ];
        }

        if (\in_array($action, ['show', 'edit', 'acl'], true)
            && $this->canAccessObject('history', $object)
            && $this->hasRoute('history')
        ) {
            $buttonList['history'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_history'),
            ];
        }

        if (\in_array($action, ['edit', 'history'], true)
            && $this->isAclEnabled()
            && $this->canAccessObject('acl', $object)
            && $this->hasRoute('acl')
        ) {
            $buttonList['acl'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_acl'),
            ];
        }

        if (\in_array($action, ['edit', 'history', 'acl'], true)
            && $this->canAccessObject('show', $object)
            && \count($this->getShow()) > 0
            && $this->hasRoute('show')
        ) {
            $buttonList['show'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_show'),
            ];
        }

        if (\in_array($action, ['show', 'edit', 'delete', 'acl', 'batch'], true)
            && $this->hasAccess('list')
            && $this->hasRoute('list')
        ) {
            $buttonList['list'] = [
                'template' => $this->getTemplateRegistry()->getTemplate('button_list'),
            ];
        }

        $buttonList = $this->configureActionButtons($buttonList, $action, $object);

        foreach ($this->getExtensions() as $extension) {
            $buttonList = $extension->configureActionButtons($this, $buttonList, $action, $object);
        }

        return $buttonList;
    }

    /**
     * {@inheritdoc}
     */
    public function getDashboardActions()
    {
        $actions = [];

        if ($this->hasRoute('create') && $this->hasAccess('create')) {
            $actions['create'] = [
                'label' => 'link_add',
                'translation_domain' => 'SonataAdminBundle',
                'template' => $this->getTemplateRegistry()->getTemplate('action_create'),
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
     * {@inheritdoc}
     */
    final public function showMosaicButton($isShown): void
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
    final public function getSearchResultLink($object): ?string
    {
        foreach ($this->searchResultActions as $action) {
            if ($this->hasRoute($action) && $this->hasAccess($action, $object)) {
                return $this->generateObjectUrl($action, $object);
            }
        }

        return null;
    }

    /**
     * Checks if a filter type is set to a default value.
     */
    final public function isDefaultFilter(string $name): bool
    {
        $filter = $this->getFilterParameters();
        $default = $this->getDefaultFilterValues();

        if (!\array_key_exists($name, $filter) || !\array_key_exists($name, $default)) {
            return false;
        }

        return $filter[$name] === $default[$name];
    }

    public function canAccessObject(string $action, object $object): bool
    {
        return $object && $this->id($object) && $this->hasAccess($action, $object);
    }

    public function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        return $buttonList;
    }

    /**
     * Hook to run after initilization.
     */
    protected function configure(): void
    {
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        return $query;
    }

    /**
     * urlize the given word.
     *
     * @param string $word
     * @param string $sep  the separator
     *
     * @return string
     */
    final protected function urlize($word, $sep = '_')
    {
        return strtolower(preg_replace('/[^a-z0-9_]/i', $sep.'$1', $word));
    }

    final protected function getTemplateRegistry(): MutableTemplateRegistryInterface
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

    protected function configureFormFields(FormMapper $form): void
    {
    }

    protected function configureListFields(ListMapper $list): void
    {
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
    }

    protected function configureShowFields(ShowMapper $show): void
    {
    }

    protected function configureRoutes(RouteCollection $collection): void
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
     * @deprecated Use configureTabMenu instead
     */
    protected function configureSideMenu(ItemInterface $menu, $action, ?AdminInterface $childAdmin = null)
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
    protected function buildShow(): void
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
    protected function buildList(): void
    {
        if ($this->list) {
            return;
        }

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
            $fieldDescription->setTemplate($this->getTemplateRegistry()->getTemplate('batch'));

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
            $fieldDescription->setTemplate($this->getTemplateRegistry()->getTemplate('select'));

            $mapper->add($fieldDescription, 'select');
        }
    }

    /**
     * Build the form FieldDescription collection.
     */
    protected function buildForm(): void
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

            if (\is_array($value) || $value instanceof \ArrayAccess) {
                $value[] = $parent;
                $propertyAccessor->setValue($object, $propertyPath, $value);
            } else {
                $propertyAccessor->setValue($object, $propertyPath, $parent);
            }
        }

        $formBuilder = $this->getFormBuilder();
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
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
        throw new \RuntimeException(sprintf(
            'Unable to find the subclass `%s` for admin `%s`',
            $name,
            static::class
        ));
    }

    /**
     * Attach the inline validator to the model metadata, this must be done once per admin.
     */
    protected function attachInlineValidator(): void
    {
        $admin = $this;

        // add the custom inline validation option
        $metadata = $this->validator->getMetadataFor($this->getClass());

        $metadata->addConstraint(new InlineConstraint([
            'service' => $this,
            'method' => static function (ErrorElement $errorElement, $object) use ($admin): void {
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
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
     *
     * Predefine per page options.
     */
    protected function predefinePerPageOptions(): void
    {
        array_unshift($this->perPageOptions, $this->maxPerPage);
        $this->perPageOptions = array_unique($this->perPageOptions);
        sort($this->perPageOptions);
    }

    /**
     * Return list routes with permissions name.
     *
     * @return array<string, string>
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
            $access = array_merge($access, $extension->getAccessMapping($this));
        }

        return $access;
    }

    /**
     * Configures a list of default filters.
     */
    protected function configureDefaultFilterValues(array &$filterValues): void
    {
    }

    /**
     * Configures a list of default sort values.
     *
     * Example:
     *   $sortValues['_sort_by'] = 'foo'
     *   $sortValues['_sort_order'] = 'DESC'
     */
    protected function configureDefaultSortValues(array &$sortValues)
    {
    }

    /**
     * {@inheritdoc}
     */
    private function buildDatagrid(): void
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
