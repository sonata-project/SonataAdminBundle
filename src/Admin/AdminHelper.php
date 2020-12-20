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
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\ORM\PersistentCollection as DoctrinePersistentCollection;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Manipulator\ObjectManipulator;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Sonata\AdminBundle\Util\FormViewIterator;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

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
     * NEXT_MAJOR: Remove this property.
     *
     * @var Pool|null
     */
    protected $pool;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * NEXT_MAJOR: Change signature for (PropertyAccessorInterface $propertyAccessor).
     */
    public function __construct($poolOrPropertyAccessor)
    {
        // NEXT_MAJOR: Remove this block.
        if (!$poolOrPropertyAccessor instanceof Pool && !$poolOrPropertyAccessor instanceof PropertyAccessorInterface) {
            throw new \TypeError(sprintf(
                'Argument 1 passed to "%s()" must be either an instance of %s or %s, %s given.',
                __METHOD__,
                Pool::class,
                PropertyAccessorInterface::class,
                \is_object($poolOrPropertyAccessor) ? 'instance of "'.\get_class($poolOrPropertyAccessor).'"' : '"'.\gettype($poolOrPropertyAccessor).'"'
            ));
        }

        // NEXT_MAJOR: Remove this block.
        if ($poolOrPropertyAccessor instanceof Pool) {
            @trigger_error(sprintf(
                'Passing an instance of "%s" as argument 1 for "%s()" is deprecated since'
                .' sonata-project/admin-bundle 3.x and will throw a \TypeError error in version 4.0.'
                .' You MUST pass an instance of %s instead.',
                Pool::class,
                __METHOD__,
                PropertyAccessorInterface::class
            ), E_USER_DEPRECATED);

            $this->pool = $poolOrPropertyAccessor;
            $this->propertyAccessor = $poolOrPropertyAccessor->getPropertyAccessor();

            return;
        }

        // NEXT_MAJOR: Remove this block.
        if ((\func_get_args()[1] ?? null) instanceof Pool) {
            $this->pool = \func_get_args()[1];
        }

        // NEXT_MAJOR: Change $poolOrPropertyAccessor to $propertyAccessor.
        $this->propertyAccessor = $poolOrPropertyAccessor;
    }

    /**
     * @param FormBuilderInterface $formBuilder
     * @param string               $elementId
     *
     * @return FormBuilderInterface|null
     */
    public function getChildFormBuilder(FormBuilderInterface $formBuilder, string $elementId): ?FormBuilderInterface
    {
        foreach (new FormBuilderIterator($formBuilder) as $name => $formBuilder) {
            if ($name === $elementId) {
                return $formBuilder;
            }
        }

        return null;
    }

    /**
     * @param FormView $formView
     * @param string   $elementId
     *
     * @return FormView|null
     */
    public function getChildFormView(FormView $formView, string $elementId): ?FormView
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
    public function getAdmin(string $code): AdminInterface
    {
        if (null === $this->pool) {
            throw new \LogicException(sprintf(
                'You MUST pass a "%s" instance as argument 2 when constructing "%s" to be able to call "%s()".',
                Pool::class,
                self::class,
                __METHOD__
            ));
        }

        return $this->pool->getInstance($code);
    }

    /**
     * Note:
     *   This code is ugly, but there is no better way of doing it.
     *
     * @param AdminInterface $admin
     * @param object         $subject
     * @param string         $elementId
     *
     * @return array
     *
     * @throws \Exception
     * @phpstan-return array{\Sonata\AdminBundle\Admin\FieldDescriptionInterface|null, \Symfony\Component\Form\FormInterface}
     */
    public function appendFormFieldElement(AdminInterface $admin, object $subject, string $elementId): array
    {
        // child rows marked as toDelete
        $toDelete = [];

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
            $path = $this->getElementAccessPath($elementId, $subject);

            $collection = $this->propertyAccessor->getValue($subject, $path);

            if ($collection instanceof DoctrinePersistentCollection || $collection instanceof PersistentCollection) {
                //since doctrine 2.4
                $modelClassName = $collection->getTypeClass()->getName();
            } elseif ($collection instanceof Collection) {
                $modelClassName = $this->getEntityClassName($admin, explode('.', preg_replace('#\[\d*?\]#', '', $path)));
            } else {
                throw new \Exception('unknown collection class');
            }

            $collection->add(new $modelClassName());
            $this->propertyAccessor->setValue($subject, $path, $collection);

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

            $associationAdmin = $fieldDescription->getAssociationAdmin();

            // add new elements to the subject
            while ($objectCount < $postCount) {
                // append a new instance into the object
                ObjectManipulator::addInstance($form->getData(), $associationAdmin->getNewInstance(), $fieldDescription);
                ++$objectCount;
            }

            $newInstance = ObjectManipulator::addInstance($form->getData(), $associationAdmin->getNewInstance(), $fieldDescription);

            $associationAdmin->setSubject($newInstance);
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
     * NEXT_MAJOR: remove this method.
     *
     * @param object                    $object
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return object
     * @deprecated since sonata-project/admin-bundle 3.72, use to be removed with 4.0.
     *
     * Add a new instance to the related FieldDescriptionInterface value.
     *
     */
    public function addNewInstance(object $object, FieldDescriptionInterface $fieldDescription): object
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/admin-bundle 3.72. It will be removed in version 4.0.'
            .' Use %s::addInstance() instead.',
            __METHOD__,
            ObjectManipulator::class
        ), E_USER_DEPRECATED);

        $instance = $fieldDescription->getAssociationAdmin()->getNewInstance();

        return ObjectManipulator::addInstance($object, $instance, $fieldDescription);
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
     * @deprecated since sonata-project/admin-bundle 3.1. Use \Doctrine\Inflector\Inflector::classify() instead
     */
    public function camelize(string $property): string
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since 3.1 and will be removed in 4.0. Use %s::classify() instead.',
            __METHOD__,
            Inflector::class
        ), E_USER_DEPRECATED);

        return InflectorFactory::create()->build()->classify($property);
    }

    /**
     * Get access path to element which works with PropertyAccessor.
     *
     * @param string $elementId expects string in format used in form id field.
     *                          (uniqueIdentifier_model_sub_model or uniqueIdentifier_model_1_sub_model etc.)
     * @param mixed  $model
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getElementAccessPath(string $elementId, $model): string
    {
        $idWithoutIdentifier = preg_replace('/^[^_]*_/', '', $elementId);
        $initialPath = preg_replace('#(_(\d+)_)#', '[$2]_', $idWithoutIdentifier);

        $parts = explode('_', $initialPath);
        $totalPath = '';
        $currentPath = '';

        foreach ($parts as $part) {
            $currentPath .= empty($currentPath) ? $part : '_'.$part;
            $separator = empty($totalPath) ? '' : '.';

            if ($this->propertyAccessor->isReadable($model, $totalPath.$separator.$currentPath)) {
                $totalPath .= $separator.$currentPath;
                $currentPath = '';
            }
        }

        if (!empty($currentPath)) {
            throw new \Exception(sprintf(
                'Could not get element id from %s Failing part: %s',
                $elementId,
                $currentPath
            ));
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
    protected function getModelClassName(AdminInterface $admin, array $elements): string
    {
        return $this->getEntityClassName($admin, $elements);
    }

    /**
     * NEXT_MAJOR: Remove this method and move its body to `getModelClassName()`.
     *
     * @param AdminInterface $admin
     * @param array          $elements
     *
     * @return string
     * @deprecated since sonata-project/admin-bundle 3.69. Use `getModelClassName()` instead.
     *
     */
    protected function getEntityClassName(AdminInterface $admin, array $elements): string
    {
        if (self::class !== static::class) {
            @trigger_error(sprintf(
                'Method %s() is deprecated since sonata-project/admin-bundle 3.69 and will be removed in version 4.0.'
                .' Use %s::getModelClassName() instead.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
        }

        $element = array_shift($elements);
        $associationAdmin = $admin->getFormFieldDescription($element)->getAssociationAdmin();
        if (0 === \count($elements)) {
            return $associationAdmin->getClass();
        }

        return $this->getEntityClassName($associationAdmin, $elements);
    }
}
