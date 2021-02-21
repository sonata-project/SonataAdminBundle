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
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;

/**
 * This class is use to simulate the Form API.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DatagridMapper extends BaseMapper
{
    /**
     * @var DatagridInterface
     */
    protected $datagrid;

    /**
     * @var DatagridBuilderInterface
     */
    protected $builder;

    public function __construct(
        DatagridBuilderInterface $datagridBuilder,
        DatagridInterface $datagrid,
        AdminInterface $admin
    ) {
        parent::__construct($datagridBuilder, $admin);
        $this->datagrid = $datagrid;
    }

    /**
     * NEXT_MAJOR: Change signature for ($name, ?string $type = null, array $filterOptions = [], array $fieldDescriptionOptions = []).
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

            if (!isset($filterOptions['field_name'])) {
                $filterOptions['field_name'] = substr(strrchr('.'.$name, '.'), 1);
            }

            // NEXT_MAJOR: Remove the check and use `createFieldDescription`.
            if (method_exists($this->admin, 'createFieldDescription')) {
                $fieldDescription = $this->admin->createFieldDescription(
                    $name,
                    array_merge($filterOptions, $fieldDescriptionOptions)
                );
            } else {
                $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                    $this->admin->getClass(),
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
            $fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'filter', 'label'));
        }

        if (!isset($fieldDescriptionOptions['role']) || $this->admin->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the DatagridBuilder
            $this->builder->addFilter($this->datagrid, $type, $fieldDescription, $this->admin);
        }

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
