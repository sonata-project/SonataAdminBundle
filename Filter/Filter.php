<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\BaseApplicationBundle\Filter;

use Symfony\Component\Form\Configurable;

abstract class Filter extends Configurable
{

    protected $description = array();

    protected $name = null;

    protected $field = null;

    protected $value = null;

    public function __construct($name, array $description)
    {
        $this->name         = $name;
        $this->description  = $description;

        parent::__construct($description['filter_options']);

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

    public function apply($query_builder, $value)
    {
        $this->value = $value;

        $this->field->bind($value);

        list($alias, $field) = $this->association($query_builder, $this->field->getData());

        $this->filter($query_builder, $alias, $field, $this->field->getData());
    }

    protected function association($query_builder, $value)
    {
        if($value) {

            if($this->description['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
                $query_builder->leftJoin(
                    sprintf('%s.%s', $query_builder->getRootAlias(), $this->description['fieldName']),
                    $this->getName()
                );

                // todo : use the metadata information to find the correct column name
                return array($this->getName(), 'id');
            }
        }
        
        return array($query_builder->getRootAlias(), $this->description['fieldName']);
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
    abstract public function filter($query, $alias, $field, $value);

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