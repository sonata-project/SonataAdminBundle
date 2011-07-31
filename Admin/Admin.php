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
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;

use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Model\ModelManagerInterface;

use Knp\Bundle\MenuBundle\Menu;
use Knp\Bundle\MenuBundle\MenuItem;

abstract class Admin implements AdminInterface, DomainObjectInterface
{
    /**
     * The class name managed by the admin class
     *
     * @var string
     */
    protected $class;

    /**
     * The list collection
     *
     * @var array
     */
    protected $list;

    /**
     * The list FieldDescription constructed from the configureListField method
     *
     * @var array
     */
    protected $listFieldDescriptions = array();

    protected $show;

    /**
     * The show FieldDescription constructed from the configureShowField method
     *
     * @var array
     */
    protected $showFieldDescriptions = array();

    /**
     * @var Form
     */
    protected $form;

    /**
     * The list FieldDescription constructed from the configureFormField method
     *
     * @var array
     */
    protected $formFieldDescriptions = array();

    /**
     * @var DatagridInterface
     */
    protected $filter;

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
    protected $formGroups = false;

    /**
     * The view group disposition
     *
     * @var array|boolean
     */
    protected $viewGroups = false;

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
    );

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
     * @var FieldDescription
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
     * @var \Sonata\AdminBundle\View\ShowBuilderInterface
     */
    protected $showBuilder;

    /**
     * The related datagrid builder
     *
     * @var \Sonata\AdminBundle\Builder\DatagridBuilderInterface
     */
    protected $datagridBuilder;

    /**
     * The datagrid instance
     *
     * @var \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    protected $datagrid;

    /**
     * The router intance
     *
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * The generated breadcrumbs
     *
     * @var array
     */
    protected $breadcrumbs = array();

    protected $securityHandler = null;

    protected $validator = null;

    /**
     * The configuration pool
     *
     * @var Pool
     */
    protected $configurationPool;

    protected $menu;

    protected $loaded = array(
        'view_fields'   => false,
        'view_groups'   => false,
        'routes'        => false,
        'side_menu'     => false,
    );

    /**
     * This method can be overwritten to tweak the form construction, by default the form
     * is built by reading the FieldDescription
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $form)
    {
    }

    /**
     * overwrite this method to configure the list FormField definition
     *
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list)
    {

    }

    /**
     *
     * @param DatagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {

    }

    /**
     *
     * @param DatagridMapper
     */
    protected function configureShowField(ShowMapper $filter)
    {

    }

    /**
     * configure the Admin routes
     *
     * @param RouteCollection
     */
    public function configureRoutes(RouteCollection $collection)
    {

    }

    public function configureSideMenu(MenuItem $menu, $action, Admin $childAdmin = null)
    {

    }

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
    }

    public function configure()
    {
        $this->uniqid = uniqid();

        if (!$this->classnameLabel) {
            $this->classnameLabel = $this->urlize(substr($this->class, strrpos($this->class, '\\') + 1), '_');
        }

        $this->baseCodeRoute = $this->getCode();
    }

    public function update($object)
    {
        $this->preUpdate($object);
        $this->modelManager->update($object);
        $this->postUpdate($object);
    }

    public function create($object)
    {
        $this->prePersist($object);
        $this->modelManager->create($object);
        $this->postPersist($object);
    }

    public function delete($object)
    {
        $this->preRemove($object);
        $this->modelManager->delete($object);
        $this->postRemove($object);
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

        $collection = new FieldDescriptionCollection();
        $mapper = new ShowMapper($this->showBuilder, $collection, $this);

        $this->configureShowField($mapper);

        if (!$this->viewGroups) {
            $this->viewGroups = array(
                false => array('fields' => array_keys($this->getShowFieldDescriptions()))
            );
        }

        // normalize array
        foreach ($this->viewGroups as $name => $group) {
            if (!isset($this->viewGroups[$name]['collapsed'])) {
                $this->viewGroups[$name]['collapsed'] = false;
            }
        }

        $this->show = $collection;
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
            $fieldDescription = $this->modelManager->getNewFieldDescriptionInstance($this->getClass(), 'batch', array(
                'label'    => 'batch',
                'code'     => '_batch',
                'sortable' => false
            ));

            $fieldDescription->setAdmin($this);
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list__batch.html.twig');

            $mapper->add($fieldDescription, 'batch');
        }

        $this->configureListFields($mapper);
    }

    /**
     * Get parameters that are currently bound to the filter.
     *
     * @return array
     */
    public function getFilterParameters()
    {
        $parameters = array();

        // build the values array
        if ($this->hasRequest()) {
            $parameters = array_merge(
                $this->getModelManager()->getDefaultSortValues($this->getClass()),
                $this->datagridValues,
                $this->request->query->all()
            );

            // always force the parent value
            if ($this->isChild() && $this->getParentAssociationMapping()) {
                $parameters[$this->getParentAssociationMapping()] = $this->request->get($this->getParent()->getIdParameter());
            }
        }

        return $parameters;
    }

    /**
     * build the filter FieldDescription array
     *
     * @return void
     */
    public function buildDatagrid()
    {
        if ($this->datagrid) {
            return;
        }

        // ok, try to limit to add parent filter
        if ($this->getParentAssociationMapping()) {
            $fieldDescription = $this->getModelManager()->getParentFieldDescription($this->getParentAssociationMapping(), $this->getClass());
            $this->filterFieldDescriptions[$this->getParentAssociationMapping()] = $fieldDescription;
        }

        foreach ($this->filterFieldDescriptions as $fieldDescription) {
            $this->getDatagridBuilder()->fixFieldDescription($this, $fieldDescription);
        }

        $parameters = $this->getFilterParameters();

        // initialize the datagrid
        $this->datagrid = $this->getDatagridBuilder()->getBaseDatagrid($this, $parameters);
        $this->datagrid->getPager()->setMaxPerPage($this->maxPerPage);

        $mapper = new DatagridMapper($this->getDatagridBuilder(), $this->datagrid, $this);

        // build the datagrid filter
        $this->configureDatagridFilters($mapper);
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

            $propertyPath = new \Symfony\Component\Form\Util\PropertyPath($this->getParentAssociationMapping());
            $propertyPath->setValue($object, $parent);
        }

        $this->form = $this->getFormBuilder()->getForm();
    }

    /**
     * Returns the baseRoutePattern used to generate the routing information
     *
     * @throws RuntimeException
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
     * @throws RuntimeException
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
     * @param string $sep the separator
     *
     * @return string
     */
    public function urlize($word, $sep = '_')
    {
        return strtolower(preg_replace('/[^a-z0-9_]/i', $sep.'$1', $word));
    }

    /**
     * Returns the class name handled by the Admin instance
     *
     * @return string the class name handled by the Admin instance
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns the list of batchs actions
     *
     * @return array the list of batchs actions
     */
    public function getBatchActions()
    {
        $actions = array();

        if ($this->isGranted('DELETE')) {
            $actions['delete'] = $this->trans('action_delete', array(), 'SonataAdminBundle');
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
     * Returns the parameter representing router id, ie: {id} or {childId}
     *
     * @return string
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

        $collection = new RouteCollection(
            $this->getBaseCodeRoute(),
            $this->getBaseRouteName(),
            $this->getBaseRoutePattern(),
            $this->getBaseControllerName()
        );

        $collection->add('list');
        $collection->add('create');
        $collection->add('batch');
        $collection->add('edit', $this->getRouterIdParameter().'/edit');
        $collection->add('delete', $this->getRouterIdParameter().'/delete');
        $collection->add('show', $this->getRouterIdParameter().'/show');

        // add children urls
        foreach ($this->getChildren() as $children) {
            $collection->addCollection($children->getRoutes());
        }

        $this->configureRoutes($collection);

        $this->routes = $collection;
    }

    /**
     * Returns the url defined by the $name
     *
     * @param strinf $name
     * @return Route
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
     * generate the url with the given $name
     *
     * @throws RuntimeException
     * @param  $name
     * @param array $parameters
     *
     * @return return a complete url
     */
    public function generateUrl($name, array $parameters = array())
    {
        if (!$this->isChild()) {
            if (strpos($name, '.')) {
                $name = $this->getCode().'|'.$name;
            } else {
                $name = $this->getCode().'.'.$name;
            }
        }
        // if the admin is a child we automatically append the parent's id
        else if ($this->isChild()) {
            $name = $this->baseCodeRoute.'.'.$name;

            // twig template does not accept variable hash key ... so cannot use admin.idparameter ...
            // switch value
            if (isset($parameters['id'])) {
                $parameters[$this->getIdParameter()] = $parameters['id'];
                unset($parameters['id']);
            }

            $parameters[$this->getParent()->getIdParameter()] = $this->request->get($this->getParent()->getIdParameter());
        }

        // if the admin is linked to a parent FieldDescription (ie, embedded widget)
        if ($this->hasParentFieldDescription()) {
            // merge link parameter if any provided by the parent field
            $parameters = array_merge($parameters, $this->getParentFieldDescription()->getOption('link_parameters', array()));

            $parameters['uniqid']  = $this->getUniqid();
            $parameters['code']    = $this->getCode();
            $parameters['pcode']   = $this->getParentFieldDescription()->getAdmin()->getCode();
            $parameters['puniqid'] = $this->getParentFieldDescription()->getAdmin()->getUniqid();
        }

        if ($name == 'update' || substr($name, -7) == '|update') {
            $parameters['uniqid'] = $this->getUniqid();
            $parameters['code']   = $this->getCode();
        }

        // allows to define persistent parameters
        if ($this->hasRequest()) {
            $parameters = array_merge($this->getPersistentParameters(), $parameters);
        }

        $route = $this->getRoute($name);

        if (!$route) {
            throw new \RuntimeException(sprintf('unable to find the route `%s`', $name));
        }

        return $this->router->generate($route->getDefault('_sonata_name'), $parameters);
    }

    /**
     * Returns the list template
     *
     * @return string the list template
     */
    public function getListTemplate()
    {
        return 'SonataAdminBundle:CRUD:list.html.twig';
    }

    /**
     * Returns the edit template
     *
     * @return string the edit template
     */
    public function getEditTemplate()
    {
        return 'SonataAdminBundle:CRUD:edit.html.twig';
    }

    /**
     * Returns the view template
     *
     * @return string the view template
     */
    public function getShowTemplate()
    {
        return 'SonataAdminBundle:CRUD:show.html.twig';
    }

    /**
     * Returns an instance of the related classname
     *
     * @return Object An instance of the related classname
     */
    public function getNewInstance()
    {
        return $this->modelManager->getModelInstance($this->getClass());
    }

    /**
     * @param Object $object
     * @return \Symfony\Component\Form\FormBuilder the form builder
     */
    public function getFormBuilder()
    {
        // add the custom inline validation option
        $metadata = $this->validator->getMetadataFactory()->getClassMetadata($this->class);
        $metadata->addConstraint(new \Sonata\AdminBundle\Validator\Constraints\InlineConstraint(array(
            'service' => $this,
            'method'  => 'validate'
        )));

        $this->formOptions['data_class'] = $this->getClass();

        $formBuilder = $this->getFormContractor()->getFormBuilder(
            $this->getUniqid(),
            $this->formOptions
        );

        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @return void
     */
    public function defineFormBuilder(FormBuilder $formBuilder)
    {
        $mapper = new FormMapper($this->getFormContractor(), $formBuilder, $this);

        $this->configureFormFields($mapper);
    }

    /**
     * attach an admin instance to the given FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     */
    public function attachAdminClass(FieldDescriptionInterface $fieldDescription)
    {
        $pool = $this->getConfigurationPool();

        $admin = $pool->getAdminByClass($fieldDescription->getTargetEntity());
        if (!$admin) {
            return;
        }

        $fieldDescription->setAssociationAdmin($admin);
    }

    /**
     * Returns the target object
     *
     * @param integer $id
     * @return object
     */
    public function getObject($id)
    {
        return $this->modelManager->findOne($this->getClass(), $id);
    }

    /**
     * Returns a form depend on the given $object
     *
     * @param object $object
     * @param array $options the form options
     * @return \Symfony\Component\Form\Form
     */
    public function getForm()
    {
        $this->buildForm();

        return $this->form;
    }

    /**
     * Returns a list depend on the given $object
     *
     * @param array $options
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionCollection
     */
    public function getList()
    {
        $this->buildList();

        return $this->list;
    }

    /**
     * Returns a list depend on the given $object
     *
     * @return \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    public function getDatagrid()
    {
        $this->buildDatagrid();

        return $this->datagrid;
    }

    /**
     * Build the side menu related to the current action
     *
     * @param string $action
     * @param \Sonata\AdminBundle\Admin\AdminInterface $childAdmin
     * @return MenuItem|false
     */
    public function buildSideMenu($action, AdminInterface $childAdmin = null)
    {
        if ($this->loaded['side_menu']) {
            return;
        }

        $this->loaded['side_menu'] = true;

        $menu = new Menu;

        $this->configureSideMenu($menu, $action, $childAdmin);

        $this->menu = $menu;
    }

    /**
     * @param string $action
     * @param \Sonata\AdminBundle\Admin\AdminInterface $childAdmin
     * @return \Knp\MenuBundle\Menu
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

    public function setBaseControllerName($baseControllerName)
    {
        $this->baseControllerName = $baseControllerName;
    }

    public function getBaseControllerName()
    {
        return $this->baseControllerName;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;
    }

    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    public function getFormGroups()
    {
        return $this->formGroups;
    }

    public function setFormGroups(array $formGroups)
    {
        $this->formGroups = $formGroups;
    }

    public function getViewGroups()
    {
        return $this->viewGroups;
    }

    /**
     * set the parent FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $parentFieldDescription
     * @return void
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
     * set the subject linked to the admin, the subject is the related model
     *
     * @param object $subject
     * @return void
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Returns the subject, if none is set try to load one from the request
     *
     * @return $object the subject
     */
    public function getSubject()
    {
        if ($this->subject === null && $this->request) {

            $id = $this->request->get($this->getIdParameter());
            if (!is_numeric($id)) {
                $this->subject = false;
            } else {
                $this->subject = $this->getModelManager()->findOne(
                    $this->getClass(),
                    $id
                );
            }
        }

        return $this->subject;
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
     * Returns the form FieldDescription with the given $name
     *
     * @param string $name
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getFormFieldDescription($name)
    {
        return $this->hasFormFieldDescription($name) ? $this->formFieldDescriptions[$name] : null;
    }

    /**
     * Returns true if the admin has a FieldDescription with the given $name
     *
     * @param string $name
     * @return bool
     */
    public function hasFormFieldDescription($name)
    {
        return array_key_exists($name, $this->formFieldDescriptions) ? true : false;
    }

    /**
     * add a FieldDescription
     *
     * @param string $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
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
        return $this->showFieldDescriptions;
    }

    /**
     * Returns the form FieldDescription with the given $name
     *
     * @param string $name
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getShowFieldDescription($name)
    {
        return $this->hasShowFieldDescription($name) ? $this->showFieldDescriptions[$name] : null;
    }

    /**
     * Returns true if the admin has a FieldDescription with the given $name
     *
     * @param string $name
     * @return bool
     */
    public function hasShowFieldDescription($name)
    {
        return array_key_exists($name, $this->showFieldDescriptions);
    }

    /**
     * add a FieldDescription
     *
     * @param string $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addShowFieldDescription($name, FieldDescriptionInterface $fieldDescription)
    {
        $this->showFieldDescriptions[$name] = $fieldDescription;
    }

    /**
     * remove a FieldDescription
     *
     * @param string $name
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
     * Returns a list FieldDescription
     *
     * @param string $name
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getListFieldDescription($name)
    {
        return $this->hasListFieldDescription($name) ? $this->listFieldDescriptions[$name] : null;
    }

    /**
     * Returns true if the list FieldDescription exists
     *
     * @param string $name
     * @return bool
     */
    public function hasListFieldDescription($name)
    {
        $this->buildList();

        return array_key_exists($name, $this->listFieldDescriptions) ? true : false;
    }

    /**
     * add a list FieldDescription
     *
     * @param string $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addListFieldDescription($name, FieldDescriptionInterface $fieldDescription)
    {
        $this->listFieldDescriptions[$name] = $fieldDescription;
    }

    /**
     * remove a list FieldDescription
     *
     * @param string $name
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
     * @return bool
     */
    public function hasFilterFieldDescription($name)
    {
        return array_key_exists($name, $this->filterFieldDescriptions) ? true : false;
    }

    /**
     * add a filter FieldDescription
     *
     * @param string $name
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
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
     * @param array filter FieldDescription collection
     */
    public function getFilterFieldDescriptions()
    {
        return $this->filterFieldDescriptions;
    }

    /**
     * add an Admin child to the current one
     *
     * @param string $code
     * @param \Sonata\AdminBundle\Admin\Admin $child
     * @return void
     */
    public function addChild(AdminInterface $child)
    {
        $this->children[$child->getCode()] = $child;

        $child->setBaseCodeRoute($this->getCode().'|'.$child->getCode());
        $child->setParent($this);
    }

    /**
     * Returns true or false if an Admin child exists for the given $code
     *
     * @param string $code Admin code
     * @return bool True if child exist, false otherwise
     */
    public function hasChild($code)
    {
        return isset($this->children[$code]);
    }

    /**
     * Returns an collection of admin children
     *
     * @return array list of Admin children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Returns an admin child with the given $code
     *
     * @param string $code
     * @return array|null
     */
    public function getChild($code)
    {
        return $this->hasChild($code) ? $this->children[$code] : null;
    }

    /**
     * set the Parent Admin
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $parent
     * @return void
     */
    public function setParent(AdminInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * get the Parent Admin
     *
     * @return \Sonata\AdminBundle\Admin\Admin|null
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
     * set the uniqid
     *
     * @param  $uniqid
     * @return void
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
     * @param string $action
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
     * @param string $action
     * @param \Knp\MenuBundle\MenuItem|null $menu
     * @return array the breadcrumbs
     */
    public function buildBreadcrumbs($action, MenuItem $menu = null)
    {
        if (isset($this->breadcrumbs[$action])) {
            return $this->breadcrumbs[$action];
        }

        $menu = $menu ?: new Menu;

        $child = $menu->addChild(
            $this->trans(sprintf('link_%s_list', $this->getClassnameLabel())),
            $this->generateUrl('list')
        );

        $childAdmin = $this->getCurrentChildAdmin();

        if ($childAdmin) {
            $id = $this->request->get($this->getIdParameter());

            $child = $child->addChild(
                (string) $this->getSubject(),
                $this->generateUrl('edit', array('id' => $id))
            );

            return $childAdmin->buildBreadcrumbs($action, $child);

        } elseif ($this->isChild()) {

            if ($action != 'list') {
                $menu = $menu->addChild(
                    $this->trans(sprintf('link_%s_list', $this->getClassnameLabel())),
                    $this->generateUrl('list')
                );
            }

            $breadcrumbs = $menu->getBreadcrumbsArray(
                $this->trans(sprintf('link_%s_%s', $this->getClassnameLabel(), $action))
            );

        } else if ($action != 'list') {

            $breadcrumbs = $child->getBreadcrumbsArray(
                $this->trans(sprintf('link_%s_%s', $this->getClassnameLabel(), $action))
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
     * translate a message id
     *
     * @param string $id
     * @param array $parameters
     * @param null $domain
     * @param null $locale
     * @return string the translated string
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
     * set the translation domain
     *
     * @param string $translationDomain the translation domain
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
     *
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     *
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        if ($request->get('uniqid')) {
            $this->setUniqid($request->get('uniqid'));
        }

        foreach ($this->getChildren() as $children) {
            $children->setRequest($request);
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
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
     * @return true if the request object is linked to the Admin
     */
    public function hasRequest()
    {
        return $this->request !== null;
    }

    /**
     * @param \Sonata\AdminBundle\Builder\FormContractorInterface $formBuilder
     * @return void
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
     * @param \Sonata\AdminBundle\Builder\DatagridBuilderInterface $datagridBuilder
     * @return void
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
     * @param \Sonata\AdminBundle\Builder\ListBuilderInterface $listBuilder
     * @return void
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
     * @param Pool $configurationPool
     * @return void
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
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return void
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setBaseCodeRoute($baseCodeRoute)
    {
        $this->baseCodeRoute = $baseCodeRoute;
    }

    public function getBaseCodeRoute()
    {
        return $this->baseCodeRoute;
    }

    /**
     * @return \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    public function getModelManager()
    {
        return $this->modelManager;
    }

    public function setModelManager(ModelManagerInterface $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * Returns a unique identifier for this domain object.
     *
     * @return string
     */
    function getObjectIdentifier()
    {
        return $this->getCode();
    }

    /**
     * Return the list of security name available for the current admin
     * This should be used by experimented users
     *
     * @return array
     */
    public function getSecurityInformation()
    {
        return array(
            'EDIT'      => array('EDIT'),
            'LIST'      => array('LIST'),
            'CREATE'    => array('CREATE'),
            'VIEW'      => array('VIEW'),
            'DELETE'    => array('DELETE'),
            'OPERATOR'  => array('OPERATOR')
        );
    }

    public function setSecurityHandler(SecurityHandlerInterface $securityHandler)
    {
        $this->securityHandler = $securityHandler;
    }

    public function getSecurityHandler()
    {
        return $this->securityHandler;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function isGranted($name)
    {
        return $this->securityHandler->isGranted($name, $this);
    }

    public function getNormalizedIdentifier($entity)
    {
        return $this->getModelManager()->getNormalizedIdentifier($entity);
    }

    /**
     * Shorthand method for templating
     *
     * @param object $entity
     * @return
     */
    public function id($entity)
    {
        return $this->getNormalizedIdentifier($entity);
    }

    public function setValidator(ValidatorInterface $validator)
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
}
