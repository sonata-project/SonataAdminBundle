<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Sonata\AdminBundle\Util\FormViewIterator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;

/**
 * Class AdminHelper.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminHelper
{
    /**
     * @var Pool
     */
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
     * @param FormBuilderInterface $formBuilder
     * @param string               $elementId
     *
     * @return FormBuilderInterface
     */
    public function getChildFormBuilder(FormBuilderInterface $formBuilder, $elementId)
    {
        foreach (new \RecursiveIteratorIterator(new FormBuilderIterator($formBuilder), \RecursiveIteratorIterator::SELF_FIRST) as $name => $formBuilder) {
            if ($name == $elementId) {
                return $formBuilder;
            }
        }

        return;
    }

    /**
     * @param FormView $formView
     * @param string   $elementId
     *
     * @return null|FormView
     */
    public function getChildFormView(FormView $formView, $elementId)
    {
        foreach (new \RecursiveIteratorIterator(new FormViewIterator($formView), \RecursiveIteratorIterator::SELF_FIRST) as $name => $formView) {
            if ($name === $elementId) {
                return $formView;
            }
        }

        return;
    }

    /**
     * @deprecated
     *
     * @param string $code
     *
     * @return AdminInterface
     */
    public function getAdmin($code)
    {
        return $this->pool->getInstance($code);
    }

    /**
     * @throws \RuntimeException
     *
     * @param AdminInterface $admin
     * @param object         $subject
     * @param string         $elementId
     *
     * @return array
     */
    public function appendFormFieldElement(AdminInterface $admin, $subject, $elementId)
    {
        // retrieve the subject
        $formBuilder = $admin->getFormBuilder();

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->handleRequest($admin->getRequest());

        // get the field element
        $childFormBuilder = $this->getChildFormBuilder($formBuilder, $elementId);

        // retrieve the FieldDescription
        $fieldDescription = $admin->getFormFieldDescription($childFormBuilder->getName());

        if (null === $fieldDescription) {
            //its null because the form field couldnt be found in the main form, so search the childs with the fieldpath
            $fieldPath = explode('_', $elementId);
            array_shift($fieldPath);

            $fieldDescription = $this->getChildFieldDescription($admin, $childFormBuilder, $fieldPath);
        }

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
        $postCount = count($data[$childFormBuilder->getName()]);

        $fields = array_keys($fieldDescription->getAssociationAdmin()->getFormFieldDescriptions());

        // for now, not sure how to do that
        $value = array();
        foreach ($fields as $name) {
            $value[$name] = '';
        }

        // add new elements to the subject
        while ($objectCount < $postCount) {
            // append a new instance into the object
            $this->addNewInstance($form->getData(), $fieldDescription, isset($fieldPath) ? $fieldPath : array());
            ++$objectCount;
        }

        $this->addNewInstance($form->getData(), $fieldDescription, isset($fieldPath) ? $fieldPath : array());

        $finalForm = $admin->getFormBuilder()->getForm();
        $finalForm->setData($subject);

        // bind the data
        $finalForm->setData($form->getData());

        return array($fieldDescription, $finalForm);
    }

    /**
     * Add a new instance to the related FieldDescriptionInterface value.
     *
     * @param object                    $object
     * @param FieldDescriptionInterface $fieldDescription
     * @param array                     $fieldPath
     *
     * @throws \RuntimeException
     */
    public function addNewInstance($object, FieldDescriptionInterface $fieldDescription, $fieldPath = array())
    {
        if ($fieldPath) {
            $object = $this->getChildObject($object, $fieldPath);
        }

        $instance = $fieldDescription->getAssociationAdmin()->getNewInstance();
        $mapping = $fieldDescription->getAssociationMapping();

        $method = sprintf('add%s', $this->camelize($mapping['fieldName']));

        if (!method_exists($object, $method)) {
            $method = rtrim($method, 's');

            if (!method_exists($object, $method)) {
                $method = sprintf('add%s', $this->camelize(Inflector::singularize($mapping['fieldName'])));

                if (!method_exists($object, $method)) {
                    throw new \RuntimeException(sprintf('Please add a %s method in the %s class!', $method, ClassUtils::getClass($object)));
                }
            }
        }

        $object->$method($instance);
    }

    /**
     * Camelize a string.
     *
     * @static
     *
     * @param string $property
     *
     * @return string
     */
    public function camelize($property)
    {
        return BaseFieldDescription::camelize($property);
    }

    /**
     * Recursively iterate through all child associations to find the correct FieldDescription.
     *
     * @param AdminInterface       $admin
     * @param FormBuilderInterface $childFormBuilder
     * @param array                $fieldPath
     *
     * @return FieldDescriptionInterface
     */
    private function getChildFieldDescription(AdminInterface $admin, FormBuilderInterface $childFormBuilder, array $fieldPath)
    {
        $formFieldDescription = $admin->getFormFieldDescription(array_shift($fieldPath));

        if ($childField = $formFieldDescription->getAssociationAdmin()->getFormFieldDescription($childFormBuilder->getName())) {
            return $childField;
        }

        return $this->getChildFieldDescription($formFieldDescription->getAssociationAdmin(), $childFormBuilder, $fieldPath);
    }

    /**
     * Recursively iterate through all child associations to find the correct entity.
     *
     * @param object $object
     * @param array  $fieldPath
     *
     * @return object
     */
    private function getChildObject($object, array $fieldPath)
    {
        $current = array_shift($fieldPath);
        $method = sprintf('get%s', $this->camelize($current));

        if (1 === count($fieldPath)) { //only 1 entry left means we reached the correct
            return $object->$method();
        }

        return $this->getChildObject($object->$method(), $fieldPath);
    }
}
