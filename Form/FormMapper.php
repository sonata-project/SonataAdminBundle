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

use Sonata\AdminBundle\Builder\FormBuilderInterface;
use Sonata\AdminBundle\Admin\Admin;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FieldInterface;
use Symfony\Component\Form\FormContextInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Form\FieldFactory\FieldFactoryInterface;


/**
 * This class is use to simulate the Form API
 *
 */
class FormMapper
{
    protected $formBuilder;

    protected $form;

    protected $admin;

    public function __construct(FormBuilderInterface $formBuilder, Form $form, Admin $admin)
    {
        $this->formBuilder = $formBuilder;
        $this->form = $form;
        $this->admin = $admin;
    }

    public function add($name, array $fieldOptions = array(), array $fieldDescriptionOptions = array())
    {

        $field = false;
        if ($name instanceof FieldDescriptionInterface) {

            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);

        } else if ($name instanceof FieldInterface) {

            $field   = $name;

            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $field->getKey(),
                $fieldDescriptionOptions
            );

            $this->formBuilder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);

            $this->admin->addFormFieldDescription($field->getKey(), $fieldDescription);

        } else if (is_string($name) && !$this->admin->hasFormFieldDescription($name)) {

            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $name,
                $fieldDescriptionOptions
            );

            // set default configuration
            $this->formBuilder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);

            // add the FieldDescription
            $this->admin->addFormFieldDescription($name, $fieldDescription);

        } else if (is_string($name) && $this->admin->hasFormFieldDescription($name)) {
            $fieldDescription = $this->admin->getFormFieldDescription($name);

            // update configuration
            $this->formBuilder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);

        } else {

            throw new \RuntimeException('invalid state');
        }

        $fieldDescription->mergeOption('form_field_options', $fieldOptions);

        // nothing to build as a Field is provided
        if ($field) {
            return $this->form->add($field);
        }

        // add the field with the FormBuilder
        return $this->formBuilder->addField(
            $this->form,
            $fieldDescription
        );
    }

    public function get($name)
    {
        return $this->form->get($name);
    }

    public function has($key)
    {
        return $this->form->has($key);
    }

    public function remove($key)
    {
        $this->admin->removeFormFieldDescription($key);
        $this->form->remove($key);
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return \Sonata\AdminBundle\Admin\Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}