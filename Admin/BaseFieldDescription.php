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

use Doctrine\Common\Inflector\Inflector;
use Sonata\AdminBundle\Exception\NoValueException;

/**
 * A FieldDescription hold the information about a field. A typical
 * admin instance contains different collections of fields.
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
 *   - associated_tostring : (deprecated, use associated_property option)
 *                           the method to retrieve the "string" representation
 *                           of the collection element.
 *   - associated_property : property path to retrieve the "string" representation
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
 *   - field_type (o): the widget class to use to render the field
 *   - field_options (o): the options to give to the widget
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
     * @var string|int the type
     */
    protected $type;

    /**
     * @var string|int the original mapping type
     */
    protected $mappingType;

    /**
     * @var string the field name (of the form)
     */
    protected $fieldName;

    /**
     * @var array the ORM association mapping
     */
    protected $associationMapping;

    /**
     * @var array the ORM field information
     */
    protected $fieldMapping;

    /**
     * @var array the ORM parent mapping association
     */
    protected $parentAssociationMappings;

    /**
     * @var string the template name
     */
    protected $template;

    /**
     * @var array the option collection
     */
    protected $options = array();

    /**
     * @var AdminInterface|null the parent Admin instance
     */
    protected $parent = null;

    /**
     * @var AdminInterface the related admin instance
     */
    protected $admin;

    /**
     * @var AdminInterface the associated admin class if the object is associated to another entity
     */
    protected $associationAdmin;

    /**
     * @var string the help message to display
     */
    protected $help;

    /**
     * {@inheritdoc}
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        if (!$this->getFieldName()) {
            $this->setFieldName(substr(strrchr('.'.$name, '.'), 1));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * {@inheritdoc}
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

        // set default placeholder
        if (!isset($options['placeholder'])) {
            $options['placeholder'] = 'short_object_description_placeholder';
        }

        if (!isset($options['link_parameters'])) {
            $options['link_parameters'] = array();
        }

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
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
    public function getAssociationMapping()
    {
        return $this->associationMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentAssociationMappings()
    {
        return $this->parentAssociationMappings;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssociationAdmin(AdminInterface $associationAdmin)
    {
        $this->associationAdmin = $associationAdmin;
        $this->associationAdmin->setParentFieldDescription($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationAdmin()
    {
        return $this->associationAdmin;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAssociationAdmin()
    {
        return $this->associationAdmin !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldValue($object, $fieldName)
    {
        if ($this->isVirtual()) {
            return;
        }

        $camelizedFieldName = Inflector::classify($fieldName);

        $getters = array();
        $parameters = array();

        // prefer method name given in the code option
        if ($this->getOption('code')) {
            $getters[] = $this->getOption('code');
        }
        // parameters for the method given in the code option
        if ($this->getOption('parameters')) {
            $parameters = $this->getOption('parameters');
        }
        $getters[] = 'get'.$camelizedFieldName;
        $getters[] = 'is'.$camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return call_user_func_array(array($object, $getter), $parameters);
            }
        }

        if (method_exists($object, '__call')) {
            return call_user_func_array(array($object, '__call'), array($fieldName, $parameters));
        }

        if (isset($object->{$fieldName})) {
            return $object->{$fieldName};
        }

        throw new NoValueException(sprintf('Unable to retrieve the value of `%s`', $this->getName()));
    }

    /**
     * {@inheritdoc}
     */
    public function setAdmin(AdminInterface $admin)
    {
        $this->admin = $admin;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function mergeOptions(array $options = array())
    {
        $this->setOptions(array_merge_recursive($this->options, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function setMappingType($mappingType)
    {
        $this->mappingType = $mappingType;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingType()
    {
        return $this->mappingType;
    }

    /**
     * Camelize a string.
     *
     * @static
     *
     * @param string $property
     *
     * @return string
     *
     * @deprecated Deprecated since version 3.1. Use \Doctrine\Common\Inflector\Inflector::classify() instead.
     */
    public static function camelize($property)
    {
        @trigger_error(
            sprintf(
                'The %s method is deprecated since 3.1 and will be removed in 4.0. '.
                'Use \Doctrine\Common\Inflector\Inflector::classify() instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return Inflector::classify($property);
    }

    /**
     * Defines the help message.
     *
     * @param string $help
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }

    /**
     * {@inheritdoc}
     */
    public function isSortable()
    {
        return false !== $this->getOption('sortable', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortFieldMapping()
    {
        return $this->getOption('sort_field_mapping');
    }

    /**
     * {@inheritdoc}
     */
    public function getSortParentAssociationMapping()
    {
        return $this->getOption('sort_parent_association_mappings');
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationDomain()
    {
        return $this->getOption('translation_domain') ?: $this->getAdmin()->getTranslationDomain();
    }

    /**
     * Return true if field is virtual.
     *
     * @return bool
     */
    public function isVirtual()
    {
        return false !== $this->getOption('virtual_field', false);
    }
}
