<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter;

use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Filter implements FilterInterface
{
    /**
     * @var string|null
     */
    protected $name = null;

    /**
     * @var mixed|null
     */
    protected $value = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $condition;

    public function initialize($name, array $options = [])
    {
        $this->name = $name;
        $this->setOptions($options);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFormName()
    {
        /*
           Symfony default form class sadly can't handle
           form element with dots in its name (when data
           get bound, the default dataMapper is a PropertyPathMapper).
           So use this trick to avoid any issue.
        */

        return str_replace('.', '__', $this->name);
    }

    public function getOption($name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function getFieldType()
    {
        return $this->getOption('field_type', TextType::class);
    }

    public function getFieldOptions()
    {
        return $this->getOption('field_options', ['required' => false]);
    }

    public function getFieldOption($name, $default = null)
    {
        if (isset($this->options['field_options'][$name]) && is_array($this->options['field_options'])) {
            return $this->options['field_options'][$name];
        }

        return $default;
    }

    public function setFieldOption($name, $value)
    {
        $this->options['field_options'][$name] = $value;
    }

    public function getLabel()
    {
        return $this->getOption('label');
    }

    public function setLabel($label)
    {
        $this->setOption('label', $label);
    }

    public function getFieldName()
    {
        $fieldName = $this->getOption('field_name');

        if (!$fieldName) {
            throw new \RuntimeException(sprintf('The option `field_name` must be set for field: `%s`', $this->getName()));
        }

        return $fieldName;
    }

    public function getParentAssociationMappings()
    {
        return $this->getOption('parent_association_mappings', []);
    }

    public function getFieldMapping()
    {
        $fieldMapping = $this->getOption('field_mapping');

        if (!$fieldMapping) {
            throw new \RuntimeException(sprintf('The option `field_mapping` must be set for field: `%s`', $this->getName()));
        }

        return $fieldMapping;
    }

    public function getAssociationMapping()
    {
        $associationMapping = $this->getOption('association_mapping');

        if (!$associationMapping) {
            throw new \RuntimeException(sprintf('The option `association_mapping` must be set for field: `%s`', $this->getName()));
        }

        return $associationMapping;
    }

    /**
     * Set options.
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge(
            ['show_filter' => null, 'advanced_filter' => true],
            $this->getDefaultOptions(),
            $options
        );
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set value.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function isActive()
    {
        $values = $this->getValue();

        return isset($values['value'])
            && false !== $values['value']
            && '' !== $values['value'];
    }

    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function getTranslationDomain()
    {
        return $this->getOption('translation_domain');
    }
}
