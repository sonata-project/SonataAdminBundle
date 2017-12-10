<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;

/**
 * This class is use to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DatagridMapper extends BaseMapper
{
    /**
     * @var DatagridInterface
     */
    protected $datagrid;

    public function __construct(
        DatagridBuilderInterface $datagridBuilder,
        DatagridInterface $datagrid,
        AdminInterface $admin
    ) {
        parent::__construct($datagridBuilder, $admin);
        $this->datagrid = $datagrid;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string $fieldType
     * @param array  $fieldOptions
     *
     * @throws \RuntimeException
     *
     * @return DatagridMapper
     */
    public function add(
        $name,
        $type = null,
        array $filterOptions = [],
        $fieldType = null,
        $fieldOptions = null,
        array $fieldDescriptionOptions = []
    ) {
        if (is_array($fieldOptions)) {
            $filterOptions['field_options'] = $fieldOptions;
        }

        if ($fieldType) {
            $filterOptions['field_type'] = $fieldType;
        }

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($filterOptions);
        } elseif (is_string($name)) {
            if ($this->admin->hasFilterFieldDescription($name)) {
                throw new \RuntimeException(sprintf('Duplicate field name "%s" in datagrid mapper. Names should be unique.', $name));
            }

            if (!isset($filterOptions['field_name'])) {
                $filterOptions['field_name'] = substr(strrchr('.'.$name, '.'), 1);
            }

            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $name,
                array_merge($filterOptions, $fieldDescriptionOptions)
            );
        } else {
            throw new \RuntimeException(
                'Unknown field name in datagrid mapper.'
                .' Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        // add the field with the DatagridBuilder
        $this->builder->addFilter($this->datagrid, $type, $fieldDescription, $this->admin);

        return $this;
    }

    public function get($name)
    {
        return $this->datagrid->getFilter($name);
    }

    public function has($key)
    {
        return $this->datagrid->hasFilter($key);
    }

    final public function keys()
    {
        return array_keys($this->datagrid->getFilters());
    }

    public function remove($key)
    {
        $this->admin->removeFilterFieldDescription($key);
        $this->datagrid->removeFilter($key);

        return $this;
    }

    public function reorder(array $keys)
    {
        $this->datagrid->reorderFilters($keys);

        return $this;
    }
}
