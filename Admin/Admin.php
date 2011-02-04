<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\Form;

use Sonata\BaseApplicationBundle\Form\FormMapper;
use Sonata\BaseApplicationBundle\Datagrid\ListMapper;
use Sonata\BaseApplicationBundle\Datagrid\DatagridMapper;
use Sonata\BaseApplicationBundle\Datagrid\Datagrid;


abstract class Admin extends ContainerAware
{
    protected $class;

    protected $list = array();

    protected $listFieldDescriptions = array();

    protected $form = array();
    
    protected $formFieldDescriptions = array();

    protected $filter = array();

    protected $filterFieldDescriptions = array(); 

    protected $maxPerPage = 25;

    protected $baseRouteName;

    protected $baseRoutePattern;

    protected $baseControllerName;

    protected $formGroups = false;

    /**
     *
     * @var array options to set to the form (ie, validation_groups)
     */
    protected $formOptions = array();

    // note : don't like this, but havn't find a better way to do it
    protected $configurationPool;

    protected $code;

    protected $label;

    protected $urls = array();

    protected $subject;

    /**
     * Reference the parent FieldDescription related to this admin
     * only set for FieldDescription which is associated to an Sub Admin instance
     *
     * FieldDescription
     */
    protected $parentFieldDescription;

    protected $loaded = array(
        'form_fields' => false,
        'form_groups' => false,
        'list_fields' => false,
        'filter_fields' => false,
        'urls'        => false,
    );

    protected $choicesCache = array();
    
    /**
     * return the entity manager
     *
     * @return EntityManager
     */
    abstract public function getEntityManager();

    abstract public function getListBuilder();

    abstract public function getFormBuilder();

    abstract public function getDatagridBuilder();

    abstract public function getClassMetaData();

    /**
     * This method can be overwritten to tweak the form construction, by default the form
     * is built by reading the FieldDescription
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $form)
    {

    }

    protected function configureListFields(ListMapper $list)
    {

    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {

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
     * build the field to use in the list view
     *
     * @return void
     */
    protected function buildListFieldDescriptions()
    {

        if ($this->loaded['list_fields']) {
            return;
        }

        $this->loaded['list_fields'] = true;

        $this->listFieldDescriptions = self::getBaseFields($this->getClassMetaData(), $this->list);

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
            $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:list__batch.twig.html');
            $this->listFieldDescriptions = array( '_batch' => $fieldDescription ) + $this->listFieldDescriptions;
        }

        return $this->listFieldDescriptions;
    }

    public function buildFilterFieldDescriptions()
    {
        if ($this->loaded['filter_fields']) {
            return;
        }

        $this->loaded['filter_fields'] = true;

        $this->filterFieldDescriptions = self::getBaseFields($this->getClassMetaData(), $this->filter);

        foreach ($this->filterFieldDescriptions as $fieldDescription) {
            $this->getDatagridBuilder()->fixFieldDescription($this, $fieldDescription);
        }
    }

    /**
     * Build the form's FieldDescription collection
     *
     * @return
     */
    protected function buildFormFieldDescriptions()
    {

        if ($this->loaded['form_fields']) {
            return;
        }

        $this->loaded['form_fields'] = true;

        $this->formFieldDescriptions = self::getBaseFields($this->getClassMetaData(), $this->form);

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
     * @param  $selected_fields
     * @return array
     */
    static public function getBaseFields($metadata, $selectedFields)
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
            if (preg_match('@(Application|Bundle)\\\([A-Za-z]*)\\\([A-Za-z]*)Bundle\\\(Entity|Document)\\\([A-Za-z]*)@', $this->getClass(), $matches)) {

                $this->baseRoutePattern = sprintf('/%s/%s/%s',
                    $this->urlize($matches[2], '-'),
                    $this->urlize($matches[3], '-'),
                    $this->urlize($matches[5], '-')
                );
            } else {
                throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', get_class($this)));
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
            if (preg_match('@(Application|Bundle)\\\([A-Za-z]*)\\\([A-Za-z]*)Bundle\\\(Entity|Document)\\\([A-Za-z]*)@', $this->getClass(), $matches)) {

                $this->baseRouteName = sprintf('admin_%s_%s_%s',
                    $this->urlize($matches[2]),
                    $this->urlize($matches[3]),
                    $this->urlize($matches[5])
                );
            } else {

                throw new \RuntimeException(sprintf('Please define a default `baseRouteName` value for the admin class `%s`', get_class($this)));
            }
        }

        return $this->baseRouteName;
    }

    public function urlize($word, $sep = '_')
    {

        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', $sep.'$1', $word));
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
            'delete' => 'action_delete'
        );
    }

    /**
     * return the list of available urls
     *
     * @return array the list of available urls
     */
    public function getUrls()
    {

        $this->buildUrls();

        return $this->urls;
    }

    public function buildUrls()
    {
        if ($this->loaded['urls']) {
            return;
        }

        $this->urls =  array(
            'list' => array(
                'name'      => $this->getBaseRouteName().'_list',
                'pattern'   => $this->getBaseRoutePattern().'/list',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':list'
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            ),
            'create' => array(
                'name'      => $this->getBaseRouteName().'_create',
                'pattern'       => $this->getBaseRoutePattern().'/create',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':create'
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            ),
            'edit' => array(
                'name'      => $this->getBaseRouteName().'_edit',
                'pattern'       => $this->getBaseRoutePattern().'/{id}/edit',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':edit'
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            ),
            'update' => array(
                'name'      => $this->getBaseRouteName().'_update',
                'pattern'       => $this->getBaseRoutePattern().'/update',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':update'
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            ),
            'batch' => array(
                'name'      => $this->getBaseRouteName().'_batch',
                'pattern'       => $this->getBaseRoutePattern().'/batch',
                'defaults'  => array(
                    '_controller' => $this->getBaseControllerName().':batch'
                ),
                'requirements' => array(),
                'options' => array(),
                'params'    => array(),
            )
        );

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
    public function generateUrl($name, $params = array())
    {
        $url = $this->getUrl($name);

        if (!$url) {
            throw new \RuntimeException(sprintf('unable to find the url `%s`', $name));
        }

        if (!is_array($params)) {
            $params = array();
        }

        return $this->container->get('router')->generate($url['name'], array_merge($url['params'], $params));
    }

    /**
     * return the list template
     *
     * @return string the list template
     */
    public function getListTemplate()
    {
        return 'SonataBaseApplicationBundle:CRUD:list.twig.html';
    }

    /**
     * return the edit template
     *
     * @return string the edit template
     */
    public function getEditTemplate()
    {
        return 'SonataBaseApplicationBundle:CRUD:edit.twig.html';
    }

    public function getReflectionFields()
    {
        return $this->getClassMetaData()->reflFields;
    }

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
        return $this->getFormBuilder()->getBaseForm($object, array_merge($this->formOptions, $options));
    }

    /**
     *
     * @return Form the base form
     */
    public function getBaseDatagrid()
    {
        return new Datagrid(
            $this->getClass(),
            $this->getEntityManager()
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
     * return the target objet
     *
     * @param  $id
     * @return
     */
    public function getObject($id)
    {

        return $this->getEntityManager()
            ->find($this->getClass(), $id);
    }

    public function buildFormGroups(Form $form)
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

        $form = $this->getBaseForm($object, $options);

        $mapper = new FormMapper($this->getFormBuilder(), $form, $this);

        foreach ($this->getFormFieldDescriptions() as $fieldDescription) {

            if (!$fieldDescription->getType()) {

                continue;
            }

            $mapper->add($fieldDescription);
        }

        $this->configureFormFields($mapper);

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

        foreach ($this->getListFieldDescriptions() as $fieldDescription) {

            if (!$fieldDescription->getType()) {

                continue;
            }

            $mapper->add($fieldDescription);
        }

        $this->configureListFields($mapper);

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
        
        $datagrid = $this->getBaseDatagrid();
        $datagrid->setMaxPerPage($this->maxPerPage);
        $datagrid->setValues($this->container->get('request')->query->all());

        $mapper = new DatagridMapper($this->getDatagridBuilder(), $datagrid, $this);

        foreach ($this->getFilterFieldDescriptions() as $fieldDescription) {

            if (!$fieldDescription->getType()) {

                continue;
            }

            $mapper->add($fieldDescription);
        }

        $this->configureDatagridFilters($mapper);

        return $datagrid;
    }

    /**
     *
     */
    public function getRootCode()
    {
        return $this->getRoot()->getCode();
    }

    /**
     * return the master admin      
     *
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

    public function setConfigurationPool($configurationPool)
    {
        $this->configurationPool = $configurationPool;
    }

    public function getConfigurationPool()
    {
        return $this->configurationPool;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
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

    public function getFormGroups(Form $form)
    {
        $this->buildFormGroups($form);

        return $this->formGroups;
    }

    public function setParentFieldDescription($parentFieldDescription)
    {
        $this->parentFieldDescription = $parentFieldDescription;
    }

    public function getParentFieldDescription()
    {
        return $this->parentFieldDescription;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getFormFieldDescriptions()
    {
        $this->buildFormFieldDescriptions();
        
        return $this->formFieldDescriptions;
    }

    public function getFormFieldDescription($name) {

        return $this->hasFormFieldDescription($name) ? $this->formFieldDescriptions[$name] : null;
    }

    public function hasFormFieldDescription($name)
    {
        $this->buildFormFieldDescriptions();

        return array_key_exists($name, $this->formFieldDescriptions) ? true : false;
    }

    public function addFormFieldDescription($name, FieldDescription $fieldDescription)
    {
        $this->formFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeFormFieldDescription($name)
    {
        unset($this->formFieldDescriptions[$name]);
    }

    public function getListFieldDescriptions()
    {

        $this->buildListFieldDescriptions();
        
        return $this->listFieldDescriptions;
    }

    public function getListFieldDescription($name) {

        return $this->hasListFieldDescription($name) ? $this->listFieldDescriptions[$name] : null;
    }

    public function hasListFieldDescription($name)
    {
        $this->buildListFieldDescriptions();

        return array_key_exists($name, $this->listFieldDescriptions) ? true : false;
    }

    public function addListFieldDescription($name, FieldDescription $fieldDescription)
    {
        $this->listFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeListFieldDescription($name)
    {
        unset($this->listFieldDescriptions[$name]);
    }

    public function getFilterFieldDescription($name) {

        return $this->hasFilterFieldDescription($name) ? $this->filterFieldDescriptions[$name] : null;
    }

    public function hasFilterFieldDescription($name)
    {
        $this->buildFilterFieldDescriptions();

        return array_key_exists($name, $this->filterFieldDescriptions) ? true : false;
    }

    public function addFilterFieldDescription($name, FieldDescription $fieldDescription)
    {
        $this->filterFieldDescriptions[$name] = $fieldDescription;
    }

    public function removeFilterFieldDescription($name)
    {
        unset($this->filterFieldDescriptions[$name]);
    }

    public function getFilterFieldDescriptions()
    {
        $this->buildFilterFieldDescriptions();
        
        return $this->filterFieldDescriptions;
    }
}