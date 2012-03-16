<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\NoValueException;

/**
 * A FieldDescription hold the information about a field. A typical
 * admin instance contains different collections of fields
 *
 * - form: used by the form
 * - list: used by the list
 * - filter: used by the list filter
 *
 * Some options are global across the different contexts, other are
 * context specifics.
 *
 * Global options :
 *   - type (m): define the field type (use to tweak the form or the list)
 *   - template (o) : the template used to render the field
 *   - name (o) : the name used (label in the form, title in the list)
 *   - link_parameters (o) : add link parameter to the related Admin class when
 *                           the Admin.generateUrl is called
 *   - code : the method name to retrieve the related value
 *   - associated_tostring : the method to retrieve the "string" representation
 *                           of the collection element.
 *
 * Form Field options :
 *   - field_type (o): the widget class to use to render the field
 *   - field_options (o): the options to give to the widget
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
 *   - options (o): options given to the Filter object
 *   - field_options (o): options given to the filter field object
 *   - field_type (o): options given to the filter field object
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseFieldDescription implements FieldDescriptionInterface
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
     * @var Admin|null the parent Admin instance
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

    /**
     * @var string the help message to display
     */
    protected $help;

    /**
     * set the field name
     *
     * @param string $fieldName
     * @return void
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * return the field name
     *
     * @return string the field name
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set the name
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;

        if (!$this->getFieldName()) {
            $this->setFieldName($name);
        }
    }

    /**
     * Return the name, the name can be used as a form label or table header
     *
     * @return string the name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the value represented by the provided name
     *
     * @param string $name
     * @param null $default
     * @return array|null the value represented by the provided name
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * Define an option, an option is has a name and a value
     *
     * @param string $name
     * @param mixed $value
     * @return void set the option value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Define the options value, if the options array contains the reserved keywords
     *   - type
     *   - template
     *   - help
     *
     * Then the value are copied across to the related property value
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
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

        // set help if provided
        if (isset($options['help'])) {
            $this->setHelp($options['help']);
            unset($options['help']);
        }

        $this->options = $options;
    }

    /**
     * return options
     *
     * @return array options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * return the template used to render the field
     *
     * @param string $template
     * @return void
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * return the template name
     *
     * @return string the template name
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * return the field type, the type is a mandatory field as it used to select the correct template
     * or the logic associated to the current FieldDescription object
     *
     * @param string $type
     * @return void the field type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * return the type
     *
     * @return int|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * set the parent Admin (only used in nested admin)
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $parent
     * @return void
     */
    public function setParent(AdminInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * return the parent Admin (only used in nested admin)
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * return the association mapping definition
     *
     * @return array
     */
    public function getAssociationMapping()
    {
        return $this->associationMapping;
    }

    /**
     * return the field mapping definition
     *
     * @return array the field mapping definition
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * set the association admin instance (only used if the field is linked to an Admin)
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $associationAdmin the associated admin
     */
    public function setAssociationAdmin(AdminInterface $associationAdmin)
    {
        $this->associationAdmin = $associationAdmin;
        $this->associationAdmin->setParentFieldDescription($this);
    }

    /**
     * return the associated Admin instance (only used if the field is linked to an Admin)
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getAssociationAdmin()
    {
        return $this->associationAdmin;
    }

    /**
     *
     * @return boolean
     */
    public function hasAssociationAdmin()
    {
        return $this->associationAdmin !== null;
    }

    /**
     * return the value linked to the description
     *
     * @param  $object
     * @return bool|mixed
     */
    public function getValue($object)
    {
        $camelizedFieldName = self::camelize($this->getFieldName());

        $getters = array();
        // prefer method name given in the code option
        if ($this->getOption('code')) {
            $getters[] = $this->getOption('code');
        }
        $getters[] = 'get'.$camelizedFieldName;
        $getters[] = 'is'.$camelizedFieldName;


        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return call_user_func(array($object, $getter));
            }
        }

        throw new NoValueException(sprintf('Unable to retrieve the value of `%s`', $this->getName()));
    }

    /**
     * set the admin class linked to this FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @return void
     */
    public function setAdmin(AdminInterface $admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return \Sonata\AdminBundle\Admin\AdminInterface the admin class linked to this FieldDescription
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * merge option values related to the provided option name
     *
     * @throws \RuntimeException
     * @param string $name
     * @param array $options
     * @return void
     */
    public function mergeOption($name, array $options = array())
    {
        if (!isset($this->options[$name])) {
            $this->options[$name] = array();
        }

        if (!is_array($this->options[$name])) {
            throw new \RuntimeException(sprintf('The key `%s` does not point to an array value', $name));
        }

        $this->options[$name] = array_merge($this->options[$name], $options);
    }

    /**
     * merge options values
     *
     * @param array $options
     * @return void
     */
    public function mergeOptions(array $options = array())
    {
        $this->setOptions(array_merge_recursive($this->options, $options));
    }

    /**
     * set the original mapping type (only used if the field is linked to an entity)
     *
     * @param string|int $mappingType
     * @return void
     */
    public function setMappingType($mappingType)
    {
        $this->mappingType = $mappingType;
    }

    /**
     * return the mapping type
     *
     * @return int|string
     */
    public function getMappingType()
    {
        return $this->mappingType;
    }

    /**
     * Camelize a string
     *
     * @static
     * @param string $property
     * @return string
     */
    public static function camelize($property)
    {
        return preg_replace(array('/(^|_| )+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }

    /**
     * Defines the help message
     *
     * @param $string help
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * return the label to use for the current field
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }
}
