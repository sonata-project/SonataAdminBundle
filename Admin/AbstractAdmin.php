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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\Constraints\InlineConstraint;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorInterface as LegacyValidatorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class AbstractAdmin implements AdminInterface, DomainObjectInterface
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
    protected $listFieldDescriptions = array();

    /**
     * The show FieldDescription constructed from the configureShowFields method.
     *
     * @var array
     */
    protected $showFieldDescriptions = array();

    /**
     * The list FieldDescription constructed from the configureFormField method.
     *
     * @var array
     */
    protected $formFieldDescriptions = array();

    /**
     * The filter FieldDescription constructed from the configureFilterField method.
     *
     * @var array
     */
    protected $filterFieldDescriptions = array();

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
    protected $formOptions = array();

    /**
     * Default values to the datagrid.
     *
     * @var array
     */
    protected $datagridValues = array(
        '_page' => 1,
        '_per_page' => 32,
    );

    /**
     * Predefined per page options.
     *
     * @var array
     */
    protected $perPageOptions = array(16, 32, 64, 128, 192);

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
     * @var bool
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
     * @var object
     */
    protected $subject;

    /**
     * Define a Collection of child admin, ie /admin/order/{id}/order-element/{childId}.
     *
     * @var array
     */
    protected $children = array();

    /**
     * Reference the parent collection.
     *
     * @var AdminInterface|null
     */
    protected $parent = null;

    /**
     * The base code route refer to the prefix used to generate the route name.
     *
     * @var string
     */
    protected $baseCodeRoute = '';

    /**
     * The related parent association, ie if OrderElement has a parent property named order,
     * then the $parentAssociationMapping must be a string named `order`.
     *
     * @var string
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
     * @var \Symfony\Component\HttpFoundation\Request
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
     * @var \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    protected $datagrid;

    /**
     * The router instance.
     *
     * @var RouteGeneratorInterface
     */
    protected $routeGenerator;

    /**
     * The generated breadcrumbs.
     *
     * NEXT_MAJOR : remove this property
     *
     * @var array
     */
    protected $breadcrumbs = array();

    /**
     * @var SecurityHandlerInterface
     */
    protected $securityHandler = null;

    /**
     * @var ValidatorInterface|LegacyValidatorInterface
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
    protected $loaded = array(
        'view_fields' => false,
        'view_groups' => false,
        'routes' => false,
        'tab_menu' => false,
    );

    /**
     * @var array
     */
    protected $formTheme = array();

    /**
     * @var array
     */
    protected $filterTheme = array();

    /**
     * @var array
     */
    protected $templates = array();

    /**
     * @var AdminExtensionInterface[]
     */
    protected $extensions = array();

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
     * @var array [role] => array([permission], [permission])
     */
    protected $securityInformation = array();

    protected $cacheIsGranted = array();

    /**
     * Action list for the search result.
     *
     * @var string[]
     */
    protected $searchResultActions = array('edit', 'show');

    protected $listModes = array(
        'list' => array(
            'class' => 'fa fa-list fa-fw',
        ),
        'mosaic' => array(
            'class' => self::MOSAIC_ICON_CLASS,
        ),
    );

    /**
     * The Access mapping.
     *
     * @var array [action1 => requiredRole1, action2 => [requiredRole2, requiredRole3]]
     */
    protected $accessMapping = array();

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
    private $subClasses = array();

    /**
     * The list collection.
     *
     * @var array
     */
    private $list;

    /**
     * @var FieldDescriptionCollection
     */
    private $show;

    /**
     * @var Form
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
        return array(
            'json', 'xml', 'csv', 'xls',
        );
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

    /**
     * {@inheritdoc}
     */
    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $fields = array();

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

    /**
     * {@inheritdoc}
     */
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

        $this->baseCodeRoute = $this->getCode();

        $this->configure();
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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
     * {@inheritdoc}
     */
    public function preValidate($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterParameters()
    {
        $parameters = array();

        // build the values array
        if ($this->hasRequest()) {
            $filters = $this->request->query->get('filter', array());

            // if persisting filters, save filters to session, or pull them out of session if no new filters set
            if ($this->persistFilters) {
                if ($filters == array() && $this->request->query->get('filters') != 'reset') {
                    $filters = $this->request->getSession()->get($this->getCode().'.filter.parameters', array());
                } else {
                    $this->request->getSession()->set($this->getCode().'.filter.parameters', $filters);
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
                $parameters[$name] = array('value' => $this->request->get($this->getParent()->getIdParameter()));
            }
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatagrid()
    {
        if ($this->datagrid) {
            return;
        }

        $filterParameters = $this->getFilterParameters();

        // transform _sort_by from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($filterParameters['_sort_by']) && is_string($filterParameters['_sort_by'])) {
            if ($this->hasListFieldDescription($filterParameters['_sort_by'])) {
                $filterParameters['_sort_by'] = $this->getListFieldDescription($filterParameters['_sort_by']);
            } else {
                $filterParameters['_sort_by'] = $this->getModelManager()->getNewFieldDescriptionInstance(
                    $this->getClass(),
                    $filterParameters['_sort_by'],
                    array()
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
            // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
            $modelHiddenType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Sonata\AdminBundle\Form\Type\ModelHiddenType'
                : 'sonata_type_model_hidden';

            // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
            $hiddenType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Symfony\Component\Form\Extension\Core\Type\HiddenType'
                : 'hidden';

            $mapper->add($this->getParentAssociationMapping(), null, array(
                'show_filter' => false,
                'label' => false,
                'field_type' => $modelHiddenType,
                'field_options' => array(
                    'model_manager' => $this->getModelManager(),
                ),
                'operator_type' => $hiddenType,
            ), null, null, array(
                'admin_code' => $this->getParent()->getCode(),
            ));
        }

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDatagridFilters($mapper);
        }
    }

    /**
     * Returns the name of the parent related field, so the field can be use to set the default
     * value (ie the parent object) or to filter the object.
     *
     * @return string the name of the parent related field
     */
    public function getParentAssociationMapping()
    {
        return $this->parentAssociationMapping;
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
            if (!$this->baseRoutePattern) {
                preg_match(self::CLASS_REGEX, $this->class, $matches);

                if (!$matches) {
                    throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', get_class($this)));
                }
            }

            $this->cachedBaseRoutePattern = sprintf('%s/{id}/%s',
                $this->getParent()->getBaseRoutePattern(),
                $this->baseRoutePattern ?: $this->urlize($matches[5], '-')
            );
        } elseif ($this->baseRoutePattern) {
            $this->cachedBaseRoutePattern = $this->baseRoutePattern;
        } else {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', get_class($this)));
            }

            $this->cachedBaseRoutePattern = sprintf('/%s%s/%s',
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
            if (!$this->baseRouteName) {
                preg_match(self::CLASS_REGEX, $this->class, $matches);

                if (!$matches) {
                    throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', get_class($this)));
                }
            }

            $this->cachedBaseRouteName = sprintf('%s_%s',
                $this->getParent()->getBaseRouteName(),
                $this->baseRouteName ?: $this->urlize($matches[5])
            );
        } elseif ($this->baseRouteName) {
            $this->cachedBaseRouteName = $this->baseRouteName;
        } else {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', get_class($this)));
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

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        // see https://github.com/sonata-project/SonataCoreBundle/commit/247eeb0a7ca7211142e101754769d70bc402a5b4
        if ($this->hasSubject() && is_object($this->getSubject())) {
            return ClassUtils::getClass($this->getSubject());
        }

        if (!$this->hasActiveSubClass()) {
            if (count($this->getSubClasses()) > 0) {
                $subject = $this->getSubject();

                if ($subject && is_object($subject)) {
                    return ClassUtils::getClass($subject);
                }
            }

            return $this->class;
        }

        if ($this->getParentFieldDescription() && $this->hasActiveSubClass()) {
            throw new \RuntimeException('Feature not implemented: an embedded admin cannot have subclass');
        }

        $subClass = $this->getRequest()->query->get('subclass');

        return $this->getSubClass($subClass);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubClasses()
    {
        return $this->subClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function addSubClass($subClass)
    {
        if (!in_array($subClass, $this->subClasses)) {
            $this->subClasses[] = $subClass;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setSubClasses(array $subClasses)
    {
        $this->subClasses = $subClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSubClass($name)
    {
        return isset($this->subClasses[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasActiveSubClass()
    {
        if (count($this->subClasses) > 0 && $this->request) {
            return null !== $this->getRequest()->query->get('subclass');
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveSubClass()
    {
        if (!$this->hasActiveSubClass()) {
            return;
        }

        return $this->getClass();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = array();

        if ($this->hasRoute('delete') && $this->hasAccess('delete')) {
            $actions['delete'] = array(
                'label' => 'action_delete',
                'translation_domain' => 'SonataAdminBundle',
                'ask_confirmation' => true, // by default always true
            );
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

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        $this->buildRoutes();

        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouterIdParameter()
    {
        return $this->isChild() ? '{childId}' : '{id}';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdParameter()
    {
        return $this->isChild() ? 'childId' : 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function hasRoute($name)
    {
        if (!$this->routeGenerator) {
            throw new \RuntimeException('RouteGenerator cannot be null');
        }

        return $this->routeGenerator->hasAdminRoute($this, $name);
    }

    /**
     * {@inheritdoc}
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

        return ($admin->getBaseRouteName().'_'.$name) == $route;
    }

    /**
     * {@inheritdoc}
     */
    public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $parameters['id'] = $this->getUrlsafeIdentifier($object);

        return $this->generateUrl($name, $parameters, $absolute);
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrl($name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->routeGenerator->generateUrl($this, $name, $parameters, $absolute);
    }

    /**
     * {@inheritdoc}
     */
    public function generateMenuUrl($name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->routeGenerator->generateMenuUrl($this, $name, $parameters, $absolute);
    }

    /**
     * @param array $templates
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @param string $name
     * @param string $template
     */
    public function setTemplate($name, $template)
    {
        $this->templates[$name] = $template;
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $object = $this->getModelManager()->getModelInstance($this->getClass());
        foreach ($this->getExtensions() as $extension) {
            $extension->alterNewInstance($this, $object);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
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
     *
     * @param FormBuilderInterface $formBuilder
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

    /**
     * {@inheritdoc}
     */
    public function attachAdminClass(FieldDescriptionInterface $fieldDescription)
    {
        $pool = $this->getConfigurationPool();

        $adminCode = $fieldDescription->getOption('admin_code');

        if ($adminCode !== null) {
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

    /**
     * {@inheritdoc}
     */
    public function getObject($id)
    {
        $object = $this->getModelManager()->find($this->getClass(), $id);
        foreach ($this->getExtensions() as $extension) {
            $extension->alterObject($this, $object);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        $this->buildForm();

        return $this->form;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $this->buildList();

        return $this->list;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        if (func_num_args() > 0) {
            @trigger_error(
                'The $context argument of '.__METHOD__.' is deprecated since 3.3, to be removed in 4.0.',
                E_USER_DEPRECATED
            );
        }
        $query = $this->getModelManager()->createQuery($this->class);

        foreach ($this->extensions as $extension) {
            $extension->configureQuery($this, $query, $context);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagrid()
    {
        $this->buildDatagrid();

        return $this->datagrid;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function buildSideMenu($action, AdminInterface $childAdmin = null)
    {
        return $this->buildTabMenu($action, $childAdmin);
    }

    /**
     * @param string         $action
     * @param AdminInterface $childAdmin
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

    /**
     * {@inheritdoc}
     */
    public function setBaseControllerName($baseControllerName)
    {
        $this->baseControllerName = $baseControllerName;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param bool $persist
     */
    public function setPersistFilters($persist)
    {
        $this->persistFilters = $persist;
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

    /**
     * {@inheritdoc}
     */
    public function getFormGroups()
    {
        return $this->formGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormGroups(array $formGroups)
    {
        $this->formGroups = $formGroups;
    }

    /**
     * {@inheritdoc}
     */
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
     * @param array $keys
     */
    public function reorderFormGroup($group, array $keys)
    {
        $formGroups = $this->getFormGroups();
        $formGroups[$group]['fields'] = array_merge(array_flip($keys), $formGroups[$group]['fields']);
        $this->setFormGroups($formGroups);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTabs()
    {
        return $this->formTabs;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormTabs(array $formTabs)
    {
        $this->formTabs = $formTabs;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowTabs()
    {
        return $this->showTabs;
    }

    /**
     * {@inheritdoc}
     */
    public function setShowTabs(array $showTabs)
    {
        $this->showTabs = $showTabs;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowGroups()
    {
        return $this->showGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function setShowGroups(array $showGroups)
    {
        $this->showGroups = $showGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function reorderShowGroup($group, array $keys)
    {
        $showGroups = $this->getShowGroups();
        $showGroups[$group]['fields'] = array_merge(array_flip($keys), $showGroups[$group]['fields']);
        $this->setShowGroups($showGroups);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentFieldDescription(FieldDescriptionInterface $parentFieldDescription)
    {
        $this->parentFieldDescription = $parentFieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentFieldDescription()
    {
        return $this->parentFieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParentFieldDescription()
    {
        return $this->parentFieldDescription instanceof FieldDescriptionInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        if (is_object($subject) && !is_a($subject, $this->class, true)) {
            $message = <<<'EOT'
You are trying to set entity an instance of "%s",
which is not the one registered with this admin class ("%s").
This is deprecated since 3.5 and will no longer be supported in 4.0.
EOT;

            @trigger_error(
                sprintf($message, get_class($subject), $this->class),
                E_USER_DEPRECATED
            ); // NEXT_MAJOR : throw an exception instead
        }

        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        if ($this->subject === null && $this->request) {
            $id = $this->request->get($this->getIdParameter());
            $this->subject = $this->getModelManager()->find($this->class, $id);
        }

        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSubject()
    {
        return $this->subject != null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormFieldDescriptions()
    {
        $this->buildForm();

        return $this->formFieldDescriptions;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function hasShowFieldDescription($name)
    {
        return array_key_exists($name, $this->showFieldDescriptions);
    }

    /**
     * {@inheritdoc}
     */
    public function addShowFieldDescription($name, FieldDescriptionInterface $fieldDescription)
    {
        $this->showFieldDescriptions[$name] = $fieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function removeShowFieldDescription($name)
    {
        unset($this->showFieldDescriptions[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getListFieldDescriptions()
    {
        $this->buildList();

        return $this->listFieldDescriptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getListFieldDescription($name)
    {
        return $this->hasListFieldDescription($name) ? $this->listFieldDescriptions[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasListFieldDescription($name)
    {
        $this->buildList();

        return array_key_exists($name, $this->listFieldDescriptions) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function addListFieldDescription($name, FieldDescriptionInterface $fieldDescription)
    {
        $this->listFieldDescriptions[$name] = $fieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function removeListFieldDescription($name)
    {
        unset($this->listFieldDescriptions[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFieldDescription($name)
    {
        return $this->hasFilterFieldDescription($name) ? $this->filterFieldDescriptions[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFilterFieldDescription($name)
    {
        return array_key_exists($name, $this->filterFieldDescriptions) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterFieldDescription($name, FieldDescriptionInterface $fieldDescription)
    {
        $this->filterFieldDescriptions[$name] = $fieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFilterFieldDescription($name)
    {
        unset($this->filterFieldDescriptions[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFieldDescriptions()
    {
        $this->buildDatagrid();

        return $this->filterFieldDescriptions;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(AdminInterface $child)
    {
        $this->children[$child->getCode()] = $child;

        $child->setBaseCodeRoute($this->getCode().'|'.$child->getCode());
        $child->setParent($this);
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild($code)
    {
        return isset($this->children[$code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function getChild($code)
    {
        return $this->hasChild($code) ? $this->children[$code] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(AdminInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
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
        return count($this->children) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setUniqid($uniqid)
    {
        $this->uniqid = $uniqid;
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqid()
    {
        if (!$this->uniqid) {
            $this->uniqid = 's'.uniqid();
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

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = array();

        foreach ($this->getExtensions() as $extension) {
            $params = $extension->getPersistentParameters($this);

            if (!is_array($params)) {
                throw new \RuntimeException(sprintf('The %s::getPersistentParameters must return an array', get_class($extension)));
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

    /**
     * {@inheritdoc}
     */
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
     * @param string             $action
     * @param ItemInterface|null $menu
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
        if ($this->breadcrumbsBuilder === null) {
            $this->breadcrumbsBuilder = new BreadcrumbsBuilder(
                $this->getConfigurationPool()->getContainer()->getParameter('sonata.admin.configuration.breadcrumbs')
            );
        }

        return $this->breadcrumbsBuilder;
    }

    /**
     * NEXT_MAJOR : remove this method.
     *
     * @param BreadcrumbsBuilderInterface
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

    /**
     * {@inheritdoc}
     */
    public function setCurrentChild($currentChild)
    {
        $this->currentChild = $currentChild;
    }

    /**
     * {@inheritdoc}
     */
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

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
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
     * @param array       $parameters
     * @param string|null $domain
     * @param string|null $locale
     *
     * @return string the translated string
     *
     * @deprecated since 3.9, to be removed with 4.0
     */
    public function transChoice($id, $count, array $parameters = array(), $domain = null, $locale = null)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.9 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $domain = $domain ?: $this->getTranslationDomain();

        return $this->translator->transChoice($id, $count, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
    }

    /**
     * {@inheritdoc}
     */
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
        $args = func_get_args();
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

    /**
     * {@inheritdoc}
     */
    public function getTranslationLabel($label, $context = '', $type = '')
    {
        return $this->getLabelTranslatorStrategy()->getLabel($label, $context, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        foreach ($this->getChildren() as $children) {
            $children->setRequest($request);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        if (!$this->request) {
            throw new \RuntimeException('The Request object has not been set');
        }

        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRequest()
    {
        return $this->request !== null;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder)
    {
        $this->datagridBuilder = $datagridBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagridBuilder()
    {
        return $this->datagridBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function setListBuilder(ListBuilderInterface $listBuilder)
    {
        $this->listBuilder = $listBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getListBuilder()
    {
        return $this->listBuilder;
    }

    /**
     * @param ShowBuilderInterface $showBuilder
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $baseCodeRoute
     */
    public function setBaseCodeRoute($baseCodeRoute)
    {
        $this->baseCodeRoute = $baseCodeRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCodeRoute()
    {
        return $this->baseCodeRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelManager()
    {
        return $this->modelManager;
    }

    /**
     * @param ModelManagerInterface $modelManager
     */
    public function setModelManager(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        return $this->getCode();
    }

    /**
     * Set the roles and permissions per role.
     *
     * @param array $information
     */
    public function setSecurityInformation(array $information)
    {
        $this->securityInformation = $information;
    }

    /**
     * {@inheritdoc}
     */
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
                return array('LIST');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function showIn($context)
    {
        switch ($context) {
            case self::CONTEXT_DASHBOARD:
            case self::CONTEXT_MENU:
            default:
                return $this->isGranted($this->getPermissionsShow($context));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectSecurity($object)
    {
        $this->getSecurityHandler()->createObjectSecurity($this, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function setSecurityHandler(SecurityHandlerInterface $securityHandler)
    {
        $this->securityHandler = $securityHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityHandler()
    {
        return $this->securityHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($name, $object = null)
    {
        $key = md5(json_encode($name).($object ? '/'.spl_object_hash($object) : ''));

        if (!array_key_exists($key, $this->cacheIsGranted)) {
            $this->cacheIsGranted[$key] = $this->securityHandler->isGranted($this, $name, $object ?: $this);
        }

        return $this->cacheIsGranted[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlsafeIdentifier($entity)
    {
        return $this->getModelManager()->getUrlsafeIdentifier($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getNormalizedIdentifier($entity)
    {
        return $this->getModelManager()->getNormalizedIdentifier($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function id($entity)
    {
        return $this->getNormalizedIdentifier($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function setValidator($validator)
    {
        // TODO: Remove it when bumping requirements to SF 2.5+
        if (!$validator instanceof ValidatorInterface && !$validator instanceof LegacyValidatorInterface) {
            throw new \InvalidArgumentException(
                'Argument 1 must be an instance of Symfony\Component\Validator\Validator\ValidatorInterface'
                .' or Symfony\Component\Validator\ValidatorInterface'
            );
        }

        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getShow()
    {
        $this->buildShow();

        return $this->show;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormTheme(array $formTheme)
    {
        $this->formTheme = $formTheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTheme()
    {
        return $this->formTheme;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilterTheme(array $filterTheme)
    {
        $this->filterTheme = $filterTheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterTheme()
    {
        return $this->filterTheme;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtension(AdminExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function setMenuFactory(MenuFactoryInterface $menuFactory)
    {
        $this->menuFactory = $menuFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuFactory()
    {
        return $this->menuFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteBuilder(RouteBuilderInterface $routeBuilder)
    {
        $this->routeBuilder = $routeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteBuilder()
    {
        return $this->routeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($object)
    {
        if (!is_object($object)) {
            return '';
        }

        if (method_exists($object, '__toString') && null !== $object->__toString()) {
            return (string) $object;
        }

        return sprintf('%s:%s', ClassUtils::getClass($object), spl_object_hash($object));
    }

    /**
     * {@inheritdoc}
     */
    public function setLabelTranslatorStrategy(LabelTranslatorStrategyInterface $labelTranslatorStrategy)
    {
        $this->labelTranslatorStrategy = $labelTranslatorStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelTranslatorStrategy()
    {
        return $this->labelTranslatorStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsPreviewMode()
    {
        return $this->supportsPreviewMode;
    }

    /**
     * Set custom per page options.
     *
     * @param array $options
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
        return in_array($perPage, $this->perPageOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function isAclEnabled()
    {
        return $this->getSecurityHandler() instanceof AclSecurityHandlerInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectMetadata($object)
    {
        return new Metadata($this->toString($object));
    }

    /**
     * {@inheritdoc}
     */
    public function getListModes()
    {
        return $this->listModes;
    }

    /**
     * {@inheritdoc}
     */
    public function setListMode($mode)
    {
        if (!$this->hasRequest()) {
            throw new \RuntimeException(sprintf('No request attached to the current admin: %s', $this->getCode()));
        }

        $this->getRequest()->getSession()->set(sprintf('%s.list_mode', $this->getCode()), $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function getListMode()
    {
        if (!$this->hasRequest()) {
            return 'list';
        }

        return $this->getRequest()->getSession()->get(sprintf('%s.list_mode', $this->getCode()), 'list');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessMapping()
    {
        return $this->accessMapping;
    }

    /**
     * {@inheritdoc}
     */
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

        if (!is_array($access[$action])) {
            $access[$action] = array($access[$action]);
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

        if (!is_array($access[$action])) {
            $access[$action] = array($access[$action]);
        }

        foreach ($access[$action] as $role) {
            if (false === $this->isGranted($role, $object)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function configureActionButtons($action, $object = null)
    {
        $list = array();

        if (in_array($action, array('tree', 'show', 'edit', 'delete', 'list', 'batch'))
            && $this->hasAccess('create')
            && $this->hasRoute('create')
        ) {
            $list['create'] = array(
                'template' => $this->getTemplate('button_create'),
            );
        }

        if (in_array($action, array('show', 'delete', 'acl', 'history'))
            && $this->canAccessObject('edit', $object)
            && $this->hasRoute('edit')
        ) {
            $list['edit'] = array(
                'template' => $this->getTemplate('button_edit'),
            );
        }

        if (in_array($action, array('show', 'edit', 'acl'))
            && $this->canAccessObject('history', $object)
            && $this->hasRoute('history')
        ) {
            $list['history'] = array(
                'template' => $this->getTemplate('button_history'),
            );
        }

        if (in_array($action, array('edit', 'history'))
            && $this->isAclEnabled()
            && $this->canAccessObject('acl', $object)
            && $this->hasRoute('acl')
        ) {
            $list['acl'] = array(
                'template' => $this->getTemplate('button_acl'),
            );
        }

        if (in_array($action, array('edit', 'history', 'acl'))
            && $this->canAccessObject('show', $object)
            && count($this->getShow()) > 0
            && $this->hasRoute('show')
        ) {
            $list['show'] = array(
                'template' => $this->getTemplate('button_show'),
            );
        }

        if (in_array($action, array('show', 'edit', 'delete', 'acl', 'batch'))
            && $this->hasAccess('list')
            && $this->hasRoute('list')
        ) {
            $list['list'] = array(
                'template' => $this->getTemplate('button_list'),
            );
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
        $actions = array();

        if ($this->hasRoute('create') && $this->hasAccess('create')) {
            $actions['create'] = array(
                'label' => 'link_add',
                'translation_domain' => 'SonataAdminBundle',
                'template' => $this->getTemplate('action_create'),
                'url' => $this->generateUrl('create'),
                'icon' => 'plus-circle',
            );
        }

        if ($this->hasRoute('list') && $this->hasAccess('list')) {
            $actions['list'] = array(
                'label' => 'link_list',
                'translation_domain' => 'SonataAdminBundle',
                'url' => $this->generateUrl('list'),
                'icon' => 'list',
            );
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
            $this->listModes['mosaic'] = array('class' => self::MOSAIC_ICON_CLASS);
        } else {
            unset($this->listModes['mosaic']);
        }
    }

    /**
     * @param FormMapper $form
     */
    final public function getSearchResultLink($object)
    {
        foreach ($this->searchResultActions as $action) {
            if ($this->hasRoute($action) && $this->hasAccess($action, $object)) {
                return $this->generateObjectUrl($action, $object);
            }
        }

        return;
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
     * Returns a list of default filters.
     *
     * @return array
     */
    final protected function getDefaultFilterValues()
    {
        $defaultFilterValues = array();

        $this->configureDefaultFilterValues($defaultFilterValues);

        foreach ($this->getExtensions() as $extension) {
            // NEXT_MAJOR: remove method check in next major release
            if (method_exists($extension, 'configureDefaultFilterValues')) {
                $extension->configureDefaultFilterValues($this, $defaultFilterValues);
            }
        }

        return $defaultFilterValues;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list)
    {
    }

    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
    }

    /**
     * @param ShowMapper $show
     */
    protected function configureShowFields(ShowMapper $show)
    {
    }

    /**
     * @param RouteCollection $collection
     */
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
     * @param MenuItemInterface $menu
     * @param                   $action
     * @param AdminInterface    $childAdmin
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
     * @param MenuItemInterface $menu
     * @param string            $action
     * @param AdminInterface    $childAdmin
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

        if (count($this->getBatchActions()) > 0) {
            $fieldDescription = $this->getModelManager()->getNewFieldDescriptionInstance(
                $this->getClass(),
                'batch',
                array(
                    'label' => 'batch',
                    'code' => '_batch',
                    'sortable' => false,
                    'virtual_field' => true,
                )
            );

            $fieldDescription->setAdmin($this);
            $fieldDescription->setTemplate($this->getTemplate('batch'));

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
                array(
                    'label' => false,
                    'code' => '_select',
                    'sortable' => false,
                    'virtual_field' => false,
                )
            );

            $fieldDescription->setAdmin($this);
            $fieldDescription->setTemplate($this->getTemplate('select'));

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

            if (is_array($value) || ($value instanceof \Traversable && $value instanceof \ArrayAccess)) {
                $value[] = $parent;
                $propertyAccessor->setValue($object, $propertyPath, $value);
            } else {
                $propertyAccessor->setValue($object, $propertyPath, $parent);
            }
        }

        $this->form = $this->getFormBuilder()->getForm();
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
            get_class($this)
        ));
    }

    /**
     * Attach the inline validator to the model metadata, this must be done once per admin.
     */
    protected function attachInlineValidator()
    {
        $admin = $this;

        // add the custom inline validation option
        // TODO: Remove conditional method when bumping requirements to SF 2.5+
        if (method_exists($this->validator, 'getMetadataFor')) {
            $metadata = $this->validator->getMetadataFor($this->getClass());
        } else {
            $metadata = $this->validator->getMetadataFactory()->getMetadataFor($this->getClass());
        }

        $metadata->addConstraint(new InlineConstraint(array(
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
        )));
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
        $access = array_merge(array(
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
        ), $this->getAccessMapping());

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
     *
     * @param array $filterValues
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
