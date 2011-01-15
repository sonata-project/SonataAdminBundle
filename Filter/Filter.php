<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\BaseApplicationBundle\Filter;

use Symfony\Component\Form\Configurable;
use Bundle\Sonata\BaseApplicationBundle\Admin\FieldDescription;
use Doctrine\ORM\QueryBuilder;

abstract class Filter extends Configurable
{

    protected $description = array();

    protected $name = null;

    protected $field = null;

    protected $value = null;

    public function __construct($name, FieldDescription $description)
    {
        $this->name         = $name;
        $this->description  = $description;

        parent::__construct($description->getOption('filter_options', array()));

        $this->field        = $this->getFormField();
    }

    /**
     *
     * set the object description
     *
     */
    public function setDescription(array $description)
    {
        $this->description = $description;
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

    public function apply(QueryBuilder $queryBuilder, $value)
    {
        $this->value = $value;

        $this->field->bind($value);

        list($alias, $field) = $this->association($queryBuilder, $this->field->getData());

        $this->filter($queryBuilder, $alias, $field, $this->field->getData());
    }

    protected function association(QueryBuilder $queryBuilder, $value)
    {
        if($value) {

            if($this->description->getType() == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
                $queryBuilder->leftJoin(
                    sprintf('%s.%s', $queryBuilder->getRootAlias(), $this->description->getFieldName()),
                    $this->getName()
                );

                // todo : use the metadata information to find the correct column name
                return array($this->getName(), 'id');
            }
        }
        
        return array($queryBuilder->getRootAlias(), $this->description->getFieldName());
    }

    /**
     * apply the filter to the QueryBuilder instance
     *
     * @abstract
     * @param  $query
     * @param  $value
     * @param  $alias the root alias 
     * @return void
     */
    abstract public function filter(QueryBuilder $queryBuilder, $alias, $field, $value);

    /**
     * get the related form field filter
     *
     * @abstract
     * @return Field
     */
    abstract public function getFormField();

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