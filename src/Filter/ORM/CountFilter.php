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
use SensioLabs\AdminBundle\Form\Type\Operator\NumberOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType as FormNumberType;

final class CountFilter extends Filter
{
    public const CHOICES = [
        NumberOperatorType::TYPE_EQUAL => '=',
        NumberOperatorType::TYPE_GREATER_EQUAL => '>=',
        NumberOperatorType::TYPE_GREATER_THAN => '>',
        NumberOperatorType::TYPE_LESS_EQUAL => '<=',
        NumberOperatorType::TYPE_LESS_THAN => '<',
    ];

    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (!$data->hasValue() || !is_numeric($data->getValue())) {
            return;
        }

        $type = $data->getType() ?? NumberOperatorType::TYPE_EQUAL;
        $operator = $this->getOperator($type);

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $parameterName = $this->getNewParameterName($query);
        $rootAlias = current($query->getQueryBuilder()->getRootAliases());

        if (false === $rootAlias) {
            throw new \RuntimeException('There are not root aliases defined in the query.');
        }

        $query->getQueryBuilder()->addGroupBy($rootAlias);
        $this->applyHaving($query, \sprintf('COUNT(%s.%s) %s :%s', $alias, $field, $operator, $parameterName));
        $query->getQueryBuilder()->setParameter($parameterName, $data->getValue());
    }

    public function getDefaultOptions(): array
    {
        return [
            'field_type' => FormNumberType::class,
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
            'label' => $this->getLabel(),
            'operator_type' => NumberOperatorType::class,
        ];
    }

    private function getOperator(int $type): string
    {
        if (!isset(self::CHOICES[$type])) {
            throw new \OutOfRangeException(\sprintf(
                'The type "%s" is not supported, allowed one are "%s".',
                $type,
                implode('", "', array_keys(self::CHOICES))
            ));
        }

        return self::CHOICES[$type];
    }
}
