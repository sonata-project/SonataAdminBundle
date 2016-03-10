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
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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
        foreach (new FormBuilderIterator($formBuilder) as $name => $formBuilder) {
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
     * Note:
     *   This code is ugly, but there is no better way of doing it.
     *   For now the append form element action used to add a new row works
     *   only for direct FieldDescription (not nested one).
     *
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

        //Child form not found (probably nested one)
        //if childFormBuilder was not found resulted in fatal error getName() method call on non object
        if (!$childFormBuilder) {
            $propertyAccessor = $this->pool->getPropertyAccessor();
            $entity = $admin->getSubject();

            $path = $this->getElementAccessPath($elementId, $entity);

            $collection = $propertyAccessor->getValue($entity, $path);

            if ($collection instanceof \Doctrine\ORM\PersistentCollection || $collection instanceof \Doctrine\ODM\MongoDB\PersistentCollection) {
                //since doctrine 2.4
                $entityClassName = $collection->getTypeClass()->getName();
            } elseif ($collection instanceof \Doctrine\Common\Collections\Collection) {
                $entityClassName = $this->getEntityClassName($admin, explode('.', preg_replace('#\[\d*?\]#', '', $path)));
            } else {
                throw new \Exception('unknown collection class');
            }

            $collection->add(new $entityClassName());
            $propertyAccessor->setValue($entity, $path, $collection);

            $fieldDescription = null;
        } else {
            // retrieve the FieldDescription
            $fieldDescription = $admin->getFormFieldDescription($childFormBuilder->getName());

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
                $this->addNewInstance($form->getData(), $fieldDescription);
                ++$objectCount;
            }

            $this->addNewInstance($form->getData(), $fieldDescription);
        }

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
     *
     * @throws \RuntimeException
     */
    public function addNewInstance($object, FieldDescriptionInterface $fieldDescription)
    {
        $instance = $fieldDescription->getAssociationAdmin()->getNewInstance();
        $mapping = $fieldDescription->getAssociationMapping();

        $method = sprintf('add%s', $this->camelize($mapping['fieldName']));

        if (!method_exists($object, $method)) {
            $method = rtrim($method, 's');

            if (!method_exists($object, $method)) {
                $method = sprintf('add%s', $this->camelize(Inflector::singularize($mapping['fieldName'])));

                if (!method_exists($object, $method)) {
                    throw new \RuntimeException(sprintf('Please add a method %s in the %s class!', $method, ClassUtils::getClass($object)));
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
     * Recursively find the class name of the admin responsible for the element at the end of an association chain.
     *
     * @param AdminInterface $admin
     * @param array          $elements
     *
     * @return string
     */
    protected function getEntityClassName(AdminInterface $admin, $elements)
    {
        $element = array_shift($elements);
        $associationAdmin = $admin->getFormFieldDescription($element)->getAssociationAdmin();
        if (count($elements) == 0) {
            return $associationAdmin->getClass();
        } else {
            return $this->getEntityClassName($associationAdmin, $elements);
        }
    }

    /**
     * Get access path to element which works with PropertyAccessor.
     *
     * @param string $elementId expects string in format used in form id field. (uniqueIdentifier_model_sub_model or uniqueIdentifier_model_1_sub_model etc.)
     * @param mixed  $entity
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getElementAccessPath($elementId, $entity)
    {
        $propertyAccessor = $this->pool->getPropertyAccessor();

        $idWithoutUniqueIdentifier = implode('_', explode('_', substr($elementId, strpos($elementId, '_') + 1)));

        //array access of id converted to format which PropertyAccessor understands
        $initialPath = preg_replace('#(_(\d+)_)#', '[$2]', $idWithoutUniqueIdentifier);

        $parts = preg_split('#\[\d+\]#', $initialPath);

        $partReturnValue = $returnValue = '';
        $currentEntity = $entity;

        foreach ($parts as $key => $value) {
            $subParts = explode('_', $value);
            $id = '';
            $dot = '';

            foreach ($subParts as $subValue) {
                $id .= ($id) ? '_'.$subValue : $subValue;

                if ($this->pathExists($propertyAccessor, $currentEntity, $partReturnValue.$dot.$id)) {
                    $partReturnValue .= $dot.$id;
                    $dot = '.';
                    $id = '';
                } else {
                    $dot = '';
                }
            }

            if ($dot !== '.') {
                throw new \Exception(sprintf('Could not get element id from %s Failing part: %s', $elementId, $subValue));
            }

            //check if array access was in this location originally
            preg_match("#$value\[(\d+)#", $initialPath, $matches);

            if (isset($matches[1])) {
                $partReturnValue .= '['.$matches[1].']';
            }

            $returnValue .= $returnValue ? '.'.$partReturnValue : $partReturnValue;
            $partReturnValue = '';

            if (isset($parts[$key + 1])) {
                $currentEntity = $propertyAccessor->getValue($entity, $returnValue);
            }
        }

        return $returnValue;
    }

    /**
     * Check if given path exists in $entity.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     * @param mixed                     $entity
     * @param string                    $path
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    private function pathExists(PropertyAccessorInterface $propertyAccessor, $entity, $path)
    {
        // Symfony <= 2.3 did not have isReadable method for PropertyAccessor
        if (method_exists($propertyAccessor, 'isReadable')) {
            return $propertyAccessor->isReadable($entity, $path);
        }

        try {
            $propertyAccessor->getValue($entity, $path);

            return true;
        } catch (NoSuchPropertyException $e) {
            return false;
        }
    }
}
