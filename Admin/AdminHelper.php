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
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
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
     * NEXT_MAJOR: remove this method.
     *
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

        $method = sprintf('add%s', Inflector::classify($mapping['fieldName']));

        if (!method_exists($object, $method)) {
            $method = rtrim($method, 's');

            if (!method_exists($object, $method)) {
                $method = sprintf('add%s', Inflector::classify(Inflector::singularize($mapping['fieldName'])));

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
     * NEXT_MAJOR: remove this method.
     *
     * @static
     *
     * @param string $property
     *
     * @return string
     *
     * @deprecated Deprecated since version 3.1. Use \Doctrine\Common\Inflector\Inflector::classify() instead
     */
    public function camelize($property)
    {
        @trigger_error(
            sprintf(
                'The %s method is deprecated since 3.1 and will be removed in 4.0. '.
                'Use \Doctrine\Common\Inflector\Inflector::classify() instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return Inflector::classify($property);
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

        $idWithoutIdentifier = preg_replace('/^[^_]*_/', '', $elementId);
        $initialPath = preg_replace('#(_(\d+)_)#', '[$2]_', $idWithoutIdentifier);

        $parts = explode('_', $initialPath);
        $totalPath = '';
        $currentPath = '';

        foreach ($parts as $part) {
            $currentPath .= empty($currentPath) ? $part : '_'.$part;
            $separator = empty($totalPath) ? '' : '.';

            if ($this->pathExists($propertyAccessor, $entity, $totalPath.$separator.$currentPath)) {
                $totalPath .= $separator.$currentPath;
                $currentPath = '';
            }
        }

        if (!empty($currentPath)) {
            throw new \Exception(sprintf('Could not get element id from %s Failing part: %s', $elementId, $currentPath));
        }

        return $totalPath;
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
        }

        return $this->getEntityClassName($associationAdmin, $elements);
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
        } catch (UnexpectedTypeException $e) {
            return false;
        }
    }
}
