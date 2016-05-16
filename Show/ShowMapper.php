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
 * Class ShowMapper
 * This class is used to simulate the Form API.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ShowMapper extends BaseGroupedMapper
{
    protected $list;

    /**
     * @param ShowBuilderInterface       $showBuilder
     * @param FieldDescriptionCollection $list
     * @param AdminInterface             $admin
     */
    public function __construct(ShowBuilderInterface $showBuilder, FieldDescriptionCollection $list, AdminInterface $admin)
    {
        parent::__construct($showBuilder, $admin);
        $this->list = $list;
    }

    /**
     * @throws \RuntimeException
     *
     * @param mixed $name
     * @param mixed $type
     * @param array $fieldDescriptionOptions
     *
     * @return $this
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = array())
    {
        if ($this->apply !== null && !$this->apply) {
            return $this;
        }

        $fieldKey = ($name instanceof FieldDescriptionInterface) ? $name->getName() : $name;

        $this->addFieldToCurrentGroup($fieldKey);

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } elseif (is_string($name)) {
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

        if (!$fieldDescription->getLabel()) {
            $fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'show', 'label'));
        }

        $fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));

        // add the field with the FormBuilder
        $this->builder->addField($this->list, $type, $fieldDescription, $this->admin);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->list->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->list->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->admin->removeShowFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function keys()
    {
        return array_keys($this->list->getElements());
    }

    /**
     * {@inheritdoc}
     */
    public function reorder(array $keys)
    {
        $this->admin->reorderShowGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getGroups()
    {
        return $this->admin->getShowGroups();
    }

    /**
     * {@inheritdoc}
     */
    protected function setGroups(array $groups)
    {
        $this->admin->setShowGroups($groups);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTabs()
    {
        return $this->admin->getShowTabs();
    }

    /**
     * {@inheritdoc}
     */
    protected function setTabs(array $tabs)
    {
        $this->admin->setShowTabs($tabs);
    }
}
