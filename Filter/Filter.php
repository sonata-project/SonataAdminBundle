<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter;

use Sonata\AdminBundle\Filter\FilterInterface;

abstract class Filter implements FilterInterface
{
    protected $name = null;

    protected $value = null;

    protected $options = array();

    protected $condition;

    const CONDITION_OR = 'OR';

    const CONDITION_AND = 'AND';

    /**
     * @param string $name
     * @param array $options
     */
    public function initialize($name, array $options = array())
    {
        $this->name = $name;
        $this->setOptions($options);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        /* Symfony default form class sadly can't handle
           form element with dots in its name (when data
           get bound, the default dataMapper is a PropertyPathMapper).
           So use this trick to avoid any issue.
        */
        return str_replace('.','~',$this->name);
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @return string
     */
    public function getFieldType()
    {
        return $this->getOption('field_type', 'text');
    }

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        return $this->getOption('field_options', array('required' => false));
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }

    /**
     * @param $label
     */
    public function setLabel($label)
    {
        $this->setOption('label', $label);
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        $fieldName = $this->getOption('field_name');

        if (!$fieldName) {
            throw new \RunTimeException(sprintf('The option `field_name` must be set for field : `%s`', $this->getName()));
        }

        return $fieldName;
    }

    /**
     * @return array of mappings
     */
    public function getParentAssociationMappings()
    {
        return $this->getOption('parent_association_mappings', array());
    }

    /**
     * @return array field mapping
     */
    public function getFieldMapping()
    {
        $fieldMapping = $this->getOption('field_mapping');

        if (!$fieldMapping) {
            throw new \RunTimeException(sprintf('The option `field_mapping` must be set for field : `%s`', $this->getName()));
        }

        return $fieldMapping;
    }

    /**
     * @return array association mapping
     */
    public function getAssociationMapping()
    {
        $associationMapping = $this->getOption('association_mapping');

        if (!$associationMapping) {
            throw new \RunTimeException(sprintf('The option `association_mapping` must be set for field : `%s`', $this->getName()));
        }

        return $associationMapping;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $value
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function isActive()
    {
        $values = $this->getValue();
        return ! empty($values['value']);
    }

    /**
     * @param $condition
     * @return void
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
