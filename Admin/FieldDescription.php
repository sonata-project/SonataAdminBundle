<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\BaseApplicationBundle\Admin;

class FieldDescription
{

    protected $name;

    protected $admin;

    protected $type;

    protected $fieldName;

    protected $associationAdmin;

    protected $associationMapping;

    protected $fieldMapping;

    protected $template;

    protected $options = array();

    protected $parent = null;

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

        if(!$this->getFieldName()) {
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

        $this->type      = $associationMapping['type'];
        $this->fieldName = $associationMapping['fieldName'];
    }

    public function getAssociationMapping()
    {
        return $this->associationMapping;
    }

    public function getTargetEntity()
    {
        if($this->associationMapping) {
            return $this->associationMapping['targetEntity'];
        }

        return null;
    }

    public function setFieldMapping(array $fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;

        $this->type      = $fieldMapping['type'];
        $this->fieldName = $fieldMapping['fieldName'];
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
            
        } else if($this->getOption('code') && method_exists($object, $this->getOption('code'))) {

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

}