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
    protected $options = [];

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
     * @var array[] cached object field getters
     */
    private static $fieldGetters = [];

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
            $this->setFieldName(substr(strrchr('.'.$name, '.'), 1));
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
            $options['link_parameters'] = [];
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

    public function setParent(AdminInterface $parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getAssociationMapping()
    {
        return $this->associationMapping;
    }

    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    public function getParentAssociationMappings()
    {
        return $this->parentAssociationMappings;
    }

    public function setAssociationAdmin(AdminInterface $associationAdmin)
    {
        $this->associationAdmin = $associationAdmin;
        $this->associationAdmin->setParentFieldDescription($this);
    }

    public function getAssociationAdmin()
    {
        return $this->associationAdmin;
    }

    public function hasAssociationAdmin()
    {
        return null !== $this->associationAdmin;
    }

    public function getFieldValue($object, $fieldName)
    {
        if ($this->isVirtual() || null === $object) {
            return;
        }

        $getters = [];
        $parameters = [];

        // prefer method name given in the code option
        if ($this->getOption('code')) {
            $getters[] = $this->getOption('code');
        }
        // parameters for the method given in the code option
        if ($this->getOption('parameters')) {
            $parameters = $this->getOption('parameters');
        }

        if (\is_string($fieldName) && '' !== $fieldName) {
            if ($this->hasCachedFieldGetter($object, $fieldName)) {
                return $this->callCachedGetter($object, $fieldName, $parameters);
            }

            $camelizedFieldName = Inflector::classify($fieldName);

            $getters[] = 'get'.$camelizedFieldName;
            $getters[] = 'is'.$camelizedFieldName;
            $getters[] = 'has'.$camelizedFieldName;
        }

        foreach ($getters as $getter) {
            if (method_exists($object, $getter) && \is_callable([$object, $getter])) {
                $this->cacheFieldGetter($object, $fieldName, 'getter', $getter);

                return \call_user_func_array([$object, $getter], $parameters);
            }
        }

        if (method_exists($object, '__call')) {
            $this->cacheFieldGetter($object, $fieldName, 'call');

            return \call_user_func_array([$object, '__call'], [$fieldName, $parameters]);
        }

        if (isset($object->{$fieldName})) {
            $this->cacheFieldGetter($object, $fieldName, 'var');

            return $object->{$fieldName};
        }

        throw new NoValueException(sprintf('Unable to retrieve the value of `%s`', $this->getName()));
    }

    public function setAdmin(AdminInterface $admin)
    {
        $this->admin = $admin;
    }

    public function getAdmin()
    {
        return $this->admin;
    }

    public function mergeOption($name, array $options = [])
    {
        if (!isset($this->options[$name])) {
            $this->options[$name] = [];
        }

        if (!\is_array($this->options[$name])) {
            throw new \RuntimeException(sprintf('The key `%s` does not point to an array value', $name));
        }

        $this->options[$name] = array_merge($this->options[$name], $options);
    }

    public function mergeOptions(array $options = [])
    {
        $this->setOptions(array_merge_recursive($this->options, $options));
    }

    public function setMappingType($mappingType)
    {
        $this->mappingType = $mappingType;
    }

    public function getMappingType()
    {
        return $this->mappingType;
    }

    /**
     * Camelize a string.
     *
     * NEXT_MAJOR: remove this method.
     *
     * @static
     *
     * @param string $property
     *
     * @return string
     *
     * @deprecated Deprecated since version 3.1. Use \Doctrine\Common\Inflector\Inflector::classify() instead
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

    public function getHelp()
    {
        return $this->help;
    }

    public function getLabel()
    {
        return $this->getOption('label');
    }

    public function isSortable()
    {
        return false !== $this->getOption('sortable', false);
    }

    public function getSortFieldMapping()
    {
        return $this->getOption('sort_field_mapping');
    }

    public function getSortParentAssociationMapping()
    {
        return $this->getOption('sort_parent_association_mappings');
    }

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

    private function getFieldGetterKey($object, $fieldName)
    {
        if (!\is_string($fieldName)) {
            return null;
        }
        if (!\is_object($object)) {
            return null;
        }
        $components = [\get_class($object), $fieldName];
        $code = $this->getOption('code');
        if (\is_string($code) && '' !== $code) {
            $components[] = $code;
        }

        return implode('-', $components);
    }

    private function hasCachedFieldGetter($object, $fieldName)
    {
        return isset(
            self::$fieldGetters[$this->getFieldGetterKey($object, $fieldName)]
        );
    }

    private function callCachedGetter($object, $fieldName, array $parameters = [])
    {
        $getterKey = $this->getFieldGetterKey($object, $fieldName);
        if ('getter' === self::$fieldGetters[$getterKey]['method']) {
            return \call_user_func_array(
                [$object, self::$fieldGetters[$getterKey]['getter']],
                $parameters
            );
        } elseif ('call' === self::$fieldGetters[$getterKey]['method']) {
            return \call_user_func_array(
                [$object, '__call'],
                [$fieldName, $parameters]
            );
        }

        return $object->{$fieldName};
    }

    private function cacheFieldGetter($object, $fieldName, $method, $getter = null)
    {
        $getterKey = $this->getFieldGetterKey($object, $fieldName);
        if (null !== $getterKey) {
            self::$fieldGetters[$getterKey] = [
                'method' => $method,
            ];
            if (null !== $getter) {
                self::$fieldGetters[$getterKey]['getter'] = $getter;
            }
        }
    }
}
