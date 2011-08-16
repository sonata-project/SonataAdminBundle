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

abstract class Filter implements FilterInterface
{
    protected $fieldDescription = array();

    protected $name = null;

    protected $field = null;

    protected $value = null;

    protected $options = array();

    public function setFieldDescription(FieldDescriptionInterface $fieldDescription)
    {
        $this->name               = $fieldDescription->getName();
        $this->fieldDescription   = $fieldDescription;
    }

    public function initialize(array $options = array())
    {
        $this->options = array_replace($this->getDefaultOptions(), $options);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getField()
    {
        if (!$this->field) {
            throw new \RuntimeException(sprintf('No field instance attached for the filter `%s`', $this->name));
        }

        return $this->field;
    }

    /**
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getFieldDescription()
    {
        return $this->fieldDescription;
    }

    public function getDefaultOptions()
    {
        return array();
    }

    public function getOption($name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }
}