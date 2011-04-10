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
use Symfony\Component\Form\Configurable;
use Doctrine\ORM\QueryBuilder;

abstract class Filter extends Configurable implements FilterInterface
{

    protected $description = array();

    protected $name = null;

    protected $field = null;

    protected $value = null;

    public function __construct(FieldDescriptionInterface $fieldDescription)
    {
        $this->name         = $fieldDescription->getName();
        $this->description  = $fieldDescription;

        parent::__construct($fieldDescription->getOption('filter_options', array()));

        $this->field        = $this->getFormField();
    }

    /**
     * get the object description
     *
     * @return array
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function getField()
    {
        return $this->field;
    }

}