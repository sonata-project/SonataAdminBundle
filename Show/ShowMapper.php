<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Show;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

/**
 * This class is used to simulate the Form API
 *
 */
class ShowMapper extends BaseGroupedMapper
{
    protected $list;

    /**
     * @param \Sonata\AdminBundle\Builder\ShowBuilderInterface     $showBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionCollection $list
     * @param \Sonata\AdminBundle\Admin\AdminInterface             $admin
     */
    public function __construct(ShowBuilderInterface $showBuilder, FieldDescriptionCollection $list, AdminInterface $admin)
    {
        parent::__construct($showBuilder, $admin);
        $this->list        = $list;
    }

    /**
     * @throws \RuntimeException
     *
     * @param mixed $name
     * @param mixed $type
     * @param array $fieldDescriptionOptions
     *
     * @return \Sonata\AdminBundle\Show\ShowMapper
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = array())
    {
        $fieldKey = ($name instanceof FieldDescriptionInterface) ? $name->getName() : $name;

        $this->addFieldToCurrentGroup($fieldKey);

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } elseif (is_string($name) && !$this->admin->hasShowFieldDescription($name)) {
            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $name,
                $fieldDescriptionOptions
            );
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
     * @param string $name
     *
     * @return array
     */
    public function get($name)
    {
        return $this->list->get($name);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->list->has($key);
    }

    /**
     * @param string $key
     *
     * @return \Sonata\AdminBundle\Show\ShowMapper
     */
    public function remove($key)
    {
        $this->admin->removeShowFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    /**
     * @param array $keys field names
     *
     * @return \Sonata\AdminBundle\Show\ShowMapper
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
