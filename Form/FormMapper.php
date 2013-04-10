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

/**
 * This class is use to simulate the Form API
 *
 */
class FormMapper
{
    protected $formBuilder;

    protected $formContractor;

    protected $admin;

    protected $currentField;

    protected $currentFieldPosition;

    protected $currentGroup;

    const FIRST = 'first';
    const BEFORE = 'before';
    const AFTER = 'after';
    const LAST = 'last';

    /**
     * @param \Sonata\AdminBundle\Builder\FormContractorInterface $formContractor
     * @param \Symfony\Component\Form\FormBuilder                 $formBuilder
     * @param \Sonata\AdminBundle\Admin\AdminInterface            $admin
     */
    public function __construct(FormContractorInterface $formContractor, FormBuilder $formBuilder, AdminInterface $admin)
    {
        $this->formBuilder    = $formBuilder;
        $this->formContractor = $formContractor;
        $this->admin          = $admin;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function with($name, array $options = array())
    {
        $formGroups = $this->admin->getFormGroups();
        if (!isset($formGroups[$name])) {
            $formGroups[$name] = array();
        }

        $formGroups[$name] = array_merge(array(
            'collapsed'   => false,
            'fields'      => array(),
            'description' => false
        ), $formGroups[$name], $options);

        $this->admin->setFormGroups($formGroups);

        $this->setCurrentGroup($name);

        return $this;
    }

    /**
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function end()
    {
        if ($this->getCurrentField()) {
            $this
                ->setCurrentField(null)
                ->setCurrentFieldPosition(null)
            ;
        } else {
            $this->setCurrentGroup(null);
        }

        return $this;
    }

    public function first()
    {
        return $this->setCurrentFieldPosition($this::FIRST);
    }

    public function before($field = null)
    {
        return $this
            ->setCurrentField($field)
            ->setCurrentFieldPosition($this::BEFORE)
        ;
    }

    public function after($field = null)
    {
        return $this
            ->setCurrentField($field)
            ->setCurrentFieldPosition($this::AFTER)
        ;
    }

    public function last()
    {
        return $this->setCurrentFieldPosition($this::LAST);
    }

    /**
     * @param string $field field name
     * @param string $position first, last, before, after
     * @param string $target field name
     *
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function move($field, $position, $target = null)
    {
        $groups = $this->admin->getFormGroups();

        $fields = array_diff(
            array_keys($groups[$this->currentGroup]['fields']),
            array($field)
        );

        switch ($position) {
            case $this::FIRST:
                $offset = 0;

                break;

            case $this::BEFORE:
                if ($target) {
                    $offset = array_search($target, $fields);
                }

                break;

            case $this::AFTER:
                if ($target) {
                    $offset = array_search($target, $fields) + 1;
                }

                break;

            case $this::LAST:
                $offset = count($fields);
        }

        if (isset($offset)) {
            array_splice($fields, $offset, 0, $field);
        }

        return $this->reorder($fields);
    }

    /**
     * @param array $keys field names
     *
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function reorder(array $keys)
    {
        if (!$this->currentGroup) {
            $this->with($this->admin->getLabel());
        }

        $this->admin->reorderFormGroup($this->currentGroup, $keys);

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
        if (!$this->currentGroup) {
            $this->with($this->admin->getLabel());
        }

        $label = $name instanceof FormBuilder ? $name->getName() : $name;

        $formGroups                                        = $this->admin->getFormGroups();
        $formGroups[$this->currentGroup]['fields'][$label] = $label;
        $this->admin->setFormGroups($formGroups);

        if (!isset($fieldDescriptionOptions['type']) && is_string($type)) {
            $fieldDescriptionOptions['type'] = $type;
        }

        $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
            $this->admin->getClass(),
            $name instanceof FormBuilder ? $name->getName() : $name,
            $fieldDescriptionOptions
        );

        $this->formContractor->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);

        $this->admin->addFormFieldDescription($name instanceof FormBuilder ? $name->getName() : $name, $fieldDescription);

        if ($name instanceof FormBuilder) {
            $this->formBuilder->add($name);
        } else {
            $options = array_replace_recursive($this->formContractor->getDefaultOptions($type, $fieldDescription), $options);

            if (!isset($options['label'])) {
                $options['label'] = $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'form', 'label');
            }

            $help = null;
            if (isset($options['help'])) {
                $help = $options['help'];
                unset($options['help']);
            }

            $this->formBuilder->add($name, $type, $options);

            if (null !== $help) {
                $this->admin->getFormFieldDescription($name)->setHelp($help);
            }
        }

        if ($this->getCurrentFieldPosition()) {
            $this->move($name, $this->getCurrentFieldPosition(), $this->getCurrentField());

            $this
                ->setCurrentField($name)
                ->setCurrentFieldPosition($this::AFTER)
            ;
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
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
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

    protected function getCurrentField()
    {
        return $this->currentField;
    }

    protected function setCurrentField($field)
    {
        $this->currentField = $field;

        return $this;
    }

    protected function getCurrentFieldPosition()
    {
        return $this->currentFieldPosition;
    }

    protected function setCurrentFieldPosition($position)
    {
        $this->currentFieldPosition = $position;

        return $this;
    }

    protected function getCurrentGroup()
    {
        return $this->currentGroup;
    }

    protected function setCurrentGroup($group)
    {
        $this->currentGroup = $group;

        return $this;
    }
}
