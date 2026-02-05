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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CallbackFilter extends Filter
{
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (!\is_callable($this->getOption('callback'))) {
            throw new \RuntimeException(\sprintf('Please provide a valid callback option "filter" for field "%s"', $this->getName()));
        }

        $isActive = \call_user_func($this->getOption('callback'), $query, $alias, $field, $data);
        if (!\is_bool($isActive)) {
            throw new \UnexpectedValueException(\sprintf(
                'The callback should return a boolean, %s returned',
                \is_object($isActive) ? 'instance of "'.$isActive::class.'"' : '"'.\gettype($isActive).'"'
            ));
        }

        $this->setActive($isActive);
    }

    public function getDefaultOptions(): array
    {
        return [
            'callback' => null,
            'field_type' => TextType::class,
            'operator_type' => HiddenType::class,
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

    protected function association(ProxyQueryInterface $query, FilterData $data): array
    {
        /** @var literal-string $alias */
        $alias = $this->getOption('alias', $query->entityJoin($this->getParentAssociationMappings()));
        /** @var literal-string $fieldName */
        $fieldName = $this->getFieldName();

        return [$alias, $fieldName];
    }
}
