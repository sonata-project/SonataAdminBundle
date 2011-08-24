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

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormFactory;

abstract class Filter implements FilterInterface
{
    protected $name = null;

    protected $value = null;

    protected $options = array();

    protected $fieldDescription = array();

    public function setFieldDescription(FieldDescriptionInterface $fieldDescription)
    {
        $this->name               = $fieldDescription->getName();
        $this->fieldDescription   = $fieldDescription;
        $this->options            = array_merge($this->getDefaultOptions(), $fieldDescription->getOptions());
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getFieldDescription()
    {
        return $this->fieldDescription;
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * @param $name
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
     * @param $options
     * @return void
     */
    public function setOptions($options)
    {
        $this->options = $options;
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
     * @return null
     */
    public function getValue()
    {
        return $this->value;
    }
}