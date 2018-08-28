<?php

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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ShowMapper extends BaseGroupedMapper
{
    protected $list;

    public function __construct(
        ShowBuilderInterface $showBuilder,
        FieldDescriptionCollection $list,
        AdminInterface $admin
    ) {
        parent::__construct($showBuilder, $admin);
        $this->list = $list;
    }

    /**
     * @param mixed $name
     * @param mixed $type
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = [])
    {
        if (null !== $this->apply && !$this->apply) {
            return $this;
        }

        $fieldKey = ($name instanceof FieldDescriptionInterface) ? $name->getName() : $name;

        $this->addFieldToCurrentGroup($fieldKey);

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
                throw new \RuntimeException(sprintf('Duplicate field name "%s" in show mapper. Names should be unique.', $name));
            }
        } else {
            throw new \RuntimeException('invalid state');
        }

        if (!$fieldDescription->getLabel() && false !== $fieldDescription->getOption('label')) {
            $fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'show', 'label'));
        }

        $fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));

        // add the field with the FormBuilder
        $this->builder->addField($this->list, $type, $fieldDescription, $this->admin);

        return $this;
    }

    public function get($name)
    {
        return $this->list->get($name);
    }

    public function has($key)
    {
        return $this->list->has($key);
    }

    public function remove($key)
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
     * @return $this
     */
    public function removeGroup($group, $tab = 'default', $deleteEmptyTab = false)
    {
        $groups = $this->getGroups();

        // When the default tab is used, the tabname is not prepended to the index in the group array
        if ('default' !== $tab) {
            $group = $tab.'.'.$group;
        }

        if (isset($groups[$group])) {
            foreach ($groups[$group]['fields'] as $field) {
                $this->remove($field);
            }
        }
        unset($groups[$group]);

        $tabs = $this->getTabs();
        $key = array_search($group, $tabs[$tab]['groups']);

        if (false !== $key) {
            unset($tabs[$tab]['groups'][$key]);
        }
        if ($deleteEmptyTab && 0 == \count($tabs[$tab]['groups'])) {
            unset($tabs[$tab]);
        }

        $this->setTabs($tabs);
        $this->setGroups($groups);

        return $this;
    }

    final public function keys()
    {
        return array_keys($this->list->getElements());
    }

    public function reorder(array $keys)
    {
        $this->admin->reorderShowGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    protected function getGroups()
    {
        return $this->admin->getShowGroups();
    }

    protected function setGroups(array $groups)
    {
        $this->admin->setShowGroups($groups);
    }

    protected function getTabs()
    {
        return $this->admin->getShowTabs();
    }

    protected function setTabs(array $tabs)
    {
        $this->admin->setShowTabs($tabs);
    }

    protected function getName()
    {
        return 'show';
    }
}
