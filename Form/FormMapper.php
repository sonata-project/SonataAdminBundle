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
namespace Sonata\AdminBundle\Form;

use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Form\FormBuilder;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

/**
 * This class is use to simulate the Form API
 *
 */
class FormMapper extends BaseGroupedMapper
{
    protected $formBuilder;

    /**
     * @param \Sonata\AdminBundle\Builder\FormContractorInterface $formContractor
     * @param \Symfony\Component\Form\FormBuilder                 $formBuilder
     * @param \Sonata\AdminBundle\Admin\AdminInterface            $admin
     */
    public function __construct(FormContractorInterface $formContractor, FormBuilder $formBuilder, AdminInterface $admin)
    {
        parent::__construct($formContractor, $admin);
        $this->formBuilder    = $formBuilder;
    }

    /**
     * @param array $keys field names
     *
     * @return \Sonata\AdminBundle\Form\FormMapper
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
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function add($name, $type = null, array $options = array(), array $fieldDescriptionOptions = array())
    {
        if ($name instanceof FormBuilder) {
            $fieldName = $name->getName();
        } else {
            $fieldName = $name;
        }

        // "Dot" notation is not allowed as form name, but can be used as property path to access nested data.
        if (!$name instanceof FormBuilder && strpos($fieldName, '.')!==false && !isset($options['property_path'])) {
             $options['property_path'] = $fieldName;

             // fix the form name
             $fieldName = str_replace('.', '__', $fieldName);
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
            $name instanceof FormBuilder ? $name->getName() : $name,
            $fieldDescriptionOptions
        );

        // Note that the builder var is actually the formContractor:
        $this->builder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);

        if ($fieldName != $name) {
            $fieldDescription->setName($fieldName);
        }

        $this->admin->addFormFieldDescription($fieldName, $fieldDescription);

        if ($name instanceof FormBuilder) {
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
     * @param string $name
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function get($name)
    {
        return $this->formBuilder->get($name);
    }

    /**
     * @param string $key
     *
     * @return boolean
     */
    public function has($key)
    {
        return $this->formBuilder->has($key);
    }

    /**
     * @param string $key
     *
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function remove($key)
    {
        $this->admin->removeFormFieldDescription($key);
        $this->admin->removeFieldFromFormGroup($key);
        $this->formBuilder->remove($key);

        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormBuilder
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
     * @return \Symfony\Component\Form\FormBuilder
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
            if ($this->admin->hasFormFieldDescription($name)) {
                $this->admin->getFormFieldDescription($name)->setHelp($help);
            }
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
}
