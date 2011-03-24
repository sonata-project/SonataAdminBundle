<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid\ORM;

use Sonata\AdminBundle\Datagrid\ORM\Pager;

class Datagrid
{

    protected $classname;

    protected $entityManager;

    /**
     * The filter descriptions
     * @var array
     */
    protected $filterFields = array();

    /**
     *
     * The filter instances
     * @var array
     */
    protected $filters = array();

    protected $values;

    protected $pager;

    protected $maxPerPage = 25;

    public function __construct($classname, $entityManager, $values = array())
    {
        $this->classname        = $classname;
        $this->entityManager    = $entityManager;
        $this->values           = $values;
    }

    public function getClassMetaData()
    {

        return $this->getEntityManager()
            ->getClassMetaData($this->getClassname());
    }

    public function getPager()
    {

        if (!$this->pager) {
            $this->pager = new Pager($this->getClassname(), $this->getMaxPerPage());

            $this->pager->setQueryBuilder($this->getQueryBuilder($this->values));
            $this->pager->setPage(isset($this->values['page']) ? $this->values['page'] : 1);
            $this->pager->init();
        }

        return $this->pager;
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

        $queryBuilder = $repository
            ->createQueryBuilder('o');

        return $queryBuilder;
    }

    public function getQueryBuilder($values = array())
    {

        $queryBuilder = $this->getBaseQueryBuilder();

        foreach ($this->getFilters() as $name => $filter) {

            $value = isset($values[$name]) ? $values[$name] : null;

            $filter->apply($queryBuilder, $value);
        }

        return $queryBuilder;
    }

    public function setClassname($classname)
    {
        $this->classname = $classname;
    }

    public function getClassname()
    {
        return $this->classname;
    }

    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function setFilterFields($filterFields)
    {
        $this->filterFields = $filterFields;
    }

    public function getFilterFields()
    {
        return $this->filterFields;
    }

    public function addFilter($filter)
    {
        $this->filters[$filter->getName()] = $filter;
    }
    
    public function getFilters()
    {
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

    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;
    }

    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }
}