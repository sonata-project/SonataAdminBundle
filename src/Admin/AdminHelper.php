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
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Manipulator\ObjectManipulator;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Sonata\AdminBundle\Util\FormViewIterator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @internal
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
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getChildFormBuilder(FormBuilderInterface $formBuilder, string $elementId): ?FormBuilderInterface
    {
        $iterator = new \RecursiveIteratorIterator(
            new FormBuilderIterator($formBuilder),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $name => $currentFormBuilder) {
            if ($name === $elementId) {
                return $currentFormBuilder;
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
     * @param AdminInterface<object> $admin
     *
     * @throws \RuntimeException
     * @throws \Exception
     *
     * @phpstan-return array{\Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface|null, \Symfony\Component\Form\FormInterface}
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
                foreach ($formData[$childFormBuilder->getName()] as &$field) {
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

        if ($childFormBuilder && $admin->hasFormFieldDescription($childFormBuilder->getName())) {
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
        } else {
            // The `else` branch is executed when child form was not found (probably nested one).
            // If `$childFormBuilder` was not found resulted in fatal error `getName()` method call on non object
            // or if `$childFormBuilder` was found, but the admin object does not have a formFieldDescription with
            // same name as `$childFormBuilder`.

            $path = $this->getElementAccessPath($elementId, $subject);

            $collection = $this->propertyAccessor->getValue($subject, $path);

            if (!($collection instanceof Collection)) {
                throw new \TypeError(sprintf(
                    'Collection must be an instance of %s, %s given.',
                    Collection::class,
                    \is_object($collection) ? 'instance of "'.\get_class($collection).'"' : '"'.\gettype($collection).'"'
                ));
            }

            $modelClassName = $this->getModelClassName(
                $admin,
                explode('.', preg_replace('#\[\d*?]#', '', $path) ?? '')
            );

            $collection->add(new $modelClassName());
            $this->propertyAccessor->setValue($subject, $path, $collection);

            $fieldDescription = null;
        }

        $finalForm = $admin->getFormBuilder()->getForm();
        $finalForm->setData($subject);

        // bind the data
        $finalForm->setData($form->getData());

        // back up delete field
        if ($childFormBuilder && \count($toDelete) > 0) {
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
     * Recursively find the class name of the admin responsible for the element at the end of an association chain.
     *
     * @param AdminInterface<object> $admin
     * @param string[]               $elements
     *
     * @phpstan-param non-empty-array<string> $elements
     * @phpstan-return class-string
     */
    private function getModelClassName(AdminInterface $admin, array $elements): string
    {
        $element = array_shift($elements);
        $associationAdmin = $admin->getFormFieldDescription($element)->getAssociationAdmin();
        if ([] === $elements) {
            return $associationAdmin->getClass();
        }

        return $this->getModelClassName($associationAdmin, $elements);
    }

    /**
     * Get access path to element which works with PropertyAccessor.
     *
     * @param string                      $elementId expects string in format used in form id field.
     *                                               uniqId_model_sub_model or uniqId_model_1_sub_model etc.
     * @param object|array<string, mixed> $model
     *
     * @throws \Exception
     */
    private function getElementAccessPath(string $elementId, $model): string
    {
        $idWithoutIdentifier = preg_replace('/^[^_]*_/', '', $elementId) ?? '';
        $initialPath = preg_replace('#(_(\d+)_)#', '[$2]_', $idWithoutIdentifier) ?? '';

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
}
