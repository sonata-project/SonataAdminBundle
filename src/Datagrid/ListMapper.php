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
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Mapper\MapperInterface;

/**
 * NEXT_MAJOR: Stop extending BaseMapper.
 *
 * This class is used to simulate the Form API.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 * @phpstan-implements MapperInterface<T>
 */
class ListMapper extends BaseMapper implements MapperInterface
{
    // NEXT_MAJOR: Change for '_actions' and add an UPGRADE NOTE.
    public const NAME_ACTIONS = '_action';
    // NEXT_MAJOR: Change for '_batch' and add an UPGRADE NOTE.
    public const NAME_BATCH = 'batch';
    // NEXT_MAJOR: Change for '_select' and add an UPGRADE NOTE.
    public const NAME_SELECT = 'select';

    public const TYPE_ACTIONS = 'actions';
    public const TYPE_BATCH = 'batch';
    public const TYPE_SELECT = 'select';

    /**
     * @var FieldDescriptionCollection
     */
    protected $list;

    /**
     * @var ListBuilderInterface
     */
    protected $builder;

    /**
     * NEXT_MAJOR: Make the property private.
     *
     * @var AdminInterface
     * @phpstan-var AdminInterface<T>
     */
    protected $admin;

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function __construct(
        ListBuilderInterface $listBuilder,
        FieldDescriptionCollection $list,
        AdminInterface $admin
    ) {
        $this->admin = $admin;
        $this->builder = $listBuilder;
        $this->list = $list;
    }

    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param string      $name
     * @param string|null $type
     *
     * @return static
     */
    public function addIdentifier($name, $type = null, array $fieldDescriptionOptions = [])
    {
        $fieldDescriptionOptions['identifier'] = true;

        if (!isset($fieldDescriptionOptions['route']['name'])) {
            $routeName = ($this->getAdmin()->hasAccess('edit') && $this->getAdmin()->hasRoute('edit')) ? 'edit' : 'show';
            $fieldDescriptionOptions['route']['name'] = $routeName;
        }

        if (!isset($fieldDescriptionOptions['route']['parameters'])) {
            $fieldDescriptionOptions['route']['parameters'] = [];
        }

        return $this->add($name, $type, $fieldDescriptionOptions);
    }

    /**
     * NEXT_MAJOR: Restrict the type of the $name parameter to string.
     *
     * @param FieldDescriptionInterface|string $name
     * @param string|null                      $type
     *
     * @throws \LogicException
     *
     * @return static
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = [])
    {
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

        // Change deprecated inline action "view" to "show"
        if (self::NAME_ACTIONS === $name && self::TYPE_ACTIONS === $type) {
            if (isset($fieldDescriptionOptions['actions']['view'])) {
                @trigger_error(
                    'Inline action "view" is deprecated since version 2.2.4 and will be removed in 4.0. Use inline action "show" instead.',
                    \E_USER_DEPRECATED
                );

                $fieldDescriptionOptions['actions']['show'] = $fieldDescriptionOptions['actions']['view'];

                unset($fieldDescriptionOptions['actions']['view']);
            }
        }

        if (\array_key_exists('identifier', $fieldDescriptionOptions) && !\is_bool($fieldDescriptionOptions['identifier'])) {
            @trigger_error(
                'Passing a non boolean value for the "identifier" option is deprecated since sonata-project/admin-bundle 3.51 and will throw an exception in 4.0.',
                \E_USER_DEPRECATED
            );

            $fieldDescriptionOptions['identifier'] = (bool) $fieldDescriptionOptions['identifier'];
            // NEXT_MAJOR: Remove the previous 6 lines and use commented line below it instead
            // throw new \InvalidArgumentException(sprintf(
            //     'Value for "identifier" option must be boolean, %s given.',
            //     gettype($fieldDescriptionOptions['identifier'])
            // ));
        }

        // NEXT_MAJOR: Only keep the elseif part.
        if ($name instanceof FieldDescriptionInterface) {
            @trigger_error(
                sprintf(
                    'Passing a %s instance as first param of %s is deprecated since sonata-project/admin-bundle 3.103'
                    .' and will throw an exception in 4.0. You should pass a string instead.',
                    FieldDescriptionInterface::class,
                    __METHOD__
                ),
                \E_USER_DEPRECATED
            );

            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } elseif (\is_string($name)) {
            if ($this->getAdmin()->hasListFieldDescription($name)) {
                throw new \LogicException(sprintf(
                    'Duplicate field name "%s" in list mapper. Names should be unique.',
                    $name
                ));
            }

            // NEXT_MAJOR: Remove the check and use `createFieldDescription`.
            if (method_exists($this->getAdmin(), 'createFieldDescription')) {
                $fieldDescription = $this->getAdmin()->createFieldDescription(
                    $name,
                    $fieldDescriptionOptions
                );
            } else {
                $fieldDescription = $this->getAdmin()->getModelManager()->getNewFieldDescriptionInstance(
                    $this->getAdmin()->getClass(),
                    $name,
                    $fieldDescriptionOptions
                );
            }
        } else {
            throw new \TypeError(
                'Unknown field name in list mapper.'
                .' Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        if (null === $fieldDescription->getLabel('sonata_deprecation_mute')) {
            $fieldDescription->setOption(
                'label',
                $this->getAdmin()->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'list', 'label')
            );
        }

        if (isset($fieldDescriptionOptions['header_style'])) {
            @trigger_error(
                'The "header_style" option is deprecated, please, use "header_class" option instead.',
                \E_USER_DEPRECATED
            );
        }

        if (!isset($fieldDescriptionOptions['role']) || $this->getAdmin()->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the FormBuilder
            $this->builder->addField($this->list, $type, $fieldDescription, $this->getAdmin());

            // Ensure batch and action pseudo-fields are tagged as virtual
            if (\in_array($fieldDescription->getType(), [self::TYPE_ACTIONS, self::TYPE_BATCH, self::TYPE_SELECT], true)) {
                $fieldDescription->setOption('virtual_field', true);
            }
        }

        return $this;
    }

    public function get($key)
    {
        return $this->list->get($key);
    }

    public function has($key)
    {
        return $this->list->has($key);
    }

    public function remove($key)
    {
        $this->getAdmin()->removeListFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    final public function keys()
    {
        return array_keys($this->list->getElements());
    }

    public function reorder(array $keys)
    {
        $this->list->reorder($keys);

        return $this;
    }
}

// NEXT_MAJOR: Remove next line.
interface_exists(FieldDescriptionInterface::class);
