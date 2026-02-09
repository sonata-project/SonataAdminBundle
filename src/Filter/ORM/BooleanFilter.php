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
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;
use SensioLabs\AdminBundle\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

final class BooleanFilter extends Filter
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
            'field_type' => BooleanType::class,
            'operator_type' => HiddenType::class,
            'operator_options' => [],
            'treat_null_as' => null,
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
        $values = [];
        foreach ($data->getValue() as $v) {
            if (!\in_array($v, [BooleanType::TYPE_NO, BooleanType::TYPE_YES], true)) {
                continue;
            }

            $values[] = (BooleanType::TYPE_YES === $v) ? 1 : 0;
        }

        if (0 === \count($values)) {
            return;
        }

        $or = $query->getQueryBuilder()->expr()->orX();
        $treatNullAs = $this->getOption('treat_null_as');
        if (
            false === $treatNullAs && \in_array(0, $values, true)
            || true === $treatNullAs && \in_array(1, $values, true)
        ) {
            $or->add($query->getQueryBuilder()->expr()->isNull(\sprintf('%s.%s', $alias, $field)));
        }

        $or->add($query->getQueryBuilder()->expr()->in(\sprintf('%s.%s', $alias, $field), $values));

        $this->applyWhere($query, $or);
    }

    /**
     * @param ProxyQueryInterface<object> $query
     */
    private function filterWithSingleValue(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        $or = $query->getQueryBuilder()->expr()->orX();
        $treatNullAs = $this->getOption('treat_null_as');
        if (
            false === $treatNullAs && BooleanType::TYPE_NO === $data->getValue()
            || true === $treatNullAs && BooleanType::TYPE_YES === $data->getValue()
        ) {
            $or->add($query->getQueryBuilder()->expr()->isNull(\sprintf('%s.%s', $alias, $field)));
        }

        $parameterName = $this->getNewParameterName($query);
        $or->add(\sprintf('%s.%s = :%s', $alias, $field, $parameterName));
        $query->getQueryBuilder()->setParameter($parameterName, (BooleanType::TYPE_YES === $data->getValue()) ? 1 : 0);

        $this->applyWhere($query, $or);
    }
}
