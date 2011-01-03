<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\BaseApplicationBundle\Tool;

use Bundle\BaseApplicationBundle\Tool\DoctrinePager as Pager;

use Bundle\BaseApplicationBundle\Filter\StringFilter;
use Bundle\BaseApplicationBundle\Filter\BooleanFilter;
use Bundle\BaseApplicationBundle\Filter\IntegerFilter;
use Bundle\BaseApplicationBundle\Filter\CallbackFilter;
use Bundle\BaseApplicationBundle\Filter\ChoiceFilter;




class Datagrid
{

    protected $classname;

    protected $entity_manager;

    /**
     * The filter descriptions
     * @var array
     */
    protected $filter_fields = array();

    /**
     *
     * The filter instances
     * @var array
     */
    protected $filters;

    protected $values;

    public function __construct($classname, $entity_manager)
    {
        $this->classname = $classname;
        $this->entity_manager = $entity_manager;
    }

    public function getClassMetaData()
    {
        $em             = $this->getEntityManager();

        return $em->getClassMetaData($this->getClassname());
    }

    public function getPager($values)
    {
        $pager = new Pager($this->getClassname());

        $pager->setQueryBuilder($this->getQueryBuilder($values));
        $pager->setPage(isset($values['page']) ? $values['page'] : 1);
        $pager->init();

        return $pager;
    }

    public function getResults()
    {
        $pager = $this->getPager($this->getValues());

        return $pager->getResults();
    }

    public function getBaseQueryBuilder()
    {
        $em             = $this->getEntityManager();
        $repository     = $em->getRepository($this->getClassname());

        $query_buidler = $repository
            ->createQueryBuilder('o');

        return $query_buidler;
    }

    public function getQueryBuilder($values = array())
    {

        $query_buidler = $this->getBaseQueryBuilder();

        foreach($this->getFilters() as $name => $filter) {

            $value = isset($values[$name]) ? $values[$name] : null;

            $filter->apply($query_buidler, $value);
        }

        return $query_buidler;
    }

    public function setClassname($classname)
    {
        $this->classname = $classname;
    }

    public function getClassname()
    {
        return $this->classname;
    }

    public function setEntityManager($entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    public function getEntityManager()
    {
        return $this->entity_manager;
    }

    public function setFilterFields($filter_fields)
    {
        $this->filter_fields = $filter_fields;
    }

    public function getFilterFields()
    {
        return $this->filter_fields;
    }


    public function buildFilterFields()
    {
        $this->filter_fields = \Bundle\BaseApplicationBundle\Admin\Admin::getBaseFields($this->getClassMetaData(), $this->filter_fields);

        foreach($this->filter_fields as $name => $options) {

            $this->filter_fields[$name]['code'] = $name;

            // set the label if filter_fields is set
            if(!isset($this->filter_fields[$name]['label']))
            {
                $this->filter_fields[$name]['label'] = $name;
            }

            // set the default type if none is set
            if(!isset($this->filter_fields[$name]['type'])) {
                $this->filter_fields[$name]['type'] = 'string';
            }

            // fix template for mapping
            if($this->filter_fields[$name]['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE) {
                $this->filter_fields[$name]['template']       = 'BaseApplicationBundle:CRUD:filter_many_to_one.twig';
            }

            if($this->filter_fields[$name]['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
                $this->filter_fields[$name]['template']       = 'BaseApplicationBundle:CRUD:filter_many_to_many.twig';
            }

            // define the default template
            if(!isset($this->filter_fields[$name]['template'])) {
                $this->filter_fields[$name]['template'] = sprintf('BaseApplicationBundle:CRUD:filter_%s.twig', $this->filter_fields[$name]['type']);
            }

            // define the default template for identifier field
            if(isset($this->filter_fields[$name]['id'])) {
                $this->filter_fields[$name]['template'] = 'BaseApplicationBundle:CRUD:filter_identifier.twig';
            }

            if(!isset($this->filter_fields[$name]['filter_value'])) {
                $this->filter_fields[$name]['filter_value'] = null;
            }

            // options given to the Filter object
            if(!isset($this->filter_fields[$name]['filter_options'])) {
                $this->filter_fields[$name]['filter_options'] = array();
            }

            // options given to the Form Field object
            if(!isset($this->filter_fields[$name]['filter_field_options'])) {
                $this->filter_fields[$name]['filter_field_options'] = array();
            }

            if(!isset($this->filter_fields[$name]['name']))
            {
                $this->filter_fields[$name]['name'] = $name;
            }
        }

        $this->configureFilterFields();
    }

    public function getChoices($description)
    {
        $targets = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('t')
            ->from($description['targetEntity'], 't')
            ->getQuery()
            ->execute();

        $choices = array();
        foreach($targets as $target) {
            // todo : puts this into a configuration option and use reflection
            foreach(array('getTitle', 'getName', '__toString') as $getter) {
                if(method_exists($target, $getter)) {
                    $choices[$target->getId()] = $target->$getter();
                    break;
                }
            }
        }

        return $choices;
    }

    public function getFilterInstance($description)
    {

        if(!isset($description['type'])) {

            return false;
        }

        $name = $description['name'];

        switch($description['type']) {

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY:

                $description['filter_field_options']['choices'] = $this->getChoices($description);

                $filter = new ChoiceFilter($name, $description);

                break;

            case 'string':
            case 'text':
                $filter = new StringFilter($name, $description);
                break;

            case 'boolean':
                $filter = new BooleanFilter($name, $description);
                break;

            case 'integer':
                $filter = new IntegerFilter($name, $description);
                break;

            case 'callback':
                $filter = new CallbackFilter($name, $description);
                break;

            default:
                return false;
        }

        return $filter;
    }

    public function configureFilterFields()
    {
        
    }

    public function getFilters()
    {

        if(!$this->filters) {
            foreach($this->filter_fields as $name => $description) {
                $filter = $this->getFilterInstance($this->filter_fields[$name]);

                if($filter) {
                    $this->filters[$name] = $filter;
                }
            }
        }

        return $this->filters;
    }

    public function setValues($values)
    {
        $this->values = $values;
    }

    public function getValues()
    {
        return $this->values;
    }
}