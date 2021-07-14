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
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Mapper\MapperInterface;

/**
 * This class is use to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-import-type FieldDescriptionOptions from \Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface
 *
 * @phpstan-template T of object
 * @phpstan-implements MapperInterface<T>
 */
final class DatagridMapper implements MapperInterface
{
    /**
     * @var DatagridBuilderInterface<ProxyQueryInterface>
     */
    private $builder;

    /**
     * @var DatagridInterface<ProxyQueryInterface>
     */
    private $datagrid;

    /**
     * @var AdminInterface<object>
     * @phpstan-var AdminInterface<T>
     */
    private $admin;

    /**
     * @param DatagridBuilderInterface<ProxyQueryInterface> $datagridBuilder
     * @param DatagridInterface<ProxyQueryInterface>        $datagrid
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function __construct(
        DatagridBuilderInterface $datagridBuilder,
        DatagridInterface $datagrid,
        AdminInterface $admin
    ) {
        $this->admin = $admin;
        $this->builder = $datagridBuilder;
        $this->datagrid = $datagrid;
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    /**
     * @param array<string, mixed> $filterOptions
     * @param array<string, mixed> $fieldDescriptionOptions
     *
     * @throws \LogicException
     *
     * @return static
     *
     * @phpstan-param class-string|null $type
     * @phpstan-param FieldDescriptionOptions $fieldDescriptionOptions
     */
    public function add(
        string $name,
        ?string $type = null,
        array $filterOptions = [],
        array $fieldDescriptionOptions = []
    ): self {
        if (isset($fieldDescriptionOptions['role']) && !$this->getAdmin()->isGranted($fieldDescriptionOptions['role'])) {
            return $this;
        }

        if ($this->getAdmin()->hasFilterFieldDescription($name)) {
            throw new \LogicException(sprintf(
                'Duplicate field name "%s" in datagrid mapper. Names should be unique.',
                $name
            ));
        }

        $fieldDescription = $this->getAdmin()->createFieldDescription(
            $name,
            array_merge($filterOptions, $fieldDescriptionOptions)
        );

        if (null === $fieldDescription->getLabel()) {
            $fieldDescription->setOption('label', $this->getAdmin()->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'filter', 'label'));
        }

        $this->builder->addFilter($this->datagrid, $type, $fieldDescription);

        return $this;
    }

    public function get(string $key): FilterInterface
    {
        return $this->datagrid->getFilter($key);
    }

    public function has(string $key): bool
    {
        return $this->datagrid->hasFilter($key);
    }

    public function keys(): array
    {
        return array_keys($this->datagrid->getFilters());
    }

    /**
     * @return static
     */
    public function remove(string $key): self
    {
        $this->getAdmin()->removeFilterFieldDescription($key);
        $this->datagrid->removeFilter($key);

        return $this;
    }

    /**
     * @return static
     */
    public function reorder(array $keys): self
    {
        $this->datagrid->reorderFilters($keys);

        return $this;
    }
}
