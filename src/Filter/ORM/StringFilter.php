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
use SensioLabs\AdminBundle\Form\Type\Operator\StringOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;

final class StringFilter extends Filter
{
    public const TRIM_NONE = 0;
    public const TRIM_LEFT = 1;
    public const TRIM_RIGHT = 2;
    public const TRIM_BOTH = self::TRIM_LEFT | self::TRIM_RIGHT;

    public const CHOICES = [
        StringOperatorType::TYPE_CONTAINS => 'LIKE',
        StringOperatorType::TYPE_STARTS_WITH => 'LIKE',
        StringOperatorType::TYPE_ENDS_WITH => 'LIKE',
        StringOperatorType::TYPE_NOT_CONTAINS => 'NOT LIKE',
        StringOperatorType::TYPE_EQUAL => '=',
        StringOperatorType::TYPE_NOT_EQUAL => '<>',
    ];

    /**
     * Filtering types do not make sense for searching by empty value.
     */
    private const MEANINGLESS_TYPES = [
        StringOperatorType::TYPE_CONTAINS,
        StringOperatorType::TYPE_STARTS_WITH,
        StringOperatorType::TYPE_ENDS_WITH,
        StringOperatorType::TYPE_NOT_CONTAINS,
    ];

    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        $value = $this->trim((string) ($data->getValue() ?? ''));
        $type = $data->getType() ?? StringOperatorType::TYPE_CONTAINS;

        $allowEmpty = $this->getOption('allow_empty', false);
        \assert(\is_bool($allowEmpty));

        // ignore empty value if it doesn't make sense
        if ('' === $value && (!$allowEmpty || \in_array($type, self::MEANINGLESS_TYPES, true))) {
            return;
        }

        $operator = $this->getOperator($type);

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $parameterName = $this->getNewParameterName($query);

        $forceCaseInsensitivity = $this->getOption('force_case_insensitivity', false);
        \assert(\is_bool($forceCaseInsensitivity));

        if ($forceCaseInsensitivity && '' !== $value) {
            $clause = 'LOWER(%s.%s) %s :%s';
        } else {
            $clause = '%s.%s %s :%s';
        }

        $or = $query->getQueryBuilder()->expr()->orX(
            \sprintf($clause, $alias, $field, $operator, $parameterName)
        );

        if (StringOperatorType::TYPE_NOT_CONTAINS === $type || StringOperatorType::TYPE_NOT_EQUAL === $type) {
            $or->add($query->getQueryBuilder()->expr()->isNull(\sprintf('%s.%s', $alias, $field)));
        }

        $this->applyWhere($query, $or);

        $format = match ($type) {
            StringOperatorType::TYPE_EQUAL, StringOperatorType::TYPE_NOT_EQUAL => '%s',
            StringOperatorType::TYPE_STARTS_WITH => '%s%%',
            StringOperatorType::TYPE_ENDS_WITH => '%%%s',
            default => '%%%s%%',
        };

        $query->getQueryBuilder()->setParameter(
            $parameterName,
            \sprintf(
                $format,
                $forceCaseInsensitivity && '' !== $value ? mb_strtolower($value) : $value
            )
        );
    }

    public function getDefaultOptions(): array
    {
        return [
            'force_case_insensitivity' => false,
            'trim' => self::TRIM_BOTH,
            'allow_empty' => false,
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
            'operator_type' => StringOperatorType::class,
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

    private function trim(string $string): string
    {
        $trimMode = $this->getOption('trim', self::TRIM_BOTH);

        if (0 !== ($trimMode & self::TRIM_LEFT)) {
            $string = ltrim($string);
        }

        if (0 !== ($trimMode & self::TRIM_RIGHT)) {
            $string = rtrim($string);
        }

        return $string;
    }
}
