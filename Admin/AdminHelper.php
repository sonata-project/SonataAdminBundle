<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Util\FormViewIterator;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AdminHelper
{
    protected $pool;

    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @throws \RuntimeException
     *
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param string                              $elementId
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function getChildFormBuilder(FormBuilder $formBuilder, $elementId)
    {
        $recurciveElementId = substr($elementId, strpos($elementId, '_') + 1);

        foreach (new FormBuilderIterator($formBuilder) as $name => $formBuilder) {
            if ($name == $elementId) {
                return $formBuilder;
            }

            if ($formBuilder->count()) {
                $formBuilder = $this->getChildFormBuilder($formBuilder, $recurciveElementId);
                if ($formBuilder) {
                    return $formBuilder;
                }
            }
        }

        return null;
    }


    /**
     * @param AdminInterface $admin
     * @param array     $elements
     * @return null|\Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    protected function getFormFieldDescription(AdminInterface $admin, array $elements)
    {
        if (count($elements) > 0) {
            $elementId = array_shift($elements);
            $fieldDescription = $admin->getFormFieldDescription($elementId);
            $recursionAdmin = $fieldDescription->getAssociationAdmin();

            if ($fieldDescription && $recursionAdmin) {
                $newFieldDescription = $this->getFormFieldDescription($recursionAdmin, $elements);
                if ($newFieldDescription) {
                    return $newFieldDescription;
                }
            }

            return $fieldDescription;
        }

        return null;
    }


    /**
     * @param mixed $formData
     * @param array $elements
     * @return mixed
     */
    protected function getFormFieldData($formData, array $elements)
    {
        if (count($elements) > 0) {
            $elementId = array_shift($elements);
            $propertyAccessor = new PropertyAccessor();
            $object = $propertyAccessor->getValue($formData, $elementId);
            if ($object) {
                $newFormData = $this->getFormFieldData($object, $elements);
                if ($newFormData) {
                    return $newFormData;
                }
            }

            return $formData;
        }

        return null;
    }

    /**
     * @param \Symfony\Component\Form\FormView $formView
     * @param string                           $elementId
     *
     * @return null|\Symfony\Component\Form\FormView
     */
    public function getChildFormView(FormView $formView, $elementId)
    {
        foreach (new \RecursiveIteratorIterator(new FormViewIterator($formView), \RecursiveIteratorIterator::SELF_FIRST) as $name => $formView) {
            if ($name === $elementId) {
                return $formView;
            }
        }

        return null;
    }

    /**
     * @deprecated
     *
     * @param string $code
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getAdmin($code)
    {
        return $this->pool->getInstance($code);
    }

    /**
     * Note:
     *   This code is ugly, but there is no better way of doing it.
     *   For now the append form element action used to add a new row works
     *   only for direct FieldDescription (not nested one)
     *
     * @throws \RuntimeException
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param object                                   $subject
     * @param string                                   $elementId
     *
     * @return array
     */
    public function appendFormFieldElement(AdminInterface $admin, $subject, $elementId)
    {
        // retrieve the subject
        $formBuilder = $admin->getFormBuilder();

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->bind($admin->getRequest());

        $elements = explode('_', substr($elementId, strpos($elementId, '_') + 1));

        // get the field element
        $childFormBuilder = $this->getChildFormBuilder($formBuilder, $elementId);

        // retrieve the FieldDescription
        $fieldDescription = $this->getFormFieldDescription($admin, $elements);

        try {
            $value = $fieldDescription->getValue($form->getData());
        } catch (NoValueException $e) {
            $value = null;
        }

        // retrieve the posted data
        $data = $admin->getRequest()->get($formBuilder->getName());

        if (!isset($data[$childFormBuilder->getName()])) {
            $data[$childFormBuilder->getName()] = array();
        }

        $objectCount = count($value);
        $postCount   = count($data[$childFormBuilder->getName()]);

        // add new elements to the subject
        while ($objectCount < $postCount) {
            // append a new instance into the object
            $this->addNewInstance($this->getFormFieldData($form->getData(), $elements), $fieldDescription);
            $objectCount++;
        }

        $this->addNewInstance($this->getFormFieldData($form->getData(), $elements), $fieldDescription);

        $finalForm = $admin->getFormBuilder()->getForm();
        $finalForm->setData($subject);

        // bind the data
        $finalForm->setData($form->getData());

        return array($fieldDescription, $finalForm);
    }

    /**
     * Add a new instance to the related FieldDescriptionInterface value
     *
     * @param object                                              $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @throws \RuntimeException
     */
    public function addNewInstance($object, FieldDescriptionInterface $fieldDescription)
    {
        $instance = $fieldDescription->getAssociationAdmin()->getNewInstance();
        $mapping  = $fieldDescription->getAssociationMapping();

        $propertyAccessor = new PropertyAccessor();
        $propertyAccessor->setValue($object, $mapping['fieldName'], [$instance]);
    }
}
