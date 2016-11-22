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

        // NEXT_MAJOR: Remove first "if" statement (when requirement of Symfony is >= 2.8)
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) { // symfony >= 2.8
            $choices = $fieldDescription->getOption('choices');
            if (($type == 'choice' || $type == 'Symfony\Component\Form\Extension\Core\Type\ChoiceType') && $choices) {
                // NEXT_MAJOR: Remove, leave only code from "else" (when requirement of Symfony is >= 3.0)
                if (method_exists('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions')) { // 2.8 <= symfony < 3.0
                    $choicesAsValues = $fieldDescription->getOption('choices_as_values') ?: false;
                    if (true !== $choicesAsValues) {
                        @trigger_error(sprintf('The value "false" for the "choices_as_values" option for field `%s` in `%s` is deprecated since symfony version 2.8 and will not be supported anymore in symfony 3.0. Set this option to "true" and flip the contents of the "choices" option instead.', $fieldDescription->getName(), get_class($this->admin)), E_USER_DEPRECATED);
                    }
                    if ($choicesAsValues) {
                        $choices = array_flip($choices);
                    }
                } else {
                    $choicesAsValues = $fieldDescription->getOption('choices_as_values') ?: false;
                    // NEXT_MAJOR: Remove "if" statement (when requirement of Symfony is >= 3.1)
                    if (method_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller', 'json')) { // symfony >= 3.1
                        if (true !== $choicesAsValues) {
                            throw new \RuntimeException(sprintf('The "choices_as_values" option for field `%s` in `%s` should not be used. Remove it and flip the contents of the "choices" option instead.', $fieldDescription->getName(), get_class($this->admin)));
                        }
                    }
                    $choices = array_flip($choices);
                }

                $fieldDescription->setOption('choices', $choices);
            }
        }

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
        if ($tab !== 'default') {
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
        if ($deleteEmptyTab && count($tabs[$tab]['groups']) == 0) {
            unset($tabs[$tab]);
        }

        $this->setTabs($tabs);
        $this->setGroups($groups);

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
