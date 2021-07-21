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

use Sonata\AdminBundle\Search\ChainableFilterInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Filter implements FilterInterface, ChainableFilterInterface
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var array<string, mixed>
     */
    protected $options = [];

    /**
     * @var string|null
     */
    protected $condition;

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var FilterInterface|null
     */
    private $previousFilter;

    final public function initialize(string $name, array $options = []): void
    {
        $this->name = $name;
        $this->setOptions($options);
    }

    final public function getName(): string
    {
        if (null === $this->name) {
            throw new \LogicException(sprintf(
                'Seems like you didn\'t call `initialize()` on the filter `%s`. Did you create it through `%s::create()`?',
                static::class,
                FilterFactory::class
            ));
        }

        return $this->name;
    }

    final public function getFormName(): string
    {
        /*
           Symfony default form class sadly can't handle
           form element with dots in its name (when data
           get bound, the default dataMapper is a PropertyPathMapper).
           So use this trick to avoid any issue.
        */

        return str_replace('.', '__', $this->getName());
    }

    final public function getOption(string $name, $default = null)
    {
        if (\array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }

    final public function setOption(string $name, $value): void
    {
        $this->options[$name] = $value;
    }

    final public function getFieldType(): string
    {
        return $this->getOption('field_type', TextType::class);
    }

    final public function getFieldOptions(): array
    {
        return $this->getOption('field_options', []);
    }

    final public function getFieldOption(string $name, $default = null)
    {
        if (isset($this->options['field_options'][$name]) && \is_array($this->options['field_options'])) {
            return $this->options['field_options'][$name];
        }

        return $default;
    }

    final public function setFieldOption(string $name, $value): void
    {
        $this->options['field_options'][$name] = $value;
    }

    final public function getLabel()
    {
        return $this->getOption('label');
    }

    final public function setLabel($label): void
    {
        $this->setOption('label', $label);
    }

    final public function getFieldName(): string
    {
        $fieldName = $this->getOption('field_name');

        if (null === $fieldName) {
            throw new \RuntimeException(sprintf(
                'The option `field_name` must be set for field: `%s`',
                $this->getName()
            ));
        }

        return $fieldName;
    }

    final public function getParentAssociationMappings(): array
    {
        return $this->getOption('parent_association_mappings', []);
    }

    final public function getFieldMapping(): array
    {
        $fieldMapping = $this->getOption('field_mapping');

        if (null === $fieldMapping) {
            throw new \RuntimeException(sprintf(
                'The option `field_mapping` must be set for field: `%s`',
                $this->getName()
            ));
        }

        return $fieldMapping;
    }

    final public function getAssociationMapping(): array
    {
        $associationMapping = $this->getOption('association_mapping');

        if (null === $associationMapping) {
            throw new \RuntimeException(sprintf(
                'The option `association_mapping` must be set for field: `%s`',
                $this->getName()
            ));
        }

        return $associationMapping;
    }

    /**
     * @param array<string, mixed> $options
     */
    final public function setOptions(array $options): void
    {
        $this->options = array_merge(
            ['show_filter' => null, 'advanced_filter' => true],
            $this->getDefaultOptions(),
            $options
        );
    }

    /**
     * @return array<string, mixed>
     */
    final public function getOptions(): array
    {
        return $this->options;
    }

    final public function isActive(): bool
    {
        return $this->active;
    }

    final public function setCondition(string $condition): void
    {
        $this->condition = $condition;
    }

    final public function getCondition(): ?string
    {
        return $this->condition;
    }

    final public function getTranslationDomain(): ?string
    {
        return $this->getOption('translation_domain');
    }

    final public function setPreviousFilter(FilterInterface $filter): void
    {
        $this->previousFilter = $filter;
    }

    final public function getPreviousFilter(): FilterInterface
    {
        if (!$this->hasPreviousFilter()) {
            throw new \LogicException(sprintf('Filter "%s" has no previous filter.', $this->getName()));
        }
        \assert(null !== $this->previousFilter);

        return $this->previousFilter;
    }

    final public function hasPreviousFilter(): bool
    {
        return null !== $this->previousFilter;
    }

    final protected function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
