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

namespace Sonata\AdminBundle\Show;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

/**
 * This class is used to simulate the Form API.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 * @phpstan-extends BaseGroupedMapper<T>
 */
class ShowMapper extends BaseGroupedMapper
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $list;

    /**
     * @var ShowBuilderInterface
     */
    protected $builder;

    /**
     * NEXT_MAJOR: Make the property private.
     *
     * @var AdminInterface
     * @phpstan-var AdminInterface<T> $admin
     */
    protected $admin;

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function __construct(
        ShowBuilderInterface $showBuilder,
        FieldDescriptionCollection $list,
        AdminInterface $admin
    ) {
        $this->admin = $admin;
        $this->builder = $showBuilder;
        $this->list = $list;
    }

    /**
     * NEXT_MAJOR: Restrict the type of the $name parameter to string.
     *
     * @param FieldDescriptionInterface|string $name
     * @param string|null                      $type
     * @param array<string, mixed>             $fieldDescriptionOptions
     *
     * @throws \LogicException
     *
     * @return static
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = [])
    {
        if (!$this->shouldApply()) {
            return $this;
        }

        $fieldKey = ($name instanceof FieldDescriptionInterface) ? $name->getName() : $name;

        $this->addFieldToCurrentGroup($fieldKey);

        // NEXT_MAJOR: Keep only the elseif part.
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
            if (!$this->getAdmin()->hasShowFieldDescription($name)) {

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
                throw new \LogicException(sprintf(
                    'Duplicate field name "%s" in show mapper. Names should be unique.',
                    $name
                ));
            }
        } else {
            throw new \TypeError(
                'Unknown field name in show mapper.'
                    .' Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.
        if (null === $fieldDescription->getLabel('sonata_deprecation_mute')) {
            $fieldDescription->setOption('label', $this->getAdmin()->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'show', 'label'));
        }

        $fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));

        if (!isset($fieldDescriptionOptions['role']) || $this->getAdmin()->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the FormBuilder
            $this->builder->addField($this->list, $type, $fieldDescription, $this->getAdmin());
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
        $this->getAdmin()->removeShowFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    final public function keys()
    {
        return array_keys($this->list->getElements());
    }

    public function reorder(array $keys)
    {
        $this->getAdmin()->reorderShowGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    protected function getGroups()
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.

        return $this->getAdmin()->getShowGroups('sonata_deprecation_mute');
    }

    protected function setGroups(array $groups)
    {
        $this->getAdmin()->setShowGroups($groups);
    }

    protected function getTabs()
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.

        return $this->getAdmin()->getShowTabs('sonata_deprecation_mute');
    }

    protected function setTabs(array $tabs)
    {
        $this->getAdmin()->setShowTabs($tabs);
    }

    protected function getName()
    {
        return 'show';
    }
}

// NEXT_MAJOR: Remove next line.
interface_exists(FieldDescriptionInterface::class);
