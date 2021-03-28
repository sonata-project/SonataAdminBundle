<?php

declare(strict_types=1);

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
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;

/**
 * This class is use to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class DatagridMapper extends BaseMapper
{
    /**
     * @var DatagridBuilderInterface
     */
    protected $builder;

    /**
     * @var DatagridInterface
     */
    private $datagrid;

    public function __construct(
        DatagridBuilderInterface $datagridBuilder,
        DatagridInterface $datagrid,
        AdminInterface $admin
    ) {
        parent::__construct($datagridBuilder, $admin);
        $this->datagrid = $datagrid;
    }

    /**
     * @param FieldDescriptionInterface|string $name
     * @param array<string, mixed>             $filterOptions
     * @param array<string, mixed>             $fieldDescriptionOptions
     *
     * @throws \LogicException
     */
    public function add(
        $name,
        ?string $type = null,
        array $filterOptions = [],
        array $fieldDescriptionOptions = []
    ): self {
        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($filterOptions);
        } elseif (\is_string($name)) {
            if ($this->admin->hasFilterFieldDescription($name)) {
                throw new \LogicException(sprintf(
                    'Duplicate field name "%s" in datagrid mapper. Names should be unique.',
                    $name
                ));
            }

            $fieldDescription = $this->admin->createFieldDescription(
                $name,
                array_merge($filterOptions, $fieldDescriptionOptions)
            );
        } else {
            throw new \TypeError(
                'Unknown field name in datagrid mapper.'
                .' Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        if (null === $fieldDescription->getLabel()) {
            $fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'filter', 'label'));
        }

        if (!isset($fieldDescriptionOptions['role']) || $this->admin->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the DatagridBuilder
            $this->builder->addFilter($this->datagrid, $type, $fieldDescription);
        }

        return $this;
    }

    public function get(string $name): FilterInterface
    {
        return $this->datagrid->getFilter($name);
    }

    public function has(string $key): bool
    {
        return $this->datagrid->hasFilter($key);
    }

    public function keys(): array
    {
        return array_keys($this->datagrid->getFilters());
    }

    public function remove(string $key): self
    {
        $this->admin->removeFilterFieldDescription($key);
        $this->datagrid->removeFilter($key);

        return $this;
    }

    public function reorder(array $keys): self
    {
        $this->datagrid->reorderFilters($keys);

        return $this;
    }
}

// NEXT_MAJOR: Remove next line.
interface_exists(FieldDescriptionInterface::class);
