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

namespace Sonata\AdminBundle\Datagrid;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Mapper\MapperInterface;

/**
 * This class is used to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-import-type FieldDescriptionOptions from FieldDescriptionInterface
 *
 * @phpstan-template T of object
 * @phpstan-implements MapperInterface<T>
 */
final class ListMapper implements MapperInterface
{
    public const NAME_ACTIONS = '_actions';
    public const NAME_BATCH = '_batch';
    public const NAME_SELECT = '_select';

    public const TYPE_ACTIONS = 'actions';
    public const TYPE_BATCH = 'batch';
    public const TYPE_SELECT = 'select';

    /**
     * @param FieldDescriptionCollection<FieldDescriptionInterface> $list
     * @param AdminInterface<object>                                $admin
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function __construct(
        private ListBuilderInterface $builder,
        private FieldDescriptionCollection $list,
        private AdminInterface $admin
    ) {
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    /**
     * @param array<string, mixed> $fieldDescriptionOptions
     *
     * @phpstan-param FieldDescriptionOptions $fieldDescriptionOptions
     */
    public function addIdentifier(string $name, ?string $type = null, array $fieldDescriptionOptions = []): static
    {
        $fieldDescriptionOptions['identifier'] = true;

        return $this->add($name, $type, $fieldDescriptionOptions);
    }

    /**
     * @param array<string, mixed> $fieldDescriptionOptions
     *
     * @throws \LogicException
     *
     * @phpstan-param FieldDescriptionOptions $fieldDescriptionOptions
     */
    public function add(string $name, ?string $type = null, array $fieldDescriptionOptions = []): static
    {
        if (
            isset($fieldDescriptionOptions['role'])
            && \is_string($fieldDescriptionOptions['role'])
            && !$this->getAdmin()->isGranted($fieldDescriptionOptions['role'])
        ) {
            return $this;
        }

        // Default sort on "associated_property"
        if (isset($fieldDescriptionOptions['associated_property'])) {
            if (!isset($fieldDescriptionOptions['sortable'])) {
                $fieldDescriptionOptions['sortable'] = !\is_callable($fieldDescriptionOptions['associated_property']);
            }
            if (!isset($fieldDescriptionOptions['sort_parent_association_mappings'])) {
                $fieldDescriptionOptions['sort_parent_association_mappings'] = [[
                    'fieldName' => $name,
                ]];
            }
            if (!isset($fieldDescriptionOptions['sort_field_mapping'])) {
                $fieldDescriptionOptions['sort_field_mapping'] = [
                    'fieldName' => $fieldDescriptionOptions['associated_property'],
                ];
            }
        }

        // Type-guess the action field here because it is not a model property.
        if (self::NAME_ACTIONS === $name && null === $type) {
            $type = self::TYPE_ACTIONS;
        }

        if (\array_key_exists('identifier', $fieldDescriptionOptions) && !\is_bool($fieldDescriptionOptions['identifier'])) {
            throw new \InvalidArgumentException(sprintf('Value for "identifier" option must be boolean, %s given.', \gettype($fieldDescriptionOptions['identifier'])));
        }

        if ($this->getAdmin()->hasListFieldDescription($name)) {
            throw new \LogicException(sprintf(
                'Duplicate field name "%s" in list mapper. Names should be unique.',
                $name
            ));
        }

        /** @psalm-suppress ArgumentTypeCoercion https://github.com/vimeo/psalm/issues/9500 */
        $fieldDescription = $this->getAdmin()->createFieldDescription(
            $name,
            $fieldDescriptionOptions
        );

        if (null === $fieldDescription->getLabel()) {
            $fieldDescription->setOption(
                'label',
                $this->getAdmin()->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'list', 'label')
            );
        }

        $this->builder->addField($this->list, $type, $fieldDescription);

        // Ensure batch and action pseudo-fields are tagged as virtual
        if (\in_array($fieldDescription->getType(), [self::TYPE_ACTIONS, self::TYPE_BATCH, self::TYPE_SELECT], true)) {
            $fieldDescription->setOption('virtual_field', true);
        }

        return $this;
    }

    public function get(string $key): FieldDescriptionInterface
    {
        return $this->list->get($key);
    }

    public function has(string $key): bool
    {
        return $this->list->has($key);
    }

    public function remove(string $key): static
    {
        $this->getAdmin()->removeListFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    public function keys(): array
    {
        return array_keys($this->list->getElements());
    }

    public function reorder(array $keys): static
    {
        $this->list->reorder($keys);

        return $this;
    }
}
