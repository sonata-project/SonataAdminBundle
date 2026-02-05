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
use SensioLabs\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;

final class StringListFilter extends Filter
{
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        $value = $data->getValue();

        if (!\is_array($value)) {
            $data = $data->changeValue([$value]);
        }

        $operator = $data->isType(ContainsOperatorType::TYPE_NOT_CONTAINS) ? 'NOT LIKE' : 'LIKE';

        $andConditions = $query->getQueryBuilder()->expr()->andX();
        foreach ($data->getValue() as $item) {
            $parameterName = $this->getNewParameterName($query);
            $andConditions->add(\sprintf('%s.%s %s :%s', $alias, $field, $operator, $parameterName));

            $query->getQueryBuilder()->setParameter($parameterName, '%'.serialize($item).'%');
        }

        if ($data->isType(ContainsOperatorType::TYPE_EQUAL)) {
            $andConditions->add(\sprintf("%s.%s LIKE 'a:%s:%%'", $alias, $field, \count($data->getValue())));
        }

        $this->applyWhere($query, $andConditions);
    }

    public function getDefaultOptions(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        return [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
            'operator_type' => ContainsOperatorType::class,
        ];
    }
}
