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
use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\ORM\PersistentCollection as DoctrinePersistentCollection;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Manipulator\ObjectManipulator;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Sonata\AdminBundle\Util\FormViewIterator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
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
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getChildFormBuilder(FormBuilderInterface $formBuilder, string $elementId): ?FormBuilderInterface
    {
        foreach (new FormBuilderIterator($formBuilder) as $name => $formBuilder) {
            if ($name === $elementId) {
                return $formBuilder;
            }
        }

        return null;
    }

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
     * Note:
     *   This code is ugly, but there is no better way of doing it.
     *
     * @throws \RuntimeException
     * @throws \Exception
     *
     * @return array{0: FieldDescriptionInterface|null, 1: FormInterface}
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
                $modelClassName = $this->getModelClassName($admin, explode('.', preg_replace('#\[\d*?\]#', '', $path)));
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
     * Get access path to element which works with PropertyAccessor.
     *
     * @param string $elementId expects string in format used in form id field.
     *                          (uniqueIdentifier_model_sub_model or uniqueIdentifier_model_1_sub_model etc.)
     * @param object $model
     *
     * @throws \Exception
     */
    public function getElementAccessPath(string $elementId, object $model): string
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
     * @param string[] $elements
     */
    protected function getModelClassName(AdminInterface $admin, array $elements): string
    {
        $element = array_shift($elements);
        $associationAdmin = $admin->getFormFieldDescription($element)->getAssociationAdmin();
        if (0 === \count($elements)) {
            return $associationAdmin->getClass();
        }

        return $this->getModelClassName($associationAdmin, $elements);
    }
}
