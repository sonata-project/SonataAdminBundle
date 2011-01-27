<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Tool;

use Sonata\BaseApplicationBundle\Tool\DoctrinePager as Pager;

use Sonata\BaseApplicationBundle\Filter\StringFilter;
use Sonata\BaseApplicationBundle\Filter\BooleanFilter;
use Sonata\BaseApplicationBundle\Filter\IntegerFilter;
use Sonata\BaseApplicationBundle\Filter\CallbackFilter;
use Sonata\BaseApplicationBundle\Filter\ChoiceFilter;

use Sonata\BaseApplicationBundle\Admin\EntityAdmin;
use Sonata\BaseApplicationBundle\Admin\FieldDescription;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

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

        $queryBuidler = $repository
            ->createQueryBuilder('o');

        return $queryBuidler;
    }

    public function getQueryBuilder($values = array())
    {

        $queryBuidler = $this->getBaseQueryBuilder();

        foreach ($this->getFilters() as $name => $filter) {

            $value = isset($values[$name]) ? $values[$name] : null;

            $filter->apply($queryBuidler, $value);
        }

        return $queryBuidler;
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


    public function buildFilterFields()
    {
        $this->filterFields = EntityAdmin::getBaseFields($this->getClassMetaData(), $this->filterFields);

        foreach ($this->filterFields as $name => $fieldDescription) {

            // set default values
            $fieldDescription->setOption('code', $fieldDescription->getOption('code', $name));
            $fieldDescription->setOption('label', $fieldDescription->getOption('label', $name));
            $fieldDescription->setOption('filter_value', $fieldDescription->getOption('filter_value', null));
            $fieldDescription->setOption('filter_options', $fieldDescription->getOption('filter_options', null));
            $fieldDescription->setOption('filter_field_options', $fieldDescription->getOption('filter_field_options', null));
            $fieldDescription->setOption('name', $fieldDescription->getOption('name', $name));

            // set the default type if none is set
            if (!$fieldDescription->getType()) {
                $fieldDescription->setType('string');
            }

            if (!$fieldDescription->getTemplate()) {
                $fieldDescription->setTemplate(sprintf('SonataBaseApplicationBundle:CRUD:filter_%s.twig.html', $fieldDescription->getType()));

                if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_ONE) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:filter_many_to_one.twig.html');
                }

                if ($fieldDescription->getType() == ClassMetadataInfo::MANY_TO_MANY) {
                    $fieldDescription->setTemplate('SonataBaseApplicationBundle:CRUD:filter_many_to_many.twig.html');
                }
            }
        }

        $this->configureFilterFields();
    }

    public function getChoices(FieldDescription $fieldDescription)
    {
        $targets = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('t')
            ->from($fieldDescription->getTargetEntity(), 't')
            ->getQuery()
            ->execute();

        $choices = array();
        foreach ($targets as $target) {
            // todo : puts this into a configuration option and use reflection
            foreach (array('getTitle', 'getName', '__toString') as $getter) {
                if (method_exists($target, $getter)) {
                    $choices[$target->getId()] = $target->$getter();
                    break;
                }
            }
        }

        return $choices;
    }

    public function getFilterInstance(FieldDescription $fieldDescription)
    {

        if (!$fieldDescription->getType()) {

            return false;
        }

        $name = $fieldDescription->getName();

        switch($fieldDescription->getType()) {

            case ClassMetadataInfo::MANY_TO_MANY:

                $options = $fieldDescription->getOption('filter_field_options');
                $options['choices'] = $this->getChoices($fieldDescription);

                $fieldDescription->setOption('filter_field_options', $options);

                $filter = new ChoiceFilter($name, $fieldDescription);

                break;

            case 'string':
            case 'text':
                $filter = new StringFilter($name, $fieldDescription);
                break;

            case 'boolean':
                $filter = new BooleanFilter($name, $fieldDescription);
                break;

            case 'integer':
                $filter = new IntegerFilter($name, $fieldDescription);
                break;

            case 'callback':
                $filter = new CallbackFilter($name, $fieldDescription);
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

        if (!$this->filters) {
            foreach ($this->filterFields as $name => $description) {
                $filter = $this->getFilterInstance($this->filterFields[$name]);

                if ($filter) {
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

    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;
    }

    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }
}