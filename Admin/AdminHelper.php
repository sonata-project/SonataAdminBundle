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
     * @param \Symfony\Component\Form\FormBuilder $formBuider
     * @param  $elementId
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function getChildFormBuilder(FormBuilder $formBuider, $elementId)
    {
        // todo : warning this introduce a bug if the field name = 'field_name',
        //        add a check to field will always be 'fieldName'
        $elements = explode('_', $elementId);

        // always remove the first element : form's name
        array_shift($elements);

        while ($elementName = array_shift($elements)) {
            if (!$formBuider->has($elementName)) {
                throw new \RuntimeException(sprintf('The element `%s` does not exists', $elementName));
            }

            $formBuider = $formBuider->get($elementName);
        }

        return $formBuider;
    }

    /**
     * @param string $code
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
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param  $elementId
     * @return void
     */
    public function appendFormFieldElement(AdminInterface $admin, $elementId)
    {
        // retrieve the subject
        $formBuilder = $admin->getFormBuilder();

        $form  = $formBuilder->getForm();
        $form->bindRequest($admin->getRequest());

        // get the field element
        $childFormBuilder = $this->getChildFormBuilder($formBuilder, $elementId);

        // retrieve the FieldDescription
        $fieldDescription = $admin->getFormFieldDescription($childFormBuilder->getName());

        $value = $fieldDescription->getValue($form->getData());

        // retrieve the posted data
        $data = $admin->getRequest()->get($formBuilder->getName());

        if (!isset($data[$childFormBuilder->getName()])) {
            $data[$childFormBuilder->getName()] = array();
        }

        $objectCount   = count($value);
        $postCount     = count($data[$childFormBuilder->getName()]);

        $fields = array_keys($fieldDescription->getAssociationAdmin()->getFormFieldDescriptions());

        // for now, not sure how to do that
        $value = array();
        foreach ($fields as $name) {
            $value[$name] = '';
        }

        // add new elements to the subject
        while($objectCount < $postCount) {
            // append a new instance into the object
            $this->addNewInstance($form->getData(), $fieldDescription);
            $objectCount++;
        }

        $this->addNewInstance($form->getData(), $fieldDescription);
        $data[$childFormBuilder->getName()][] = $value;

        $form = $admin->getFormBuilder($form->getData())->getForm();

        // bind the data
        $form->bind($data);

        $admin->setSubject($form->getData());

        return array($fieldDescription, $formBuilder);
    }

    /**
     * Add a new instance to the related FieldDescriptionInterface value
     *
     * @param object $object
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addNewInstance($object, FieldDescriptionInterface $fieldDescription)
    {
        $instance = $fieldDescription->getAssociationAdmin()->getNewInstance();
        $mapping  = $fieldDescription->getAssociationMapping();

        $method = sprintf('add%s', $this->camelize($mapping['fieldName']));

        $object->$method($instance);
    }

    /**
     * Camelize a string
     *
     * @static
     * @param string $property
     * @return string
     */
    public function camelize($property)
    {
       return preg_replace(array('/(^|_| )+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }
}