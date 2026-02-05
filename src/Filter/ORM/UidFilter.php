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

use Doctrine\DBAL\Types\Types;
use SensioLabs\AdminBundle\Filter\Model\FilterData;
use SensioLabs\AdminBundle\Form\Type\Operator\EqualOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;

final class UidFilter extends Filter
{
    public const CHOICES = [
        EqualOperatorType::TYPE_EQUAL => '=',
        EqualOperatorType::TYPE_NOT_EQUAL => '<>',
    ];

    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        if ('' === $data->getValue()) {
            return;
        }

        $uidValue = $this->convertToUid($data->getValue()) ?? $data->getValue();
        $operator = $this->getOperator($data->getType() ?? EqualOperatorType::TYPE_EQUAL);
        $parameterName = $this->getNewParameterName($query);

        $this->applyWhere($query, \sprintf('%s.%s %s :%s', $alias, $field, $operator, $parameterName));
        $query->getQueryBuilder()->setParameter($parameterName, $uidValue, $this->getParameterType($uidValue));
    }

    public function getDefaultOptions(): array
    {
        return [
            'field_type' => TextType::class,
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

    private function convertToUid(string $value): ?AbstractUid
    {
        if (Uuid::isValid($value)) {
            return Uuid::fromString($value);
        }

        if (Ulid::isValid($value)) {
            return Ulid::fromString($value);
        }

        return null;
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

    private function getParameterType(AbstractUid|string $parameter): string
    {
        if ($parameter instanceof Uuid) {
            return 'uuid';
        }

        if ($parameter instanceof Ulid) {
            return 'ulid';
        }

        return Types::STRING;
    }
}
