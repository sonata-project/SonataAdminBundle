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
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Mapper\MapperInterface;

/**
 * NEXT_MAJOR: Stop extending BaseMapper.
 *
 * This class is use to simulate the Form API.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 * @phpstan-implements MapperInterface<T>
 * @phpstan-extends BaseMapper<T>
 */
class DatagridMapper extends BaseMapper implements MapperInterface
{
    /**
     * @var DatagridInterface
     */
    protected $datagrid;

    /**
     * @var DatagridBuilderInterface
     */
    protected $builder;

    /**
     * NEXT_MAJOR: Make the property private.
     *
     * @var AdminInterface
     * @phpstan-var AdminInterface<T>
     */
    protected $admin;

    /**
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

    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * NEXT_MAJOR: Change signature for (string $name, ?string $type = null, array $filterOptions = [], array $fieldDescriptionOptions = []).
     *
     * @param FieldDescriptionInterface|string $name
     * @param string|null                      $type
     * @param array|string|null                $fieldDescriptionOptionsOrDeprecatedFieldType
     * @param array|null                       $deprecatedFieldOptions
     * @param array|null                       $deprecatedFieldDescriptionOptions
     *
     * @throws \LogicException
     *
     * @return DatagridMapper
     *
     * @phpstan-param class-string|null $type
     */
    public function add(
        $name,
        $type = null,
        array $filterOptions = [],
        $fieldDescriptionOptionsOrDeprecatedFieldType = [],
        $deprecatedFieldOptions = null,
        $deprecatedFieldDescriptionOptions = []
    ) {
        // NEXT_MAJOR remove the check and the else part.
        if (\is_array($fieldDescriptionOptionsOrDeprecatedFieldType)) {
            $fieldDescriptionOptions = $fieldDescriptionOptionsOrDeprecatedFieldType;
        } else {
            @trigger_error(
                'Not passing an array as argument 4 is deprecated since sonata-project/admin-bundle 3.89.',
                \E_USER_DEPRECATED
            );

            if (\is_array($deprecatedFieldOptions)) {
                @trigger_error(
                    'Passing the field_options as argument 5 is deprecated since sonata-project/admin-bundle 3.89.'.
                    'Use the `field_options` option of the third argument instead.',
                    \E_USER_DEPRECATED
                );

                $filterOptions['field_options'] = $deprecatedFieldOptions;
            }

            if ($fieldDescriptionOptionsOrDeprecatedFieldType) {
                @trigger_error(
                    'Passing the field_type as argument 4 is deprecated since sonata-project/admin-bundle 3.89.'.
                    'Use the `field_type` option of the third argument instead.',
                    \E_USER_DEPRECATED
                );

                $filterOptions['field_type'] = $fieldDescriptionOptionsOrDeprecatedFieldType;
            }

            $fieldDescriptionOptions = $deprecatedFieldDescriptionOptions;
        }

        // NEXT_MAJOR: Only keep the elseif part.
        if ($name instanceof FieldDescriptionInterface) {
            @trigger_error(
                sprintf(
                    'Passing a %s instance as first param of %s is deprecated since sonata-project/admin-bundle 3.103'
                    .' and will throw an exception in 4.0. You should pass a string instead.',
                    FieldDescriptionInterface::class,
                    __METHOD__
                ),
                \E_USER_DEPRECATED
            );

            $fieldDescription = $name;
            $fieldDescription->mergeOptions($filterOptions);
        } elseif (\is_string($name)) {
            if ($this->getAdmin()->hasFilterFieldDescription($name)) {
                throw new \LogicException(sprintf(
                    'Duplicate field name "%s" in datagrid mapper. Names should be unique.',
                    $name
                ));
            }

            if (!isset($filterOptions['field_name'])) {
                $filterOptions['field_name'] = substr(strrchr('.'.$name, '.'), 1);
            }

            // NEXT_MAJOR: Remove the check and use `createFieldDescription`.
            if (method_exists($this->getAdmin(), 'createFieldDescription')) {
                $fieldDescription = $this->getAdmin()->createFieldDescription(
                    $name,
                    array_merge($filterOptions, $fieldDescriptionOptions)
                );
            } else {
                $fieldDescription = $this->getAdmin()->getModelManager()->getNewFieldDescriptionInstance(
                    $this->getAdmin()->getClass(),
                    $name,
                    array_merge($filterOptions, $fieldDescriptionOptions)
                );
            }
        } else {
            throw new \TypeError(
                'Unknown field name in datagrid mapper.'
                .' Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        if (null === $fieldDescription->getLabel('sonata_deprecation_mute')) {
            $fieldDescription->setOption('label', $this->getAdmin()->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'filter', 'label'));
        }

        if (!isset($fieldDescriptionOptions['role']) || $this->getAdmin()->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the DatagridBuilder
            $this->builder->addFilter($this->datagrid, $type, $fieldDescription, $this->getAdmin());
        }

        return $this;
    }

    public function get($key)
    {
        return $this->datagrid->getFilter($key);
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
        $this->getAdmin()->removeFilterFieldDescription($key);
        $this->datagrid->removeFilter($key);

        return $this;
    }

    public function reorder(array $keys)
    {
        $this->datagrid->reorderFilters($keys);

        return $this;
    }
}

// NEXT_MAJOR: Remove next line.
interface_exists(FieldDescriptionInterface::class);
