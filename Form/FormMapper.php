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
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
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

    public function __construct(FormContractorInterface $formContractor, FormBuilder $formBuilder, AdminInterface $admin)
    {
        $this->formBuilder      = $formBuilder;
        $this->formContractor   = $formContractor;
        $this->admin            = $admin;
    }

//    /**
//     * The method add a new field to the provided FormBuilder, there are 4 ways to add new field :
//     *
//     *   - if $name is a string with no related FieldDescription, then the form will use the FormFactory
//     *     to instantiate a new Field
//     *   - if $name is a FormDescription, the method uses information defined in the FormDescription to
//     *     instantiate a new Field
//     *   - if $name is a FormBuilder, then a FieldDescriptionInterface is created, the FormBuilder is added to
//     *     the form
//     *   - if $name is a string with a related FieldDescription, then the method uses information defined in the
//     *     FormDescription to instantiate a new Field
//     *
//     * @throws \RuntimeException
//     * @param string $name
//     * @param array $fieldOptions
//     * @param array $fieldDescriptionOptions
//     * @return \Symfony\Component\Form\FieldInterface|void
//     */
//    public function add($name, array $fieldOptions = array(), array $fieldDescriptionOptions = array())
//    {
//        $fieldType = false;
//        if ($name instanceof FieldDescriptionInterface) {
//
//            $fieldDescription = $name;
//            $fieldDescription->mergeOptions($fieldDescriptionOptions);
//
//        } else if ($name instanceof FormBuilder) {
//
//            $fieldType   = $name;
//
//            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
//                $this->admin->getClass(),
//                $fieldType->getName(),
//                $fieldDescriptionOptions
//            );
//
//            $this->formContractor->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);
//
//            $this->admin->addFormFieldDescription($fieldType->getName(), $fieldDescription);
//
//        } else if (is_string($name) && !$this->admin->hasFormFieldDescription($name)) {
//
//            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
//                $this->admin->getClass(),
//                $name,
//                $fieldDescriptionOptions
//            );
//
//            // set default configuration
//            $this->formContractor->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);
//
//            // add the FieldDescription
//            $this->admin->addFormFieldDescription($name, $fieldDescription);
//
//        } else if (is_string($name) && $this->admin->hasFormFieldDescription($name)) {
//            $fieldDescription = $this->admin->getFormFieldDescription($name);
//
//            // update configuration
//            $this->formContractor->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);
//
//        } else {
//
//            throw new \RuntimeException('invalid state');
//        }
//
//        $fieldDescription->mergeOption('form_field_options', $fieldOptions);
//
//        // nothing to build as a Field is provided
//        if ($fieldType) {
//            $this->formBuilder->add($fieldType);
//        }
//
//        // add the field with the FormBuilder
//        $this->formContractor->addField(
//            $this->formBuilder,
//            $fieldDescription
//        );
//
//        return $this;
//    }

    /**
     * @param string $name
     * @param string $type
     * @param array $options
     * @param array $fieldDescriptionOptions
     * @return \Symfony\Component\Form\FormInterface
     */
    public function add($name, $type = null, array $options = array(), array $fieldDescriptionOptions = array())
    {
        if (!isset($fieldDescriptionOptions['type']) && is_string($type)) {
            $fieldDescriptionOptions['type'] = $type;
        }

        $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
            $this->admin->getClass(),
            $name instanceof FormBuilder ? $name->getName() : $name,
            $fieldDescriptionOptions
        );

        $this->formContractor->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);

        $options = $this->formContractor->getDefaultOptions($type, $fieldDescription, $options);

        $this->admin->addFormFieldDescription($name instanceof FormBuilder ? $name->getName() : $name, $fieldDescription);

        if ($name instanceof FormBuilder) {
            $this->formBuilder->add($name);
        } else {
            $this->formBuilder->add($name, $type, $options);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return \Symfony\Component\Form\FieldInterface
     */
    public function get($name)
    {
        return $this->formBuilder->get($name);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->formBuilder->has($key);
    }

    /**
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        $this->admin->removeFormFieldDescription($key);
        $this->formBuilder->remove($key);
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
     * @param mixed $type
     * @param array $options
     * @return void
     */
    public function create($name, $type = null, array $options = array())
    {
        return $this->formBuilder->create($name, $type, $options);
    }

    public function setHelps(array $helps = array())
    {
        foreach($helps as $name => $help) {
            if ($this->admin->hasFormFieldDescription($name)) {
                $this->admin->getFormFieldDescription($name)->setHelp($help);
            }
        }

        return $this;
    }
}