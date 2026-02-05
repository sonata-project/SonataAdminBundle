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

namespace SensioLabs\AdminBundle\Filter\ORM;

use Doctrine\ORM\Query\Expr\Composite;
use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use SensioLabs\AdminBundle\Filter\Filter as BaseFilter;
use SensioLabs\AdminBundle\Filter\Model\FilterData;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;

abstract class Filter extends BaseFilter implements GroupableConditionAwareInterface
{
    private ?Composite $conditionGroup = null;

    /**
     * Apply the filter to the QueryBuilder instance.
     *
     * @phpstan-param ProxyQueryInterface<object> $query
     * @phpstan-param literal-string $alias
     * @phpstan-param literal-string $field
     */
    abstract public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void;

    final public function apply(BaseProxyQueryInterface $query, FilterData $filterData): void
    {
        if (!$query instanceof ProxyQueryInterface) {
            throw new \TypeError(\sprintf('The query MUST implement %s.', ProxyQueryInterface::class));
        }

        if ($filterData->hasValue()) {
            [$alias, $field] = $this->association($query, $filterData);

            $this->filter($query, $alias, $field, $filterData);
        }
    }

    public function setConditionGroup(Composite $conditionGroup): void
    {
        $this->conditionGroup = $conditionGroup;
    }

    public function getConditionGroup(): Composite
    {
        if (!$this->hasConditionGroup()) {
            throw new \LogicException(\sprintf('Filter "%s" has no condition group.', $this->getName()));
        }
        \assert(null !== $this->conditionGroup);

        return $this->conditionGroup;
    }

    public function hasConditionGroup(): bool
    {
        return null !== $this->conditionGroup;
    }

    /**
     * @param ProxyQueryInterface<object> $query
     *
     * @return string[]
     *
     * @phpstan-return array{literal-string, literal-string}
     */
    protected function association(ProxyQueryInterface $query, FilterData $data): array
    {
        $alias = $query->entityJoin($this->getParentAssociationMappings());
        /** @var literal-string $fieldName */
        $fieldName = $this->getFieldName();

        return [$alias, $fieldName];
    }

    /**
     * @param ProxyQueryInterface<object> $query
     */
    final protected function applyWhere(ProxyQueryInterface $query, mixed $parameter): void
    {
        if (self::CONDITION_OR === $this->getCondition()) {
            $this->addOrParameter($query, $parameter);
        } else {
            $query->getQueryBuilder()->andWhere($parameter);
        }

        // filter is active since it's added to the queryBuilder
        $this->setActive(true);
    }

    /**
     * @param ProxyQueryInterface<object> $query
     */
    final protected function applyHaving(ProxyQueryInterface $query, mixed $parameter): void
    {
        if (self::CONDITION_OR === $this->getCondition()) {
            $query->getQueryBuilder()->orHaving($parameter);
        } else {
            $query->getQueryBuilder()->andHaving($parameter);
        }

        // filter is active since it's added to the queryBuilder
        $this->setActive(true);
    }

    /**
     * @param ProxyQueryInterface<object> $query
     */
    final protected function getNewParameterName(ProxyQueryInterface $query): string
    {
        // dots are not accepted in a DQL identifier so replace them
        // by underscores.
        return str_replace('.', '_', $this->getName()).'_'.$query->getUniqueParameterId();
    }

    /**
     * Adds the parameter to the corresponding `Orx` expression used in the `where` clause.
     * If it doesn't exist, a new one is created.
     * It allows to get queries like "WHERE previous_condition = previous_value AND (filter_1 = value OR filter_2 = value OR ...)",
     * where the logical "OR" operators added by the filters are grouped inside a condition,
     * instead of having unfolded "WHERE ..." clauses like "WHERE previous_condition = previous_value OR filter_1 = value OR filter_2 = value OR ...",
     * which will produce undesired results.
     *
     * @param ProxyQueryInterface<object> $query
     */
    private function addOrParameter(ProxyQueryInterface $query, mixed $parameter): void
    {
        if ($this->hasPreviousFilter()) {
            $previousFilter = $this->getPreviousFilter();

            if (
                $previousFilter instanceof GroupableConditionAwareInterface
                && $previousFilter->hasConditionGroup()
            ) {
                $conditionGroup = $previousFilter->getConditionGroup();
                $conditionGroup->add($parameter);

                $this->setConditionGroup($conditionGroup);

                return;
            }
        }

        $qb = $query->getQueryBuilder();

        // Create a new `Orx` expression.
        $conditionGroup = $qb->expr()->orX();

        $conditionGroup->add($parameter);

        // Add the `Orx` expression to the `where` clause.
        $qb->andWhere($conditionGroup);

        $this->setConditionGroup($conditionGroup);
    }
}
