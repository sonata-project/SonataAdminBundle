<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\ORM\PersistentCollection as DoctrinePersistentCollection;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Sonata\AdminBundle\Util\FormViewIterator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminHelper
{
    /**
     * @var string
     */
    private const FORM_FIELD_DELETE = '_delete';

    /**
     * @var Pool
     */
    protected $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param string $elementId
     *
     * @throws \RuntimeException
     *
     * @return FormBuilderInterface|null
     */
    public function getChildFormBuilder(FormBuilderInterface $formBuilder, $elementId)
    {
        foreach (new FormBuilderIterator($formBuilder) as $name => $formBuilder) {
            if ($name === $elementId) {
                return $formBuilder;
            }
        }

        return null;
    }

    /**
     * @param string $elementId
     *
     * @return FormView|null
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
     *
     * @param object $subject
     * @param string $elementId
     *
     * @throws \RuntimeException
     * @throws \Exception
     *
     * @return array
     */
    public function appendFormFieldElement(AdminInterface $admin, $subject, $elementId)
    {
        // child rows marked as toDelete
        $toDelete = [];
        // retrieve the subject
        $formBuilder = $admin->getFormBuilder();

        // get the field element
        $childFormBuilder = $this->getChildFormBuilder($formBuilder, $elementId);

        if ($childFormBuilder) {
            $formData = $admin->getRequest()->get($formBuilder->getName(), []);
            if (\array_key_exists($childFormBuilder->getName(), $formData)) {
                $formData = $admin->getRequest()->get($formBuilder->getName(), []);
                $i = 0;
                foreach ($formData[$childFormBuilder->getName()] as $name => &$field) {
                    $toDelete[$i] = false;
                    if (\array_key_exists(self::FORM_FIELD_DELETE, $field)) {
                        $toDelete[$i] = true;
                        unset($field[self::FORM_FIELD_DELETE]);
                    }
                    ++$i;
                }
            }
            $admin->getRequest()->request->set($formBuilder->getName(), $formData);
        }

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->handleRequest($admin->getRequest());

        //Child form not found (probably nested one)
        //if childFormBuilder was not found resulted in fatal error getName() method call on non object
        if (!$childFormBuilder) {
            $propertyAccessor = $this->pool->getPropertyAccessor();
            $entity = $admin->getSubject();

            $path = $this->getElementAccessPath($elementId, $entity);

            $collection = $propertyAccessor->getValue($entity, $path);

            if ($collection instanceof DoctrinePersistentCollection || $collection instanceof PersistentCollection) {
                //since doctrine 2.4
                $entityClassName = $collection->getTypeClass()->getName();
            } elseif ($collection instanceof Collection) {
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
                $data[$childFormBuilder->getName()] = [];
            }

            $objectCount = null === $value ? 0 : \count($value);
            $postCount = \count($data[$childFormBuilder->getName()]);

            $fields = array_keys($fieldDescription->getAssociationAdmin()->getFormFieldDescriptions());

            // for now, not sure how to do that
            $value = [];
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

        // back up delete field
        if (\count($toDelete) > 0) {
            $i = 0;
            foreach ($finalForm->get($childFormBuilder->getName()) as $childField) {
                if ($childField->has(self::FORM_FIELD_DELETE)) {
                    $childField->get(self::FORM_FIELD_DELETE)->setData($toDelete[$i] ?? false);
                }
                ++$i;
            }
        }

        return [$fieldDescription, $finalForm];
    }

    /**
     * Add a new instance to the related FieldDescriptionInterface value.
     *
     * @param object $object
     *
     * @throws \RuntimeException
     */
    public function addNewInstance($object, FieldDescriptionInterface $fieldDescription)
    {
        $instance = $fieldDescription->getAssociationAdmin()->getNewInstance();
        $mapping = $fieldDescription->getAssociationMapping();
        $parentMappings = $fieldDescription->getParentAssociationMappings();

        foreach ($parentMappings as $parentMapping) {
            $method = sprintf('get%s', Inflector::classify($parentMapping['fieldName']));

            if (!\is_callable([$object, $method])) {
                /*
                 * NEXT_MAJOR: Use BadMethodCallException instead
                 */
                throw new \RuntimeException(
                    sprintf('Method %s::%s() does not exist.', ClassUtils::getClass($object), $method)
                );
            }

            $object = $object->$method();
        }

        $method = sprintf('add%s', Inflector::classify($mapping['fieldName']));

        if (!\is_callable([$object, $method])) {
            $method = rtrim($method, 's');

            if (!\is_callable([$object, $method])) {
                $method = sprintf('add%s', Inflector::classify(Inflector::singularize($mapping['fieldName'])));

                if (!\is_callable([$object, $method])) {
                    /*
                     * NEXT_MAJOR: Use BadMethodCallException instead
                     */
                    throw new \RuntimeException(
                        sprintf('Method %s::%s() does not exist.', ClassUtils::getClass($object), $method)
                    );
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
     * @deprecated since sonata-project/admin-bundle 3.1. Use \Doctrine\Common\Inflector\Inflector::classify() instead
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
     * @param string $elementId expects string in format used in form id field.
     *                          (uniqueIdentifier_model_sub_model or uniqueIdentifier_model_1_sub_model etc.)
     * @param mixed  $entity
     *
     * @throws \Exception
     *
     * @return string
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

            if ($propertyAccessor->isReadable($entity, $totalPath.$separator.$currentPath)) {
                $totalPath .= $separator.$currentPath;
                $currentPath = '';
            }
        }

        if (!empty($currentPath)) {
            throw new \Exception(
                sprintf('Could not get element id from %s Failing part: %s', $elementId, $currentPath)
            );
        }

        return $totalPath;
    }

    /**
     * Recursively find the class name of the admin responsible for the element at the end of an association chain.
     *
     * @param array $elements
     *
     * @return string
     */
    protected function getEntityClassName(AdminInterface $admin, $elements)
    {
        $element = array_shift($elements);
        $associationAdmin = $admin->getFormFieldDescription($element)->getAssociationAdmin();
        if (0 === \count($elements)) {
            return $associationAdmin->getClass();
        }

        return $this->getEntityClassName($associationAdmin, $elements);
    }
}
