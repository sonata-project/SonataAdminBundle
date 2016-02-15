<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class FormMapper
 * This class is use to simulate the Form API.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class FormMapper extends BaseGroupedMapper
{
    /**
     * @var FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * @param FormContractorInterface $formContractor
     * @param FormBuilderInterface    $formBuilder
     * @param AdminInterface          $admin
     */
    public function __construct(FormContractorInterface $formContractor, FormBuilderInterface $formBuilder, AdminInterface $admin)
    {
        parent::__construct($formContractor, $admin);
        $this->formBuilder = $formBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function reorder(array $keys)
    {
        $this->admin->reorderFormGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param array  $fieldDescriptionOptions
     *
     * @return $this
     */
    public function add($name, $type = null, array $options = array(), array $fieldDescriptionOptions = array())
    {
        if ($this->apply !== null && !$this->apply) {
            return $this;
        }

        if ($name instanceof FormBuilderInterface) {
            $fieldName = $name->getName();
        } else {
            $fieldName = $name;
        }

        // "Dot" notation is not allowed as form name, but can be used as property path to access nested data.
        if (!$name instanceof FormBuilderInterface && strpos($fieldName, '.') !== false && !isset($options['property_path'])) {
            $options['property_path'] = $fieldName;

             // fix the form name
             $fieldName = str_replace('.', '__', $fieldName);
        }

        // change `collection` to `sonata_type_native_collection` form type to
        // avoid BC break problems
        if ($type === 'collection' || $type === 'Symfony\Component\Form\Extension\Core\Type\CollectionType') {
            // the field name is used to preserve Symfony <2.8 compatibility, the FQCN should be used instead
            $type = 'sonata_type_native_collection';
        }

        $label = $fieldName;

        $group = $this->addFieldToCurrentGroup($label);

        if (!isset($fieldDescriptionOptions['type']) && is_string($type)) {
            $fieldDescriptionOptions['type'] = $type;
        }

        if ($group['translation_domain'] && !isset($fieldDescriptionOptions['translation_domain'])) {
            $fieldDescriptionOptions['translation_domain'] = $group['translation_domain'];
        }

        $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
            $this->admin->getClass(),
            $name instanceof FormBuilderInterface ? $name->getName() : $name,
            $fieldDescriptionOptions
        );

        // Note that the builder var is actually the formContractor:
        $this->builder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);

        if ($fieldName != $name) {
            $fieldDescription->setName($fieldName);
        }

        $this->admin->addFormFieldDescription($fieldName, $fieldDescription);

        if ($name instanceof FormBuilderInterface) {
            $this->formBuilder->add($name);
        } else {
            // Note that the builder var is actually the formContractor:
            $options = array_replace_recursive($this->builder->getDefaultOptions($type, $fieldDescription), $options);

            // be compatible with mopa if not installed, avoid generating an exception for invalid option
            // force the default to false ...
            if (!isset($options['label_render'])) {
                $options['label_render'] = false;
            }

            if (!isset($options['label'])) {
                $options['label'] = $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'form', 'label');
            }

            $help = null;
            if (isset($options['help'])) {
                $help = $options['help'];
                unset($options['help']);
            }

            $this->formBuilder->add($fieldDescription->getName(), $type, $options);

            if (null !== $help) {
                $this->admin->getFormFieldDescription($fieldDescription->getName())->setHelp($help);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->formBuilder->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->formBuilder->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->admin->removeFormFieldDescription($key);
        $this->admin->removeFieldFromFormGroup($key);
        $this->formBuilder->remove($key);

        return $this;
    }

    /**
     * @return FormBuilderInterface
     *                              Removes a group.
     *
     * @param string $group          The group to delete
     * @param string $tab            The tab the group belongs to, defaults to 'default'
     * @param bool   $deleteEmptyTab Whether or not the Tab should be deleted, when the deleted group leaves the tab empty after deletion
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
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
    }

    /**
     * @param string $name
     * @param mixed  $type
     * @param array  $options
     *
     * @return FormBuilderInterface
     */
    public function create($name, $type = null, array $options = array())
    {
        return $this->formBuilder->create($name, $type, $options);
    }

    /**
     * @param array $helps
     *
     * @return FormMapper
     */
    public function setHelps(array $helps = array())
    {
        foreach ($helps as $name => $help) {
            $this->addHelp($name, $help);
        }

        return $this;
    }

    /**
     * @param $name
     * @param $help
     *
     * @return FormMapper
     */
    public function addHelp($name, $help)
    {
        if ($this->admin->hasFormFieldDescription($name)) {
            $this->admin->getFormFieldDescription($name)->setHelp($help);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getGroups()
    {
        return $this->admin->getFormGroups();
    }

    /**
     * {@inheritdoc}
     */
    protected function setGroups(array $groups)
    {
        $this->admin->setFormGroups($groups);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTabs()
    {
        return $this->admin->getFormTabs();
    }

    /**
     * {@inheritdoc}
     */
    protected function setTabs(array $tabs)
    {
        $this->admin->setFormTabs($tabs);
    }
}
