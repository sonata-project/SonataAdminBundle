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

/**
 * A FieldDescription hold the information about a field. A typical
 * admin instance contains different collections of fields
 *
 * - formFields: used by the form
 * - listFields: used by the list
 * - filderFields: used by the list filter
 *
 * Some options are global accross the different contexts, other are
 * context specifics.
 *
 * Global options :
 *   - type (m): define the field type (use to tweak the form or the list)
 *   - template (o) : the template used to render the field
 *   - name (o) : the name used (label in the form, title in the list)
 *
 * Form Field options :
 *   - form_field_widget (o): the widget class to use to render the field
 *   - form_field_options (o): the options to give to the widget
 *   - edit (o) : list|inline|standard (only used for associated admin)
 *      - list : open a popup where the user can search, filter and click on one field
 *               to select one item
 *      - inline : the associated form admin is embedded into the current form
 *      - standard : the associated admin is created through a popup
 *
 * List Field options :
 *   - identifier (o): if set to true a link appear on to edit the element
 *
 * Filter Field options :
 *   - filter_options (o): options given to the Filter object
 *   - filter_field_options (o): options given to the filter field object
 *
 */
class FieldDescription
{

    /**
     * @var string the field name
     */
    protected $name;

    /**
     * @var string|integer the type
     */
    protected $type;

    /**
     * @var string|integer the original mapping type
     */
    protected $mappingType;

    /**
     * @var string the field name (of the form)
     */
    protected $fieldName;

    /**
     * @var array the Doctrine association mapping
     */
    protected $associationMapping;

    /**
     * @var array the Doctrine field information
     */
    protected $fieldMapping;

    /**
     * @var string the template name
     */
    protected $template;

    /**
     * @var array the option collection
     */
    protected $options = array();

    /**
     * @var admin|null the parent Admin instance
     */
    protected $parent = null;

    /**
     * @var Admin the related admin instance
     */
    protected $admin;
    
    /**
     * @var Admin the associated admin class if the object is associated to another entity
     */
    protected $associationAdmin;

    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function setName($name)
    {
        $this->name = $name;

        if (!$this->getFieldName()) {
            $this->setFieldName($name);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }
    
    public function setOptions($options)
    {

        // set the type if provided
        if (isset($options['type'])) {
            $this->setType($options['type']);
            unset($options['type']);
        }

        // remove property value
        if (isset($options['template'])) {
            $this->setTemplate($options['template']);
            unset($options['template']);
        }


        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setAssociationMapping(array $associationMapping)
    {
        $this->associationMapping = $associationMapping;

        $this->type         = $this->type ?: $associationMapping['type'];
        $this->mappingType  = $this->mappingType ?: $associationMapping['type'];
        $this->fieldName    = $associationMapping['fieldName'];
    }

    public function getAssociationMapping()
    {
        return $this->associationMapping;
    }

    public function getTargetEntity()
    {
        if ($this->associationMapping) {
            return $this->associationMapping['targetEntity'];
        }

        return null;
    }

    public function setFieldMapping(array $fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;

        $this->type         = $this->type ?: $fieldMapping['type'];
        $this->mappingType  = $this->mappingType ?: $fieldMapping['type'];
        $this->fieldName    = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * set the association admin instance
     *
     */
    public function setAssociationAdmin(Admin $associationAdmin)
    {
        $this->associationAdmin = $associationAdmin;
        $this->associationAdmin->setParentFieldDescription($this);
    }

    /**
     * return the associated Admin instance, only available when the property
     * is linked to an entity
     *
     * @return Admin
     */
    public function getAssociationAdmin()
    {
        return $this->associationAdmin;
    }

    /**
     *
     * return true if the FieldDescription is linked to an identifier field
     *
     * @return bool
     */
    public function isIdentifier()
    {

        return isset($this->fieldMapping['id']) ? $this->fieldMapping['id'] : false; 
    }

    /**
     * return the value linked to the description
     *
     * @param  $object
     * @return bool|mixed
     */
    public function getValue($object)
    {

        $value = false;

        $fieldName  = $this->getFieldName();
        $getter     = 'get'.self::camelize($fieldName);

        if (method_exists($object, $getter)) {

            $value = call_user_func(array($object, $getter));
            
        } else if ($this->getOption('code') && method_exists($object, $this->getOption('code'))) {

            $value = call_user_func(array($object, $this->getOption('code')));
        }

        return $value;
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    public function getAdmin()
    {
        return $this->admin;
    }

    public static function camelize($property)
    {
       return preg_replace(array('/(^|_)+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }

    public function mergeOption($name, array $options = array())
    {
        if(!isset($this->options[$name])) {
            $this->options[$name] = array();
        }

        $this->options[$name] = array_merge($this->options[$name], $options);
    }

    public function mergeOptions(array $options = array())
    {

        $this->setOptions(array_merge($this->options, $options));
    }

    public function setMappingType(string $mappingType)
    {
        $this->mappingType = $mappingType;
    }

    public function getMappingType()
    {
        return $this->mappingType;
    }


}