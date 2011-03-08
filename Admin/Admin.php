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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
    
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\Datagrid;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Builder\FormBuilderInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;

use Knplabs\MenuBundle\Menu;
use Knplabs\MenuBundle\MenuItem;

abstract class Admin implements AdminInterface
{
    /**
     * The class name managed by the admin class
     *
     * @var string
     */
    protected $class;

    /**
     * The list field definitions (quick property definition)
     *
     * @var array
     */
    protected $list = array();

    /**
     * The list FieldDescription constructed from the $list property
     * and the the configureListField method
     *
     * @var array
     */
    protected $listFieldDescriptions = array();

    /**
     * The form field definition (quick property definition)
     *
     * @var array
     */
    protected $form = array();

    /**
     * The list FieldDescription constructed from the $list property
     * and the the configureFormField method
     *
     * @var array
     */
    protected $formFieldDescriptions = array();

    /**
     * The filter field definition (quick property definition)
     *
     * @var array
     */
    protected $filter = array();

    /**
     * The filter FieldDescription constructed from the $list property
     * and the the configureFilterField method
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
    protected $translationDomain = 'AdminBundle';

    /**
     * options to set to the form (ie, validation_groups)
     *
     * @var array
     */
    protected $formOptions = array();

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
     * Array of urls related to this admin
     *
     * @var array
     */
    protected $urls = array();

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
     * @var object
     */
    protected $modelManager;

    /**
     * The current request object
     *
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * The translator component
     *
     * @var Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * The related form builder
     *
     * @var Sonata\AdminBundle\Builder\FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * The related list builder
     *
     * @var Sonata\AdminBundle\Builder\ListBuilderInterface
     */
    protected $listBuilder;

    /**
     * The related datagrid builder
     *
     * @var Sonata\AdminBundle\Builder\DatagridBuilderInterface
     */
    protected $datagridBuilder;

    /**
     * The router intance
     *
     * @var Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * The configuration pool
     * 
     * @var Pool
     */
    protected $configurationPool;

    protected $loaded = array(
        'form_fields' => false,
        'form_groups' => false,
        'list_fields' => false,
        'filter_fields' => false,
        'urls'        => false,
    );

    /**
     * return the doctrine class metadata handled by the Admin instance
     *
     * @return ClassMetadataInfo the doctrine class metadata handled by the Admin instance
     */
    public function getClassMetaData()
    {

        return $this->getModelManager()
            ->getClassMetaData($this->getClass());
    }

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

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
    }

    /**
     * @param string $class
     * @param string $baseControllerName
     */
    public function __construct($class, $baseControllerName)
    {
        $this->class = $class;
        $this->baseControllerName = $baseControllerName;
    }

    public function configure()
    {

        $this->uniqid = uniqid();
        
        if ($this->parentAssociationMapping) {
            if (!isset($this->getClassMetaData()->associationMappings[$this->parentAssociationMapping])) {
                throw new \RuntimeException(sprintf('The value set to `parentAssociationMapping` refer to a non existent association', $this->parentAssociationMapping));
            }
            $this->parentAssociationMapping = $this->getClassMetaData()->associationMappings[$this->parentAssociationMapping];
        }

        if (!$this->classnameLabel) {

            $this->classnameLabel = $this->urlize(substr($this->class, strrpos($this->class, '\\') + 1), '_');
        }

        $this->baseCodeRoute = $this->getCode();
    }

    public function configureUrls()
    {

    }

    public function preUpdate($object)
    {

    }

    public function postUpdate($object)
    {

    }

    public function preInsert($object)
    {

    }

    public function postInsert($object)
    {

    }

    /**
     * build the list FieldDescription array
     *
     * @return void
     */
    protected function buildListFieldDescriptions()
    {

        if ($this->loaded['list_fields']) {
            return;
        }

        $this->loaded['list_fields'] = true;

        $this->listFieldDescriptions = self::getBaseFields($this->list);

        // normalize field
        foreach ($this->listFieldDescriptions as $fieldDescription) {
            $this->getListBuilder()->fixFieldDescription($this, $fieldDescription);
        }

        if (!isset($this->listFieldDescriptions['_batch'])) {
            $fieldDescription = new FieldDescription();
            $fieldDescription->setOptions(array(
                'label' => 'batch',
                'code'  => '_batch',
                'type'  => 'batch',
            ));
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:list__batch.html.twig');
            $this->listFieldDescriptions = array( '_batch' => $fieldDescription ) + $this->listFieldDescriptions;
        }

        return $this->listFieldDescriptions;
    }

    /**
     * build the filter FieldDescription array
     *
     * @return void
     */
    public function buildFilterFieldDescriptions()
    {

        if ($this->loaded['filter_fields']) {
            return;
        }

        $this->loaded['filter_fields'] = true;

        $this->filterFieldDescriptions = self::getBaseFields($this->filter);

        // ok, try to limit to add parent filter
        $parentAssociationMapping = $this->getParentAssociationMapping();

        if ($parentAssociationMapping) {
            
            $fieldName = $parentAssociationMapping['fieldName'];
            $this->filterFieldDescriptions[$fieldName] = new FieldDescription;
            $this->filterFieldDescriptions[$fieldName]->setName($parentAssociationMapping['fieldName']);
            $this->filterFieldDescriptions[$fieldName]->setAssociationMapping($parentAssociationMapping);
        }

        foreach ($this->filterFieldDescriptions as $fieldDescription) {
            $this->getDatagridBuilder()->fixFieldDescription($this, $fieldDescription);
        }
    }

    /**
     * return the name of the parent related field, so the field can be use to set the default
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
    protected function buildFormFieldDescriptions()
    {

        if ($this->loaded['form_fields']) {
            return;
        }

        $this->loaded['form_fields'] = true;

        $this->formFieldDescriptions = self::getBaseFields($this->form);

        foreach ($this->formFieldDescriptions as $name => &$fieldDescription) {

            $this->getFormBuilder()->fixFieldDescription($this, $fieldDescription);

            // unset the identifier field as it is not required to update an object
            if ($fieldDescription->isIdentifier()) {
                unset($this->formFieldDescriptions[$name]);
            }
        }
    }

    /**
     * make sure the base fields are set in the correct format
     *
     * @param array $selectedFields
     * @return array
     */
    static public function getBaseFields($selectedFields)
    {

        $fields = array();

        // make sure we works with array
        foreach ($selectedFields as $name => $options) {

            $description = new FieldDescription;
            
            if (!is_array($options)) {
                $name = $options;
                $options = array();
            }

            $description->setName($name);
            $description->setOptions($options);

            $fields[$name] = $description;
        }

        return $fields;
    }

    /**
     * return the baseRoutePattern used to generate the routing information
     *
     * @throws RuntimeException
     * @return string the baseRoutePattern used to generate the routing information
     */
    public function getBaseRoutePattern()
    {
        if (!$this->baseRoutePattern) {
            preg_match('@([A-Za-z]*)\\\([A-Za-z]*)Bundle\\\(Entity|Document)\\\(.*)@', $this->getClass(), $matches);

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
     * return the baseRouteName used to generate the routing information
     *
     * @throws RuntimeException
     * @return string the baseRouteName used to generate the routing information
     */
    public function getBaseRouteName()
    {
        if (!$this->baseRouteName) {
            preg_match('@([A-Za-z]*)\\\([A-Za-z]*)Bundle\\\(Entity|Document)\\\(.*)@', $this->getClass(), $matches);

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
     */
    public function urlize($word, $sep = '_')
    {

        return strtolower(preg_replace('/[^a-z0-9_]/i', $sep.'$1', $word));
    }

    /**
     * return the class name handled by the Admin instance
     *
     * @return string the class name handled by the Admin instance
     */
    public function getClass()
    {
        return $this->class;
    }


    /**
     * return the list of batchs actions
     *
     * @return array the list of batchs actions
     */
    public function getBatchActions()
    {

        return array(
            'delete' => $this->trans('action_delete')
        );
    }

    /**
     * return the list of available urls
     *
     * @return array the list of available urls
     */
    public function getUrls($baseCode = '')
    {

        $this->buildUrls($baseCode);

        return $this->urls;
    }

    /**
     * return the parameter representing router id, ie: {id} or {childId}
     *
     * @return string
     */
    public function getRouterIdParameter()
    {
        return $this->isChild() ? '{childId}' : '{id}';
    }

    /**
     * return the parameter representing request id, ie: id or childId
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
    public function buildUrls()
    {
        if ($this->loaded['urls']) {
            return;
        }

        $this->loaded['urls'] = true;
        
        $this->urls =  array(
            $this->baseCodeRoute . '.list' => array(
                'name'      => $this->getBaseRouteName().'_list',
                'pattern'   => $this->getBaseRoutePattern().'/list',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':list',
                    '_sonata_admin' => $this->baseCodeRoute
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            ),
            $this->baseCodeRoute . '.create' => array(
                'name'      => $this->getBaseRouteName().'_create',
                'pattern'       => $this->getBaseRoutePattern().'/create',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':create',
                    '_sonata_admin' => $this->baseCodeRoute
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            ),
            $this->baseCodeRoute . '.edit' => array(
                'name'      => $this->getBaseRouteName().'_edit',
                'pattern'   => $this->getBaseRoutePattern().'/'.$this->getRouterIdParameter().'/edit',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':edit',
                    '_sonata_admin' => $this->baseCodeRoute
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            ),
            $this->baseCodeRoute . '.update' => array(
                'name'      => $this->getBaseRouteName().'_update',
                'pattern'       => $this->getBaseRoutePattern().'/update',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':update',
                    '_sonata_admin' => $this->baseCodeRoute
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            ),
            $this->baseCodeRoute . '.batch' => array(
                'name'      => $this->getBaseRouteName().'_batch',
                'pattern'       => $this->getBaseRoutePattern().'/batch',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':batch',
                    '_sonata_admin' => $this->baseCodeRoute
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            )
        );
        
        // add children urls
        foreach ($this->getChildren() as $children) {
            $this->urls = array_merge($this->urls, $children->getUrls());
        }

        $this->configureUrls();
    }

    /**
     * return the url defined by the $name
     *
     * @param  $name
     * @return bool
     */
    public function getUrl($name)
    {
        $urls = $this->getUrls();

        if (!isset($urls[$name])) {
            return false;
        }

        return $urls[$name];
    }

    /**
     * generate the url with the given $name
     *
     * @throws RuntimeException
     * @param  $name
     * @param array $params
     *
     * @return return a complete url
     */
    public function generateUrl($name, array $params = array())
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
            if (isset($params['id'])) {
                $params[$this->getIdParameter()] = $params['id'];
                unset($params['id']);
            }

            $params[$this->getParent()->getIdParameter()] = $this->request->get($this->getParent()->getIdParameter());
        }

        // if the admin is linked to a FieldDescription (ie, embedded widget)
        if ($this->hasParentFieldDescription()) {
            $params['uniqid']  = $this->getUniqid();
            $params['code']    = $this->getCode();
            $params['pcode']   = $this->getParentFieldDescription()->getAdmin()->getCode();
            $params['puniqid'] = $this->getParentFieldDescription()->getAdmin()->getUniqid();
        }

        if ($name == 'update' || substr($name, -7) == '|update') {
            $params['uniqid'] = $this->getUniqid();
            $params['code']   = $this->getCode();
        }
        
        $url = $this->getUrl($name);

        if (!$url) {
            throw new \RuntimeException(sprintf('unable to find the url `%s`', $name));
        }

        return $this->router->generate($url['name'], $params);
    }

    /**
     * return the list template
     *
     * @return string the list template
     */
    public function getListTemplate()
    {
        return 'SonataAdminBundle:CRUD:list.html.twig';
    }

    /**
     * return the edit template
     *
     * @return string the edit template
     */
    public function getEditTemplate()
    {
        return 'SonataAdminBundle:CRUD:edit.html.twig';
    }

    /**
     * return the reflection fields related to the classname
     *
     * @return array The reflection fields related to the classname
     */
    public function getReflectionFields()
    {
        return $this->getClassMetaData()->reflFields;
    }

    /**
     * return an instance of the related classname
     *
     * @return Object An instance of the related classname
     */
    public function getNewInstance()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     *
     * @return Form the base form
     */
    public function getBaseForm($object, $options = array())
    {
        return $this->getFormBuilder()->getBaseForm(
            $this->getUniqid(),
            $object,
            array_merge($this->formOptions, $options)
        );
    }

    /**
     *
     * @return Form the base form
     */
    public function getBaseDatagrid($values = array())
    {
        return new Datagrid(
            $this->getClass(),
            $this->getModelManager(),
            $values
        );
    }

    /**
     * attach an admin instance to the given FieldDescription
     *
     */
    public function attachAdminClass(FieldDescription $fieldDescription)
    {
        $pool = $this->getConfigurationPool();

        $admin = $pool->getAdminByClass($fieldDescription->getTargetEntity());
        if (!$admin) {
            throw new \RuntimeException(sprintf('You must define an Admin class for the `%s` field (targetEntity=%s)', $fieldDescription->getFieldName(), $fieldDescription->getTargetEntity()));
        }

        $fieldDescription->setAssociationAdmin($admin);
    }

    /**
     * return the target object
     *
     * @param  $id
     * @return
     */
    public function getObject($id)
    {

        return $this->getModelManager()
            ->find($this->getClass(), $id);
    }

    /**
     * build the form group array
     *
     * @return void
     */
    public function buildFormGroups()
    {

        if ($this->loaded['form_groups']) {
            return;
        }

        $this->loaded['form_groups'] = true;

        if (!$this->formGroups) {
            $this->formGroups = array(
                false => array('fields' => array_keys($this->getFormFieldDescriptions()))
            );
        }

        // normalize array
        foreach ($this->formGroups as $name => $group) {
            if (!isset($this->formGroups[$name]['collapsed'])) {
                $this->formGroups[$name]['collapsed'] = false;
            }
        }
    }

    /**
     * return a form depend on the given $object
     *
     * @param  $object
     * @return Symfony\Component\Form\Form
     */
    public function getForm($object, array $options = array())
    {

        // append parent object if any
        // todo : clean the way the Admin class can retrieve set the object
        if ($this->isChild() && $this->getParentAssociationMapping()) {
            $mapping = $this->getParentAssociationMapping();
            $parent = $this->getParent()->getObject($this->request->get($this->getParent()->getIdParameter()));

            $propertyPath = new \Symfony\Component\Form\PropertyPath($mapping['fieldName']);
            $propertyPath->setValue($object, $parent);
        }

        $form = $this->getBaseForm($object, $options);

        $mapper = new FormMapper($this->getFormBuilder(), $form, $this);

        $this->buildFormFieldDescriptions();

        $this->configureFormFields($mapper);

        foreach ($this->getFormFieldDescriptions() as $fieldDescription) {

            // do not add field already set in the configureFormField method
            if ($mapper->has($fieldDescription->getFieldName())) {
                continue;
            }

            $mapper->add($fieldDescription);
        }
        
        return $form;
    }

    /**
     * return a list depend on the given $object
     *
     * @param  $object
     * @return Symfony\Component\Datagrid\ListCollection
     */
    public function getList(array $options = array())
    {   

        $list = $this->getListBuilder()->getBaseList($options);

        $mapper = new ListMapper($this->getListBuilder(), $list, $this);

        $this->buildListFieldDescriptions();
        
        $this->configureListFields($mapper);

        foreach ($this->getListFieldDescriptions() as $fieldDescription) {

            // do not add field already set in the configureFormField method
            if ($mapper->has($fieldDescription->getFieldName())) {
                continue;
            }

            $mapper->add($fieldDescription);
        }

        return $list;
    }

    /**
     * return a list depend on the given $object
     *
     * @param  $object
     * @return Symfony\Component\Datagrid\Datagrid
     */
    public function getDatagrid()
    {

        $parameters = $this->request->query->all();

        $datagrid = $this->getBaseDatagrid($parameters);
        $datagrid->setMaxPerPage($this->maxPerPage);

        if ($this->isChild() && $this->getParentAssociationMapping()) {
            $mapping = $this->getParentAssociationMapping();
            $parameters[$mapping['fieldName']] = $this->request->get($this->getParent()->getIdParameter());
        }

        $datagrid->setValues($parameters);
        $mapper = new DatagridMapper($this->getDatagridBuilder(), $datagrid, $this);

        $this->buildFilterFieldDescriptions();
        $this->configureDatagridFilters($mapper);

        foreach ($this->getFilterFieldDescriptions() as $fieldDescription) {

            $mapper->add($fieldDescription);
        }

        return $datagrid;
    }

    /**
     * Build the side menu related to the current action
     *
     * @return MenuItem|false
     */
    public function getSideMenu($action)
    {

        return false;
    }

    /**
     * return the root code
     *
     * @return string the root code
     */
    public function getRootCode()
    {
        return $this->getRoot()->getCode();
    }

    /**
     * return the master admin
     *
     * @return Admin the root admin class
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

    public function setFormGroups($formGroups)
    {
        $this->formGroups = $formGroups;
    }

    public function getFormGroups()
    {
        $this->buildFormGroups();

        return $this->formGroups;
    }

    /**
     * set the parent FieldDescription
     *
     * @param FieldDescription $parentFieldDescription
     * @return void
     */
    public function setParentFieldDescription(FieldDescription $parentFieldDescription)
    {
        $this->parentFieldDescription = $parentFieldDescription;
    }

    /**
     *
     * @return FieldDescription the parent field description
     */
    public function getParentFieldDescription()
    {
        return $this->parentFieldDescription;
    }

    /**
     * return true if the Admin is linked to a parent FieldDescription
     *
     * @return bool
     */
    public function hasParentFieldDescription()
    {
        return $this->parentFieldDescription instanceof FieldDescription;
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
     * return the subject, if none is set try to load one from the request
     *
     * @return $object the subject 
     */
    public function getSubject()
    {
        if ($this->subject === null) {

            $id = $this->request->get($this->getIdParameter());
            if (!is_numeric($id)) {
                $this->subject = false;
            } else {
                $this->subject = $this->getModelManager()->find(
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
        $this->buildFormFieldDescriptions();
        
        return $this->formFieldDescriptions;
    }

    /**
     * return the form FieldDescription with the given $name
     *
     * @param string $name
     * @return FieldDescription
     */
    public function getFormFieldDescription($name) 
    {
        return $this->hasFormFieldDescription($name) ? $this->formFieldDescriptions[$name] : null;
    }

    /**
     * return true if the admin has a FieldDescription with the given $name
     *
     * @param string $name
     * @return bool
     */
    public function hasFormFieldDescription($name)
    {
        $this->buildFormFieldDescriptions();

        return array_key_exists($name, $this->formFieldDescriptions) ? true : false;
    }

    /**
     * add a FieldDescription
     *
     * @param string $name
     * @param FieldDescription $fieldDescription
     * @return void
     */
    public function addFormFieldDescription($name, FieldDescription $fieldDescription)
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
     * return the collection of list FieldDescriptions
     *
     * @return array
     */
    public function getListFieldDescriptions()
    {

        $this->buildListFieldDescriptions();
        
        return $this->listFieldDescriptions;
    }

    /**
     * return a list FieldDescription
     *
     * @param string $name
     * @return FieldDescription
     */
    public function getListFieldDescription($name) {

        return $this->hasListFieldDescription($name) ? $this->listFieldDescriptions[$name] : null;
    }

    /**
     * return true if the list FieldDescription exists
     *
     * @param string $name
     * @return bool
     */
    public function hasListFieldDescription($name)
    {
        $this->buildListFieldDescriptions();

        return array_key_exists($name, $this->listFieldDescriptions) ? true : false;
    }

    /**
     * add a list FieldDescription
     *
     * @param string $name
     * @param FieldDescription $fieldDescription
     * @return void
     */
    public function addListFieldDescription($name, FieldDescription $fieldDescription)
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
     * return a filter FieldDescription
     *
     * @param string $name
     * @return array|null
     */
    public function getFilterFieldDescription($name) {

        return $this->hasFilterFieldDescription($name) ? $this->filterFieldDescriptions[$name] : null;
    }

    /**
     * return true if the filter FieldDescription exists
     *
     * @param string $name
     * @return bool
     */
    public function hasFilterFieldDescription($name)
    {
        $this->buildFilterFieldDescriptions();

        return array_key_exists($name, $this->filterFieldDescriptions) ? true : false;
    }

    /**
     * add a filter FieldDescription
     *
     * @param string $name
     * @param FieldDescription $fieldDescription
     * @return void
     */
    public function addFilterFieldDescription($name, FieldDescription $fieldDescription)
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
     * return the filter FieldDescription collection
     *
     * @param array filter FieldDescription collection
     */
    public function getFilterFieldDescriptions()
    {
        $this->buildFilterFieldDescriptions();
        
        return $this->filterFieldDescriptions;
    }

    /**
     * add an Admin child to the current one
     *
     * @param string $code
     * @param Admin $child
     * @return void
     */
    public function addChild($code, AdminInterface $child)
    {
        $this->children[$code] = $child;
        
        $child->setCode($code);
        $child->setBaseCodeRoute($this->getCode().'|'.$code);
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
     * return an collection of admin children
     *
     * @return array list of Admin children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * return an admin child with the given $code
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
     * @param Admin $parent
     * @return void
     */
    public function setParent(AdminInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * get the Parent Admin
     *
     * @return Admin|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * return true if the Admin class has an Parent Admin defined
     *
     * @return boolean
     */
    public function isChild()
    {
        return $this->parent instanceof AdminInterface;
    }

    /**
     * return true if the admin has children, false otherwise
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
     * return the uniqid
     * 
     * @return integer
     */
    public function getUniqid()
    {
        return $this->uniqid;
    }

    /**
     * return the classname label
     *
     * @return string the classname label
     */
    public function getClassnameLabel()
    {

        return $this->classnameLabel;
    }

    /**
     * generate the breadcrumbs array
     *
     * @param  $action
     * @param \Knplabs\MenuBundle\MenuItem|null $menu
     * @return array the breadcrumbs
     */
    public function getBreadcrumbs($action, MenuItem $menu = null)
    {
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

            return $childAdmin->getBreadcrumbs($action, $child);
        
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

        return $breadcrumbs;
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
     * return the current child status
     *
     * @return bool
     */
    public function getCurrentChild()
    {
        return $this->currentChild;
    }

    /**
     * return the current child admin instance
     *
     * @return Admin|null the current child admin instance
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
     * return the translation domain
     *
     * @return string the translation domain
     */
    public function getTranslationDomain()
    {
        return $this->translationDomain;
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setFormBuilder(FormBuilderInterface $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    public function getFormBuilder()
    {
        return $this->formBuilder;
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

    public function setConfigurationPool(Pool $configurationPool)
    {
        $this->configurationPool = $configurationPool;
    }

    public function getConfigurationPool()
    {
        return $this->configurationPool;
    }

    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setModelManager($modelManager)
    {
        $this->modelManager = $modelManager;
    }

    public function getModelManager()
    {
        return $this->modelManager;
    }

    public function setCode($code)
    {
        $this->code = $code;
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
}