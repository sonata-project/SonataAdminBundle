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

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Util\PropertyPath;
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
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Model\ModelManagerInterface;

use Knp\Menu\FactoryInterface as MenuFactoryInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;

abstract class Admin implements AdminInterface, DomainObjectInterface
{
    const CONTEXT_MENU       = 'menu';
    const CONTEXT_DASHBOARD  = 'dashboard';

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
     * The view group disposition
     *
     * @var array|boolean
     */
    private $showGroups = false;

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

    protected $securityHandler = null;

    /**
     * @var \Symfony\Component\Validator\ValidatorInterface $validator
     */
    protected $validator = null;

    /**
     * The configuration pool
     *
     * @var Pool
     */
    protected $configurationPool;

    protected $menu;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    protected $menuFactory;

    protected $loaded = array(
        'view_fields'   => false,
        'view_groups'   => false,
        'routes'        => false,
        'side_menu'     => false,
    );

    protected $formTheme = array();

    protected $filterTheme = array();

    protected $templates  = array();

    protected $extensions = array();

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
     * @deprecated removed with Symfony 2.2
     *
     * {@inheritdoc}
     */
    protected function configureShowField(ShowMapper $show)
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
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {

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
     * @return
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
        $this->uniqid = "s".uniqid();

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
        $this->getModelManager()->update($object);
        $this->postUpdate($object);
    }

    /**
     * {@inheritdoc}
     */
    public function create($object)
    {
        $this->prePersist($object);
        $this->getModelManager()->create($object);
        $this->postPersist($object);
        $this->createObjectSecurity($object);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $this->preRemove($object);
        $this->getSecurityHandler()->deleteObjectSecurity($this, $object);
        $this->getModelManager()->delete($object);
        $this->postRemove($object);
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

        $this->configureShowField($mapper); // deprecated, use configureShowFields instead
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
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list__batch.html.twig');

            $mapper->add($fieldDescription, 'batch');
        }

        $this->configureListFields($mapper);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureListFields($mapper);
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
                $parameters[$this->getParentAssociationMapping()] = array('value' => $this->request->get($this->getParent()->getIdParameter()));
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
                'field_type' => 'sonata_type_model_reference',
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

            $propertyPath = new PropertyPath($this->getParentAssociationMapping());

            $object = $this->getSubject();

            $propertyPath->setValue($object, $parent);
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
            preg_match('@([A-Za-z0-9]*)\\\([A-Za-z0-9]*)Bundle\\\(Entity|Document|Model)\\\(.*)@', $this->getClass(), $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', get_class($this)));
            }

            if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
                $this->baseRoutePattern = sprintf('%s/{id}/%s',
                    $this->getParent()->getBaseRoutePattern(),
                    $this->urlize($matches[4], '-')
                );
            } else {

                $this->baseRoutePattern = sprintf('/%s/%s/%s',
                    $this->urlize($matches[1], '-'),
                    $this->urlize($matches[2], '-'),
                    $this->urlize($matches[4], '-')
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
            preg_match('@([A-Za-z0-9]*)\\\([A-Za-z0-9]*)Bundle\\\(Entity|Document|Model)\\\(.*)@', $this->getClass(), $matches);

            if (!$matches) {
                throw new \RuntimeException(sprintf('Please define a default `baseRouteName` value for the admin class `%s`', get_class($this)));
            }

            if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
                $this->baseRouteName = sprintf('%s_%s',
                    $this->getParent()->getBaseRouteName(),
                    $this->urlize($matches[4])
                );
            } else {

                $this->baseRouteName = sprintf('admin_%s_%s_%s',
                    $this->urlize($matches[1]),
                    $this->urlize($matches[2]),
                    $this->urlize($matches[4])
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
        return $this->class;
    }

    /**
     * Returns the list of supported sub classes
     *
     * @return array the list of sub classes
     */
    public function getSubClasses()
    {
        return $this->subClasses;
    }

    /**
     * Sets the list of supported sub classes
     *
     * @param array $subClasses the list of sub classes
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

        return null;
    }

    /**
     * Returns true if the admin has the sub classes
     *
     * @param string $name The name of the sub class
     *
     * @return bool
     */
    public function hasSubClass($name)
    {
        return isset($this->subClasses[$name]);
    }

    /**
     * Returns true if a subclass is currently active
     *
     * @return bool
     */
    public function hasActiveSubClass()
    {
        if ($this->request) {
            return null !== $this->getRequest()->get('subclass');
        }

        return false;
    }

    /**
     * Returns the currently active sub class
     *
     * @return string the active sub class
     */
    public function getActiveSubClass()
    {
        if (!$this->hasActiveSubClass()) {
            return null;
        }

        $subClass = $this->getRequest()->get('subclass');

        return $this->getSubClass($subClass);
    }

    /**
     * Returns the list of batchs actions
     *
     * @return array the list of batchs actions
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
     * Returns the list of available urls
     *
     * @return \Sonata\AdminBundle\Route\RouteCollection the list of available urls
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
     * Returns the parameter representing request id, ie: id or childId
     *
     * @return string
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
    public function buildRoutes()
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
     * {@inheritdoc}
     */
    public function getRoute($name)
    {
        $this->buildRoutes();

        if (!$this->routes->has($name)) {
            return false;
        }

        return $this->routes->get($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasRoute($name)
    {
        $this->buildRoutes();

        if (
            ! $this->isChild()
            && strpos($name, '.') !== false
            && strpos($name, $this->getBaseCodeRoute() . '|') !== 0
            && strpos($name, $this->getBaseCodeRoute() . '.') !== 0
        ) {
            $name = $this->getCode() . '|' . $name;
        }

        return $this->routes->has($name);
    }

    /**
     * Generates the object url with the given $name
     *
     * @param string $name
     * @param mixed  $object
     * @param array  $parameters
     *
     * @return string return a complete url
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
     * @param string $name
     *
     * @return null|string
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
        return $this->getModelManager()->getModelInstance($this->getActiveSubClass() ?: $this->getClass());
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getActiveSubClass() ?: $this->getClass();

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
        return $this->getModelManager()->find($this->getClass(), $id);
    }

    /**
     * Returns a form depend on the given $object
     *
     * @return \Symfony\Component\Form\Form
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
     * Build the side menu related to the current action
     *
     * @param string                                   $action
     * @param \Sonata\AdminBundle\Admin\AdminInterface $childAdmin
     *
     * @return \Knp\Menu\ItemInterface|boolean
     */
    public function buildSideMenu($action, AdminInterface $childAdmin = null)
    {
        if ($this->loaded['side_menu']) {
            return;
        }

        $this->loaded['side_menu'] = true;

        $menu = $this->menuFactory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav nav-list');
        $menu->setCurrentUri($this->getRequest()->getBaseUrl().$this->getRequest()->getPathInfo());

        $this->configureSideMenu($menu, $action, $childAdmin);

        foreach ($this->getExtensions() as $extension) {
            $extension->configureSideMenu($this, $menu, $action, $childAdmin);
        }

        $this->menu = $menu;
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
     * @param string $baseControllerName
     */
    public function setBaseControllerName($baseControllerName)
    {
        $this->baseControllerName = $baseControllerName;
    }

    /**
     * @return string
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
     * @return string
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
     * @return array
     */
    public function getFormGroups()
    {
        return $this->formGroups;
    }

    /**
     * @param array $formGroups
     */
    public function setFormGroups(array $formGroups)
    {
        $this->formGroups = $formGroups;
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
     * @return array
     */
    public function getShowGroups()
    {
        return $this->showGroups;
    }

    /**
     * @param array $showGroups
     */
    public function setShowGroups(array $showGroups)
    {
        $this->showGroups = $showGroups;
    }

    /**
     * @param string $group
     * @param array  $keys
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
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface the parent field description
     */
    public function getParentFieldDescription()
    {
        return $this->parentFieldDescription;
    }

    /**
     * Returns true if the Admin is linked to a parent FieldDescription
     *
     * @return bool
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
                $this->subject = $this->getModelManager()->find($this->getClass(), $id);
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
     * build and return the collection of form FieldDescription
     *
     * @return array collection of form FieldDescription
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
     * add a FieldDescription
     *
     * @param string                                              $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @return void
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
     * Returns true if the admin has a FieldDescription with the given $name
     *
     * @param string $name
     *
     * @return bool
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
     * remove a FieldDescription
     *
     * @param string $name
     *
     * @return void
     */
    public function removeShowFieldDescription($name)
    {
        unset($this->showFieldDescriptions[$name]);
    }

    /**
     * Returns the collection of list FieldDescriptions
     *
     * @return array
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
     * Returns true if the list FieldDescription exists
     *
     * @param string $name
     *
     * @return bool
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
     * remove a list FieldDescription
     *
     * @param string $name
     *
     * @return void
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
     * Returns true if the filter FieldDescription exists
     *
     * @param string $name
     *
     * @return bool
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
     * remove a filter FieldDescription
     *
     * @param string $name
     */
    public function removeFilterFieldDescription($name)
    {
        unset($this->filterFieldDescriptions[$name]);
    }

    /**
     * Returns the filter FieldDescription collection
     *
     * @return FieldDescriptionInterface[]
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
     * {@inheritDoc}
     */
    public function setParent(AdminInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns true if the Admin class has an Parent Admin defined
     *
     * @return boolean
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
     * Returns the uniqid
     *
     * @return integer
     */
    public function getUniqid()
    {
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
     * Returns an array of persistent parameters
     *
     * @return array
     */
    public function getPersistentParameters()
    {
        return array();
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
     * @param string $action
     *
     * @return array
     */
    public function getBreadcrumbs($action)
    {
        if ($this->isChild()) {
            return $this->getParent()->getBreadcrumbs($action);
        }

        return $this->buildBreadcrumbs($action);
    }

    /**
     * Generates the breadcrumbs array
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
        }

        $child = $menu->addChild(
            $this->trans($this->getLabelTranslatorStrategy()->getLabel('dashboard', 'breadcrumb', 'link'), array(), 'SonataAdminBundle'),
            array('uri' => $this->routeGenerator->generate('sonata_admin_dashboard'))
        );

        $child = $child->addChild(
            $this->trans($this->getLabelTranslatorStrategy()->getLabel(sprintf('%s_list', $this->getClassnameLabel()), 'breadcrumb', 'link')),
            array('uri' => $this->hasRoute('list') && $this->isGranted('LIST') ? $this->generateUrl('list') : null)
        );

        $childAdmin = $this->getCurrentChildAdmin();

        if ($childAdmin) {
            $id = $this->request->get($this->getIdParameter());

            $child = $child->addChild(
                $this->toString($this->getSubject()),
                array('uri' => $this->hasRoute('edit') && $this->isGranted('EDIT') ? $this->generateUrl('edit', array('id' => $id)) : null)
            );

            return $childAdmin->buildBreadcrumbs($action, $child);

        } elseif ($this->isChild()) {
            if ($action != 'list') {
                $menu = $menu->addChild(
                    $this->trans($this->getLabelTranslatorStrategy()->getLabel(sprintf('%s_list', $this->getClassnameLabel()), 'breadcrumb', 'link')),
                    array('uri' => $this->hasRoute('list') && $this->isGranted('LIST') ? $this->generateUrl('list') : null)
                );
            }

            if ($action != 'create' && $this->hasSubject()) {
                $breadcrumbs = $menu->getBreadcrumbsArray($this->toString($this->getSubject()));
            } else {
                $breadcrumbs = $menu->getBreadcrumbsArray(
                    $this->trans($this->getLabelTranslatorStrategy()->getLabel(sprintf('%s_%s', $this->getClassnameLabel(), $action), 'breadcrumb', 'link'))
                );
            }

        } elseif ($action != 'list') {
            $breadcrumbs = $child->getBreadcrumbsArray(
//                $this->trans($this->getLabelTranslatorStrategy()->getLabel(sprintf('%s_%s', $this->getClassnameLabel(), $action), 'breadcrumb', 'link'))
                  $this->toString($this->getSubject())
            );
        } else {
            $breadcrumbs = $child->getBreadcrumbsArray();
        }

        // the generated $breadcrumbs contains an empty element
        array_shift($breadcrumbs);

        return $this->breadcrumbs[$action] = $breadcrumbs;
    }

    /**
     * set the current child status
     *
     * @param boolean $currentChild
     *
     * @return void
     */
    public function setCurrentChild($currentChild)
    {
        $this->currentChild = $currentChild;
    }

    /**
     * Returns the current child status
     *
     * @return bool
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
        $domain = $domain ?: $this->translationDomain;

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
        $domain = $domain ?: $this->translationDomain;

        if (!$this->translator) {
            return $id;
        }

        return $this->translator->transChoice($id, $count, $parameters, $domain, $locale);
    }

    /**
     * set the translation domain
     *
     * @param string $translationDomain the translation domain
     *
     * @return void
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
    }

    /**
     * Returns the translation domain
     *
     * @return string the translation domain
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
     * @return \Symfony\Component\Translation\TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
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
     *
     * @return boolean true if the request object is linked to the Admin
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
     * @return \Sonata\AdminBundle\Builder\DatagridBuilderInterface
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
     * @return \Sonata\AdminBundle\Builder\ListBuilderInterface
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
     * @return string
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
        return $this->securityHandler->isGranted($this, $name, $object ?: $this);
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
        if (method_exists($object, '__toString')) {
            return (string) $object;
        }

        return sprintf("%s:%s", get_class($object), spl_object_hash($object));
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
     * Returning true will enable preview mode for
     * the target entity and show a preview button
     * when editing/creating an entity
     *
     * @return boolean
     */
    public function supportsPreviewMode()
    {
        return $this->supportsPreviewMode;
    }

    /**
     * Set custom per page options
     *
     * @param $options
     */
    public function setPerPageOptions($options)
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
     * @param $per_page
     * @return bool
     */
    public function determinedPerPageValue($per_page)
    {
        return in_array($per_page, $this->perPageOptions);
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
}
