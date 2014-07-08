<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Datagrid;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;

/**
 * This class is use to simulate the Form API
 *
 */
class DatagridMapper extends BaseMapper
{
    protected $datagrid;

    /**
     * @param \Sonata\AdminBundle\Builder\DatagridBuilderInterface $datagridBuilder
     * @param DatagridInterface                                    $datagrid
     * @param \Sonata\AdminBundle\Admin\AdminInterface             $admin
     */
    public function __construct(DatagridBuilderInterface $datagridBuilder, DatagridInterface $datagrid, AdminInterface $admin)
    {
        parent::__construct($datagridBuilder, $admin);
        $this->datagrid        = $datagrid;
    }

    /**
     * @throws \RuntimeException
     *
     * @param string $name
     * @param string $type
     * @param array  $filterOptions
     * @param string $fieldType
     * @param array  $fieldOptions
     *
     * @return DatagridMapper
     */
    public function add($name, $type = null, array $filterOptions = array(), $fieldType = null, $fieldOptions = null)
    {
        if (is_array($fieldOptions)) {
            $filterOptions['field_options'] = $fieldOptions;
        }

        if ($fieldType) {
            $filterOptions['field_type'] = $fieldType;
        }

        $filterOptions['field_name'] = isset($filterOptions['field_name']) ? $filterOptions['field_name'] : substr(strrchr('.'.$name, '.'), 1);

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($filterOptions);
        } elseif (is_string($name) && !$this->admin->hasFilterFieldDescription($name)) {
            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $name,
                $filterOptions
            );
        } elseif (is_string($name) && $this->admin->hasFilterFieldDescription($name)) {
            throw new \RuntimeException(sprintf('The field "%s" is already defined', $name));
        } else {
            throw new \RuntimeException('invalid state');
        }

        // add the field with the DatagridBuilder
        $this->builder->addFilter($this->datagrid, $type, $fieldDescription, $this->admin);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    public function get($name)
    {
        return $this->datagrid->getFilter($name);
    }

    /**
     * @param string $key
     *
     * @return boolean
     */
    public function has($key)
    {
        return $this->datagrid->hasFilter($key);
    }

    /**
     * @param string $key
     *
     * @return \Sonata\AdminBundle\Datagrid\DatagridMapper
     */
    public function remove($key)
    {
        $this->admin->removeFilterFieldDescription($key);
        $this->datagrid->removeFilter($key);

        return $this;
    }

    /**
     * @param array $keys field names
     *
     * @return \Sonata\AdminBundle\Datagrid\ListMapper
     */
    public function reorder(array $keys)
    {
        $this->datagrid->reorderFilters($keys);

        return $this;
    }
}
