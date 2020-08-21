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
use Sonata\AdminBundle\Admin\FieldDescriptionCollectionInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;

/**
 * This class is used to simulate the Form API.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ListMapper extends BaseMapper
{
    public const TYPE_ACTIONS = 'actions';
    public const TYPE_BATCH = 'batch';
    public const TYPE_SELECT = 'select';

    /**
     * @var FieldDescriptionCollectionInterface
     */
    protected $list;

    /**
     * @var ListBuilderInterface
     */
    protected $builder;

    public function __construct(
        ListBuilderInterface $listBuilder,
        FieldDescriptionCollectionInterface $list,
        AdminInterface $admin
    ) {
        parent::__construct($listBuilder, $admin);
        $this->list = $list;
    }

    /**
     * @param FieldDescriptionInterface|string $name
     * @param array<string, mixed>             $fieldDescriptionOptions
     */
    public function addIdentifier($name, ?string $type = null, array $fieldDescriptionOptions = []): self
    {
        $fieldDescriptionOptions['identifier'] = true;

        if (!isset($fieldDescriptionOptions['route']['name'])) {
            $routeName = ($this->admin->hasAccess('edit') && $this->admin->hasRoute('edit')) ? 'edit' : 'show';
            $fieldDescriptionOptions['route']['name'] = $routeName;
        }

        if (!isset($fieldDescriptionOptions['route']['parameters'])) {
            $fieldDescriptionOptions['route']['parameters'] = [];
        }

        return $this->add($name, $type, $fieldDescriptionOptions);
    }

    /**
     * @param FieldDescriptionInterface|string $name
     * @param array<string, mixed>             $fieldDescriptionOptions
     *
     * @throws \LogicException
     */
    public function add($name, ?string $type = null, array $fieldDescriptionOptions = []): self
    {
        // Default sort on "associated_property"
        if (isset($fieldDescriptionOptions['associated_property'])) {
            if (!isset($fieldDescriptionOptions['sortable'])) {
                $fieldDescriptionOptions['sortable'] = true;
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
        if ('_action' === $name && null === $type) {
            $type = self::TYPE_ACTIONS;
        }

        if (\array_key_exists('identifier', $fieldDescriptionOptions) && !\is_bool($fieldDescriptionOptions['identifier'])) {
            throw new \InvalidArgumentException(sprintf('Value for "identifier" option must be boolean, %s given.', \gettype($fieldDescriptionOptions['identifier'])));
        }

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } elseif (\is_string($name)) {
            if ($this->admin->hasListFieldDescription($name)) {
                throw new \LogicException(sprintf(
                    'Duplicate field name "%s" in list mapper. Names should be unique.',
                    $name
                ));
            }

            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $name,
                $fieldDescriptionOptions
            );
        } else {
            throw new \TypeError(
                'Unknown field name in list mapper.'
                .' Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        if (null === $fieldDescription->getLabel()) {
            $fieldDescription->setOption(
                'label',
                $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'list', 'label')
            );
        }

        if (!isset($fieldDescriptionOptions['role']) || $this->admin->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the FormBuilder
            $this->builder->addField($this->list, $type, $fieldDescription, $this->admin);

            // Ensure batch and action pseudo-fields are tagged as virtual
            if (\in_array($fieldDescription->getType(), [self::TYPE_ACTIONS, self::TYPE_BATCH, self::TYPE_SELECT], true)) {
                $fieldDescription->setOption('virtual_field', true);
            }
        }

        return $this;
    }

    public function get(string $name): FieldDescriptionInterface
    {
        return $this->list->get($name);
    }

    public function has(string $key): bool
    {
        return $this->list->has($key);
    }

    public function remove(string $key): self
    {
        $this->admin->removeListFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    /**
     * @return string[]
     */
    final public function keys(): array
    {
        return array_keys($this->list->getElements());
    }

    /**
     * @param string[] $keys
     */
    public function reorder(array $keys): self
    {
        $this->list->reorder($keys);

        return $this;
    }
}
