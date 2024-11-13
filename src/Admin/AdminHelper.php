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

use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Manipulator\ObjectManipulator;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Sonata\AdminBundle\Util\FormViewIterator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
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

    public function __construct(
        private PropertyAccessorInterface $propertyAccessor,
    ) {
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
        $iterator = new \RecursiveIteratorIterator(
            new FormViewIterator($formView),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $name => $currentFormView) {
            if ($name === $elementId) {
                return $currentFormView;
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
     * @return array{FieldDescriptionInterface|null, FormInterface}
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
     */
    public function appendFormFieldElement(AdminInterface $admin, object $subject, string $elementId): array
    {
        // child rows marked as toDelete
        $toDelete = [];

        $formBuilder = $admin->getFormBuilder();

        // get the field element
        $childFormBuilder = $this->getChildFormBuilder($formBuilder, $elementId);

        if (null !== $childFormBuilder) {
            $formData = $admin->getRequest()->get($formBuilder->getName(), []);
            \assert(\is_array($formData));

            if (
                \array_key_exists($childFormBuilder->getName(), $formData)
                && is_iterable($formData[$childFormBuilder->getName()])
            ) {
                $formData = $admin->getRequest()->get($formBuilder->getName(), []);
                \assert(\is_array($formData));

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

        $childFieldDescription = null !== $childFormBuilder
            ? $childFormBuilder->getOption('sonata_field_description')
            : null;

        if (
            null !== $childFormBuilder
            && $childFieldDescription instanceof FieldDescriptionInterface
            && $childFieldDescription->hasAdmin()
            && $childFieldDescription->getAdmin() === $admin
            && $admin->hasFormFieldDescription($childFieldDescription->getName())
        ) {
            // retrieve the FieldDescription
            $fieldDescription = $admin->getFormFieldDescription($childFieldDescription->getName());

            try {
                $value = $fieldDescription->getValue($form->getData());
            } catch (NoValueException) {
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

            if (!$collection instanceof \ArrayAccess && !\is_array($collection)) {
                throw new \TypeError(\sprintf(
                    'Collection must be an instance of %s or array, %s given.',
                    \ArrayAccess::class,
                    \is_object($collection) ? 'instance of "'.$collection::class.'"' : '"'.\gettype($collection).'"'
                ));
            }

            $modelClassName = $this->getModelClassName(
                $admin,
                explode('.', preg_replace('#\[\d*?]#', '', $path) ?? '')
            );

            $collection[] = new $modelClassName();
            $this->propertyAccessor->setValue($subject, $path, $collection);

            $fieldDescription = null;
        }

        $finalForm = $admin->getFormBuilder()->getForm();
        $finalForm->setData($subject);

        // bind the data
        $finalForm->setData($form->getData());

        // back up delete field
        if (null !== $childFormBuilder && \count($toDelete) > 0) {
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
     * @param string[] $elements
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
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

        if (!$associationAdmin->hasSubject()) {
            $associationAdmin->setSubject($associationAdmin->getNewInstance());
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
            $currentPath .= '' === $currentPath ? $part : '_'.$part;
            $separator = '' === $totalPath ? '' : '.';

            if ($this->propertyAccessor->isReadable($model, $totalPath.$separator.$currentPath)) {
                $totalPath .= $separator.$currentPath;
                $currentPath = '';
            }
        }

        if ('' !== $currentPath) {
            throw new \Exception(\sprintf(
                'Could not get element id from %s Failing part: %s',
                $elementId,
                $currentPath
            ));
        }

        return $totalPath;
    }
}
