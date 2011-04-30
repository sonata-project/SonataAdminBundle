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

    public function __construct(FieldDescriptionInterface $fieldDescription)
    {
        $this->name         = $fieldDescription->getName();
        $this->fieldDescription  = $fieldDescription;
        $this->options      = array_replace(
            $this->getDefaultOptions(),
            $this->fieldDescription->getOption('filter_options', array())
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getField()
    {
        if (!$this->field) {
            throw new \RuntimeException('No field attached');
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
        if (array_keys($this->options, $name)) {
            return $this->options[$name];
        }

        return $default;
    }
}