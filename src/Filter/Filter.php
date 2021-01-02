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

use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Filter implements FilterInterface
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var mixed|null
     *
     * @deprecated since sonata-project/admin-bundle 3.84, to be removed in 4.0.
     */
    protected $value;

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

    public function initialize(string $name, array $options = []): void
    {
        $this->name = $name;
        $this->setOptions($options);
    }

    public function getName(): string
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

    public function getFormName(): string
    {
        /*
           Symfony default form class sadly can't handle
           form element with dots in its name (when data
           get bound, the default dataMapper is a PropertyPathMapper).
           So use this trick to avoid any issue.
        */

        return str_replace('.', '__', $this->getName());
    }

    public function getOption(string $name, $default = null)
    {
        if (\array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }

    public function setOption(string $name, $value): void
    {
        $this->options[$name] = $value;
    }

    public function getFieldType(): string
    {
        return $this->getOption('field_type', TextType::class);
    }

    public function getFieldOptions(): array
    {
        return $this->getOption('field_options', ['required' => false]);
    }

    public function getFieldOption(string $name, $default = null)
    {
        if (isset($this->options['field_options'][$name]) && \is_array($this->options['field_options'])) {
            return $this->options['field_options'][$name];
        }

        return $default;
    }

    public function setFieldOption(string $name, $value): void
    {
        $this->options['field_options'][$name] = $value;
    }

    public function getLabel()
    {
        return $this->getOption('label');
    }

    public function setLabel($label): void
    {
        $this->setOption('label', $label);
    }

    public function getFieldName(): string
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

    public function getParentAssociationMappings(): array
    {
        return $this->getOption('parent_association_mappings', []);
    }

    public function getFieldMapping(): array
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

    public function getAssociationMapping(): array
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
    public function setOptions(array $options): void
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
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param mixed $value
     *
     * @deprecated since sonata-project/admin-bundle 3.84, to be removed in 4.0.
     */
    public function setValue($value): void
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/admin-bundle 3.84 and will be removed in version 4.0.',
            __METHOD__,
        ), E_USER_DEPRECATED);

        $this->value = $value;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @return mixed
     *
     * @deprecated since sonata-project/admin-bundle 3.84, to be removed in 4.0.
     */
    public function getValue()
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/admin-bundle 3.84 and will be removed in version 4.0.',
            __METHOD__,
        ), E_USER_DEPRECATED);

        return $this->value;
    }

    public function isActive(): bool
    {
        $values = $this->value;

        // NEXT_MAJOR: Change for `return $this->active;`
        return $this->active
            || isset($values['value']) && false !== $values['value'] && '' !== $values['value'];
    }

    public function setCondition(string $condition): void
    {
        $this->condition = $condition;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function getTranslationDomain(): ?string
    {
        return $this->getOption('translation_domain');
    }

    protected function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
