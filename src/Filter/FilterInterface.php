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

namespace Sonata\AdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FilterInterface
{
    public const CONDITION_OR = 'OR';

    public const CONDITION_AND = 'AND';

    /**
     * Apply the filter to the QueryBuilder instance.
     *
     * @param string  $alias
     * @param string  $field
     * @param mixed[] $value
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value);

    /**
     * @param mixed $query
     * @param mixed $value
     */
    public function apply($query, $value);

    /**
     * Returns the filter name.
     */
    public function getName(): string;

    /**
     * Returns the filter form name.
     */
    public function getFormName(): string;

    /**
     * Returns the label name.
     *
     * @return string|bool
     */
    public function getLabel();

    /**
     * @param string $label
     */
    public function setLabel($label);

    public function getDefaultOptions(): array;

    /**
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value);

    /**
     * @param string $name
     */
    public function initialize($name, array $options = []);

    public function getFieldName(): string;

    /**
     * @return array of mappings
     */
    public function getParentAssociationMappings(): array;

    /**
     * @return array field mapping
     */
    public function getFieldMapping(): array;

    /**
     * @return array association mapping
     */
    public function getAssociationMapping(): array;

    public function getFieldOptions(): array;

    /**
     * Get field option.
     *
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getFieldOption($name, $default = null);

    /**
     * Set field option.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setFieldOption($name, $value);

    public function getFieldType(): string;

    /**
     * Returns the main widget used to render the filter.
     */
    public function getRenderSettings(): array;

    /**
     * Returns true if filter is active.
     */
    public function isActive(): bool;

    /**
     * Set the condition to use with the left side of the query : OR or AND.
     *
     * @param string $condition
     */
    public function setCondition($condition);

    public function getCondition(): string;

    public function getTranslationDomain(): string;
}
