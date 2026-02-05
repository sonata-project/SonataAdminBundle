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

use SensioLabs\AdminBundle\Filter\Model\FilterData;
use SensioLabs\AdminBundle\Form\Type\Operator\EqualOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;

final class ChoiceFilter extends Filter
{
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        if (\is_array($data->getValue())) {
            $this->filterWithMultipleValues($query, $alias, $field, $data);
        } else {
            $this->filterWithSingleValue($query, $alias, $field, $data);
        }
    }

    public function getDefaultOptions(): array
    {
        return [
            'operator_type' => EqualOperatorType::class,
            'operator_options' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        return [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label' => $this->getLabel(),
        ];
    }

    /**
     * @param ProxyQueryInterface<object> $query
     */
    private function filterWithMultipleValues(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (0 === \count($data->getValue())) {
            return;
        }

        $isNullSelected = \in_array(null, $data->getValue(), true);
        $completeField = \sprintf('%s.%s', $alias, $field);
        $parameterName = $this->getNewParameterName($query);

        $or = $query->getQueryBuilder()->expr()->orX();
        if ($data->isType(EqualOperatorType::TYPE_NOT_EQUAL)) {
            $or->add($query->getQueryBuilder()->expr()->notIn($completeField, ':'.$parameterName));

            if (!$isNullSelected) {
                $or->add($query->getQueryBuilder()->expr()->isNull($completeField));
            }
        } else {
            $or->add($query->getQueryBuilder()->expr()->in($completeField, ':'.$parameterName));

            if ($isNullSelected) {
                $or->add($query->getQueryBuilder()->expr()->isNull($completeField));
            }
        }

        $this->applyWhere($query, $or);
        $query->getQueryBuilder()->setParameter($parameterName, $data->getValue());
    }

    /**
     * @param ProxyQueryInterface<object> $query
     */
    private function filterWithSingleValue(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if ('' === $data->getValue() || false === $data->getValue()) {
            return;
        }

        $parameterName = $this->getNewParameterName($query);
        $completeField = \sprintf('%s.%s', $alias, $field);

        if ($data->isType(EqualOperatorType::TYPE_NOT_EQUAL)) {
            if (null === $data->getValue()) {
                $this->applyWhere($query, $query->getQueryBuilder()->expr()->isNotNull($completeField));
            } else {
                $this->applyWhere(
                    $query,
                    \sprintf('%s != :%s OR %s IS NULL', $completeField, $parameterName, $completeField)
                );
                $query->getQueryBuilder()->setParameter($parameterName, $data->getValue());
            }
        } else {
            if (null === $data->getValue()) {
                $this->applyWhere($query, $query->getQueryBuilder()->expr()->isNull($completeField));
            } else {
                $this->applyWhere($query, \sprintf('%s = :%s', $completeField, $parameterName));
                $query->getQueryBuilder()->setParameter($parameterName, $data->getValue());
            }
        }
    }
}
