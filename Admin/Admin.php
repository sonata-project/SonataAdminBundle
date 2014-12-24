<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RoutesCache;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Validator\Constraints\InlineConstraint;

use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;

use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Model\ModelManagerInterface;

use Knp\Menu\FactoryInterface as MenuFactoryInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;

use Doctrine\Common\Util\ClassUtils;

abstract class Admin implements AdminInterface, DomainObjectInterface
{
    const CONTEXT_MENU       = 'menu';
    const CONTEXT_DASHBOARD  = 'dashboard';

    const CLASS_REGEX        = '@([A-Za-z0-9]*)\\\(Bundle\\\)?([A-Za-z0-9]+)Bundle\\\(Entity|Document|Model|PHPCR|CouchDocument|Phpcr|Doctrine\\\Orm|Doctrine\\\Phpcr|Doctrine\\\MongoDB|Doctrine\\\CouchDB)\\\(.*)@';

    /**
     * The class name managed by the admin class
     *
     * @var string
     */
    private $class;

    /**
     * The subclasses supported by the admin class
     *
     * @var array
     */
    private $subClasses = array();

    /**
     * The list collection
     *
     * @var array
     */
    private $list;

    /**
     * The list FieldDescription constructed from the configureListField method
     *
     * @var array
     */
    protected $listFieldDescriptions = array();

    private $show;

    /**
     * The show FieldDescription constructed from the configureShowFields method
     *
     * @var array
     */
    protected $showFieldDescriptions = array();

    /**
     * @var Form
     */
    private $form;

    /**
     * The list FieldDescription constructed from the configureFormField method
     *
     * @var array
     */
    protected $formFieldDescriptions = array();

    /**
     * @var \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    private $filter;

    /**
     * The filter FieldDescription constructed from the configureFilterField method
     *
     * @var array
     */
    protected $filterFieldDescriptions = array();

    /**
     * The number of result to display in the list
     *
     * @var integer
     */
    protected $maxPerPage = 25;

    /**
     * The maximum number of page numbers to display in the list
     *
     * @var integer
     */
    protected $maxPageLinks = 25;

    /**
     * The base route name used to generate the routing information
     *
     * @var string
     */
    protected $baseRouteName;

    /**
     * The base route pattern used to generate the routing information
     *
     * @var string
     */
    protected $baseRoutePattern;

    /**
     * The base name controller used to generate the routing information
     *
     * @var string
     */
    protected $baseControllerName;

    /**
     * The form group disposition
     *
     * @var array|boolean
     */
    private $formGroups = false;

    /**
     * The form tabs disposition
     *
     * @var array|boolean
     */
    private $formTabs = false;

    /**
     * The view group disposition
     *
     * @var array|boolean
     */
    private $showGroups = false;

    /**
     * The view tab disposition
     *
     * @var array|boolean
     */
    private $showTabs = false;

    /**
     * The label class name  (used in the title/breadcrumb ...)
     *
     * @var string
     */
    protected $classnameLabel;

    /**
     * The translation domain to be used to translate messages
     *
     * @var string
     */
    protected $translationDomain = 'messages';

    /**
     * Options to set to the form (ie, validation_groups)
     *
     * @var array
     */
    protected $formOptions = array();

    /**
     * Default values to the datagrid
     *
     * @var array
     */
    protected $datagridValues = array(
        '_page'       => 1,
        '_per_page'   => 25,
    );

    /**
     * Predefined per page options
     *
     * @var array
     */
    protected $perPageOptions = array(15, 25, 50, 100, 150, 200);

    /**
     * The code related to the admin
     *
     * @var string
     */
    protected $code;

    /**
     * The label
     *
     * @var string
     */
    protected $label;

    /**
     * Whether or not to persist the filters in the session
     *
     * @var boolean
     */
    protected $persistFilters = false;

    /**
     * Array of routes related to this admin
     *
     * @var \Sonata\AdminBundle\Route\RouteCollection
     */
    protected $routes;

    /**
     * The subject only set in edit/update/create mode
     *
     * @var object
     */
    protected $subject;

    /**
     * Define a Collection of child admin, ie /admin/order/{id}/order-element/{childId}
     *
     * @var array
     */
    protected $children = array();

    /**
     * Reference the parent collection
     *
     * @var Admin
     */
    protected $parent = null;

    /**
     * The base code route refer to the prefix used to generate the route name
     *
     * @var string
     */
    protected $baseCodeRoute = '';

    /**
     * The related field reflection, ie if OrderElement is linked to Order,
     * then the $parentReflectionProperty must be the ReflectionProperty of
     * the order (OrderElement::$order)
     *
     * @var \ReflectionProperty $parentReflectionProperty
     */
    protected $parentAssociationMapping = null;

    /**
     * Reference the parent FieldDescription related to this admin
     * only set for FieldDescription which is associated to an Sub Admin instance
     *
     * @var FieldDescriptionInterface
     */
    protected $parentFieldDescription;

    /**
     * If true then the current admin is part of the nested admin set (from the url)
     *
     * @var boolean
     */
    protected $currentChild = false;

    /**
     * The uniqid is used to avoid clashing with 2 admin related to the code
     * ie: a Block linked to a Block
     *
     * @var string
     */
    protected $uniqid;

    /**
     * The Entity or Document manager
     *
     * @var \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    protected $modelManager;

    /**
     * The manager type to use for the admin
     *
     * @var string
     */
    private $managerType;

    /**
     * The current request object
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * The translator component
     *
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * The related form contractor
     *
     * @var \Sonata\AdminBundle\Builder\FormContractorInterface
     */
    protected $formContractor;

    /**
     * The related list builder
     *
     * @var \Sonata\AdminBundle\Builder\ListBuilderInterface
     */
    protected $listBuilder;

    /**
     * The related view builder
     *
     * @var ShowBuilderInterface
     */
    protected $showBuilder;

    /**
     * The related datagrid builder
     *
     * @var \Sonata\AdminBundle\Builder\DatagridBuilderInterface
     */
    protected $datagridBuilder;

    /**
     * @var \Sonata\AdminBundle\Builder\RouteBuilderInterface
     */
    protected $routeBuilder;

    /**
     * The datagrid instance
     *
     * @var \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    protected $datagrid;

    /**
     * The router instance
     *
     * @var RouteGeneratorInterface
     */
    protected $routeGenerator;

    /**
     * The generated breadcrumbs
     *
     * @var array
     */
    protected $breadcrumbs = array();

    /**
     * @var SecurityHandlerInterface
     */
    protected $securityHandler = null;

    /**
     * @var ValidatorInterface $validator
     */
    protected $validator = null;

    /**
     * The configuration pool
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
        'view_fields'   => false,
        'view_groups'   => false,
        'routes'        => false,
        'tab_menu'      => false,
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
    protected $templates  = array();

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
     * edit/create forms
     *
     * @var boolean
     */
    protected $supportsPreviewMode = false;

    /**
     * Roles and permissions per role
     *
     * @var array [role] => array([permission], [permission])
     */
    protected $securityInformation = array();

    protected $cacheIsGranted = array();

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list)
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $filter)
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {

    }

    /**
     * DEPRECATED: Use configureTabMenu instead
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
     * Configures the tab menu in your admin
     *
     * @param MenuItemInterface $menu
     * @param                   $action
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
     * {@inheritdoc}
     */
    public function getExportFormats()
    {
        return array(
            'json', 'xml', 'csv', 'xls'
        );
    }

    /**
     * @return array
     */
    public function getExportFields()
    {
        return $this->getModelManager()->getExportFields($this->getClass());
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        return $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {

    }

    /**
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName)
    {
        $this->code                 = $code;
        $this->class                = $class;
        $this->baseControllerName   = $baseControllerName;

        $this->predefinePerPageOptions();
        $this->datagridValues['_per_page'] = $this->maxPerPage;
    }

    /**
     * define custom variable
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
    public function preUpdate($object)
    {}

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {}

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {}

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {}

    /**
     * {@inheritdoc}
     */
    public function preRemove($object)
    {}

    /**
     * {@inheritdoc}
     */
    public function postRemove($object)
    {}

    /**
     * {@inheritdoc}
     */
    public function preBatchAction($actionName, ProxyQueryInterface $query, array & $idx, $allElements)
    {
    }

    /**
     * build the view FieldDescription array
     *
     * @return void
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
     * build the list FieldDescription array
     *
     * @return void
     */
    protected function buildList()
    {
        if ($this->list) {
            return;
        }

        $this->list = $this->getListBuilder()->getBaseList();

        $mapper = new ListMapper($this->getListBuilder(), $this->list, $this);

        if (count($this->getBatchActions()) > 0) {
            $fieldDescription = $this->getModelManager()->getNewFieldDescriptionInstance($this->getClass(), 'batch', array(
                'label'    => 'batch',
                'code'     => '_batch',
                'sortable' => false
            ));

            $fieldDescription->setAdmin($this);
            $fieldDescription->setTemplate($this->getTemplate('batch'));

            $mapper->add($fieldDescription, 'batch');
        }

        $this->configureListFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureListFields($mapper);
        }

        if ($this->hasRequest() && $this->getRequest()->isXmlHttpRequest()) {
            $fieldDescription = $this->getModelManager()->getNewFieldDescriptionInstance($this->getClass(), 'select', array(
                'label'    => false,
                'code'     => '_select',
                'sortable' => false,
            ));

            $fieldDescription->setAdmin($this);
            $fieldDescription->setTemplate($this->getTemplate('select'));

            $mapper->add($fieldDescription, 'select');
        }
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
            $mapper->add($this->getParentAssociationMapping(), null, array(
                'label' => false,
                'field_type' => 'sonata_type_model_hidden',
                'field_options' => array(
                    'model_manager' => $this->getModelManager()
                ),
                'operator_type' => 'hidden'
            ));
        }

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDatagridFilters($mapper);
        }
    }

    /**
     * Returns the name of the parent related field, so the field can be use to set the default
     * value (ie the parent object) or to filter the object
     *
     * @return string the name of the parent related field
     */
    public function getParentAssociationMapping()
    {
        return $this->parentAssociationMapping;
    }

    /**
     * Build the form FieldDescription collection
     *
     * @return void
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

            $propertyAccessor = PropertyAccess::createPropertyAccessor();
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
     * Returns the baseRoutePattern used to generate the routing information
     *
     * @throws \RuntimeException
     *
     * @return string the baseRoutePattern used to generate the routing information
     */
    public function getBaseRoutePattern()
    {
        if (!$this->baseRoutePattern) {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', get_class($this)));
            }

            if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
                $this->baseRoutePattern = sprintf('%s/{id}/%s',
                    $this->getParent()->getBaseRoutePattern(),
                    $this->urlize($matches[5], '-')
                );
            } else {

                $this->baseRoutePattern = sprintf('/%s/%s/%s',
                    $this->urlize($matches[1], '-'),
                    $this->urlize($matches[3], '-'),
                    $this->urlize($matches[5], '-')
                );
            }
        }

        return $this->baseRoutePattern;
    }

    /**
     * Returns the baseRouteName used to generate the routing information
     *
     * @throws \RuntimeException
     *
     * @return string the baseRouteName used to generate the routing information
     */
    public function getBaseRouteName()
    {
        if (!$this->baseRouteName) {
            preg_match(self::CLASS_REGEX, $this->class, $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', get_class($this)));
            }

            if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
                $this->baseRouteName = sprintf('%s_%s',
                    $this->getParent()->getBaseRouteName(),
                    $this->urlize($matches[5])
                );
            } else {

                $this->baseRouteName = sprintf('admin_%s_%s_%s',
                    $this->urlize($matches[1]),
                    $this->urlize($matches[3]),
                    $this->urlize($matches[5])
                );
            }
        }

        return $this->baseRouteName;
    }

    /**
     * urlize the given word
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
    public function setSubClasses(array $subClasses)
    {
        $this->subClasses = $subClasses;
    }

    /**
     * Gets the subclass corresponding to the given name
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

        throw new \RuntimeException(sprintf('Unable to find the subclass `%s` for admin `%s`', $name, get_class($this)));
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
            return null;
        }

        return $this->getClass();
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveSubclassCode()
    {
        if (!$this->hasActiveSubClass()) {
            return null;
        }

        $subClass = $this->getRequest()->query->get('subclass');

        if (!$this->hasSubClass($subClass)) {
            return null;
        }

        return $subClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = array();

        if ($this->hasRoute('delete') && $this->isGranted('DELETE')) {
            $actions['delete'] = array(
                'label'            => $this->trans('action_delete', array(), 'SonataAdminBundle'),
                'ask_confirmation' => true, // by default always true
            );
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
     * Build all the related urls to the current admin
     *
     * @return void
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

    /**
     * @param string $name
     *
     * @return bool
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
    public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = false)
    {
        $parameters['id'] = $this->getUrlsafeIdentifier($object);

        return $this->generateUrl($name, $parameters, $absolute);
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrl($name, array $parameters = array(), $absolute = false)
    {
        return $this->routeGenerator->generateUrl($this, $name, $parameters, $absolute);
    }

    /**
     * {@inheritdoc}
     */
    public function generateMenuUrl($name, array $parameters = array(), $absolute = false)
    {
        return $this->routeGenerator->generateMenuUrl($this, $name,$parameters, $absolute);
    }

    /**
     * @param array $templates
     *
     * @return void
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @param string $name
     * @param string $template
     *
     * @return void
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

        return null;
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
     * the getFormBuilder is only call by the main admin class
     *
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     *
     * @return void
     */
    public function defineFormBuilder(FormBuilder $formBuilder)
    {
        $mapper = new FormMapper($this->getFormContractor(), $formBuilder, $this);

        $this->configureFormFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureFormFields($mapper);
        }

        $this->attachInlineValidator();
    }

    /**
     * Attach the inline validator to the model metadata, this must be done once per admin
     */
    protected function attachInlineValidator()
    {
        $admin = $this;

        // add the custom inline validation option
        $metadata = $this->validator->getMetadataFactory()->getMetadataFor($this->getClass());

        $metadata->addConstraint(new InlineConstraint(array(
            'service' => $this,
            'method'  => function(ErrorElement $errorElement, $object) use ($admin) {
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
            }
        )));
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

        // Prevents BC break with KnpMenuBundle v1.x
        if (method_exists($menu, "setCurrentUri")) {
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
     * @param string                                   $action
     * @param \Sonata\AdminBundle\Admin\AdminInterface $childAdmin
     *
     * @return \Knp\Menu\ItemInterface
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
     * Returns the root code
     *
     * @return string the root code
     */
    public function getRootCode()
    {
        return $this->getRoot()->getCode();
    }

    /**
     * Returns the master admin
     *
     * @return \Sonata\AdminBundle\Admin\Admin the root admin class
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
     * @param boolean $persist
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
        $showGroups                   = $this->getShowGroups();
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
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        if ($this->subject === null && $this->request) {
            $id = $this->request->get($this->getIdParameter());
            if (!preg_match('#^[0-9A-Fa-f]+$#', $id)) {
                $this->subject = false;
            } else {
                $this->subject = $this->getModelManager()->find($this->class, $id);
            }
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
     * Returns true if the admin has a FieldDescription with the given $name
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
     * remove a FieldDescription
     *
     * @param string $name
     *
     * @return void
     */
    public function removeFormFieldDescription($name)
    {
        unset($this->formFieldDescriptions[$name]);
    }

    /**
     * build and return the collection of form FieldDescription
     *
     * @return array collection of form FieldDescription
     */
    public function getShowFieldDescriptions()
    {
        $this->buildShow();

        return $this->showFieldDescriptions;
    }

    /**
     * Returns the form FieldDescription with the given $name
     *
     * @param string $name
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
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
     * Returns a filter FieldDescription
     *
     * @param string $name
     *
     * @return array|null
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
     * Returns true if the admin has children, false otherwise
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
            $this->uniqid = "s".uniqid();
        }

        return $this->uniqid;
    }

    /**
     * Returns the classname label
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
        if ($this->isChild()) {
            return $this->getParent()->getBreadcrumbs($action);
        }

        $menu = $this->buildBreadcrumbs($action);

        do {
            $breadcrumbs[] = $menu;
        } while ($menu = $menu->getParent());

        $breadcrumbs = array_reverse($breadcrumbs);
        array_shift($breadcrumbs);
        return $breadcrumbs;
    }

    /**
     * Generates the breadcrumbs array
     *
     * Note: the method will be called by the top admin instance (parent => child)
     *
     * @param string                       $action
     * @param \Knp\Menu\ItemInterface|null $menu
     *
     * @return array
     */
    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        if (isset($this->breadcrumbs[$action])) {
            return $this->breadcrumbs[$action];
        }

        if (!$menu) {
            $menu = $this->menuFactory->createItem('root');

            $menu = $menu->addChild(
                $this->trans($this->getLabelTranslatorStrategy()->getLabel('dashboard', 'breadcrumb', 'link'), array(), 'SonataAdminBundle'),
                array('uri' => $this->routeGenerator->generate('sonata_admin_dashboard'))
            );
        }

        $menu = $menu->addChild(
            $this->trans($this->getLabelTranslatorStrategy()->getLabel(sprintf('%s_list', $this->getClassnameLabel()), 'breadcrumb', 'link')),
            array('uri' => $this->hasRoute('list') && $this->isGranted('LIST') ? $this->generateUrl('list') : null)
        );

        $childAdmin = $this->getCurrentChildAdmin();

        if ($childAdmin) {
            $id = $this->request->get($this->getIdParameter());

            $menu = $menu->addChild(
                $this->toString($this->getSubject()),
                array('uri' => $this->hasRoute('edit') && $this->isGranted('EDIT') ? $this->generateUrl('edit', array('id' => $id)) : null)
            );

            return $childAdmin->buildBreadcrumbs($action, $menu);

        } elseif ($this->isChild()) {

            if ($action == 'list') {
                $menu->setUri(false);
            } elseif ($action != 'create' && $this->hasSubject()) {
                $menu = $menu->addChild($this->toString($this->getSubject()));
            } else {
                $menu = $menu->addChild(
                    $this->trans($this->getLabelTranslatorStrategy()->getLabel(sprintf('%s_%s', $this->getClassnameLabel(), $action), 'breadcrumb', 'link'))
                );
            }

        } elseif ($action != 'list' && $this->hasSubject()) {
            $menu = $menu->addChild($this->toString($this->getSubject()));
        } elseif ($action != 'list') {
            $menu = $menu->addChild(
                $this->trans($this->getLabelTranslatorStrategy()->getLabel(sprintf('%s_%s', $this->getClassnameLabel(), $action), 'breadcrumb', 'link'))
            );
        }

        return $this->breadcrumbs[$action] = $menu;
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
     * Returns the current child admin instance
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface|null the current child admin instance
     */
    public function getCurrentChildAdmin()
    {
        foreach ($this->children as $children) {
            if ($children->getCurrentChild()) {
                return $children;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $domain = $domain ?: $this->getTranslationDomain();

        if (!$this->translator) {
            return $id;
        }

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * translate a message id
     *
     * @param string  $id
     * @param integer $count
     * @param array   $parameters
     * @param null    $domain
     * @param null    $locale
     *
     * @return string the translated string
     */
    public function transChoice($id, $count, array $parameters = array(), $domain = null, $locale = null)
    {
        $domain = $domain ?: $this->getTranslationDomain();

        if (!$this->translator) {
            return $id;
        }

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
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslator()
    {
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
     * @return \Sonata\AdminBundle\Builder\FormContractorInterface
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
     * @param \Sonata\AdminBundle\Builder\ShowBuilderInterface $showBuilder
     *
     * @return void
     */
    public function setShowBuilder(ShowBuilderInterface $showBuilder)
    {
        $this->showBuilder = $showBuilder;
    }

    /**
     * @return \Sonata\AdminBundle\Builder\ShowBuilderInterface
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
     * @return \Sonata\AdminBundle\Route\RouteGeneratorInterface
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
     * @param \Sonata\AdminBundle\Model\ModelManagerInterface $modelManager
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
     * Set the roles and permissions per role
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
     * Return the list of permissions the user should have in order to display the admin
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
        $key = md5(json_encode($name) . ($object ? '/'.spl_object_hash($object) : ''));

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
    public function setValidator(ValidatorInterface $validator)
    {
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

        return sprintf("%s:%s", ClassUtils::getClass($object), spl_object_hash($object));
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
     * Set custom per page options
     *
     * @param array $options
     */
    public function setPerPageOptions(array $options)
    {
        $this->perPageOptions = $options;
    }

    /**
     * Returns predefined per page options
     *
     * @return array
     */
    public function getPerPageOptions()
    {
        return $this->perPageOptions;
    }

    /**
     * Returns true if the per page value is allowed, false otherwise
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
     * Predefine per page options
     */
    protected function predefinePerPageOptions()
    {
        array_unshift($this->perPageOptions, $this->maxPerPage);
        $this->perPageOptions = array_unique($this->perPageOptions);
        sort($this->perPageOptions);
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
}
