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
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

/**
 * This class is used to simulate the Form API.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
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

    public function __construct(
        ShowBuilderInterface $showBuilder,
        FieldDescriptionCollection $list,
        AdminInterface $admin
    ) {
        parent::__construct($showBuilder, $admin);
        $this->list = $list;
    }

    /**
     * @param FieldDescriptionInterface|string $name
     * @param array<string, mixed>             $fieldDescriptionOptions
     *
     * @throws \LogicException
     *
     * @return static
     */
    public function add($name, ?string $type = null, array $fieldDescriptionOptions = []): self
    {
        if (!$this->shouldApply()) {
            return $this;
        }

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } elseif (\is_string($name)) {
            if (!$this->admin->hasShowFieldDescription($name)) {
                $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                    $this->admin->getClass(),
                    $name,
                    $fieldDescriptionOptions
                );
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

        $fieldKey = ($name instanceof FieldDescriptionInterface) ? $name->getName() : $name;

        $this->addFieldToCurrentGroup($fieldKey);

        if (null === $fieldDescription->getLabel()) {
            $fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'show', 'label'));
        }

        $fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));

        if (!isset($fieldDescriptionOptions['role']) || $this->admin->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the FormBuilder
            $this->builder->addField($this->list, $type, $fieldDescription, $this->admin);
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
        $this->admin->removeShowFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    /**
     * Removes a group.
     *
     * @param string $group          The group to delete
     * @param string $tab            The tab the group belongs to, defaults to 'default'
     * @param bool   $deleteEmptyTab Whether or not the parent Tab should be deleted too,
     *                               when the deleted group leaves the tab empty after deletion
     *
     * @return static
     */
    public function removeGroup(string $group, string $tab = 'default', bool $deleteEmptyTab = false): self
    {
        $groups = $this->getGroups();

        // When the default tab is used, the tabname is not prepended to the index in the group array
        if ('default' !== $tab) {
            $group = sprintf('%s.%s', $tab, $group);
        }

        if (isset($groups[$group])) {
            foreach ($groups[$group]['fields'] as $field) {
                $this->remove($field);
            }
        }
        unset($groups[$group]);

        $tabs = $this->getTabs();
        $key = array_search($group, $tabs[$tab]['groups'], true);

        if (false !== $key) {
            unset($tabs[$tab]['groups'][$key]);
        }
        if ($deleteEmptyTab && 0 === \count($tabs[$tab]['groups'])) {
            unset($tabs[$tab]);
        }

        $this->setTabs($tabs);
        $this->setGroups($groups);

        return $this;
    }

    final public function keys(): array
    {
        return array_keys($this->list->getElements());
    }

    public function reorder(array $keys): self
    {
        $this->admin->reorderShowGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    protected function getGroups(): array
    {
        return $this->admin->getShowGroups();
    }

    protected function setGroups(array $groups): void
    {
        $this->admin->setShowGroups($groups);
    }

    protected function getTabs(): array
    {
        return $this->admin->getShowTabs();
    }

    protected function setTabs(array $tabs): void
    {
        $this->admin->setShowTabs($tabs);
    }

    protected function getName(): string
    {
        return 'show';
    }
}
