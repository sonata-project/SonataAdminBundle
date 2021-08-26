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

namespace Sonata\AdminBundle\Form;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
use Sonata\BlockBundle\Form\Mapper\FormMapper as BlockFormMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType as SymfonyCollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * This class is used to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-import-type FieldDescriptionOptions from \Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface
 *
 * @phpstan-template T of object
 * @phpstan-extends BaseGroupedMapper<T>
 */
final class FormMapper extends BaseGroupedMapper implements BlockFormMapper
{
    /**
     * @var FormContractorInterface
     */
    private $builder;

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * @var AdminInterface<object>
     * @phpstan-var AdminInterface<T>
     */
    private $admin;

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function __construct(
        FormContractorInterface $formContractor,
        FormBuilderInterface $formBuilder,
        AdminInterface $admin
    ) {
        $this->builder = $formContractor;
        $this->admin = $admin;
        $this->formBuilder = $formBuilder;
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    /**
     * @return static
     */
    public function reorder(array $keys): self
    {
        $this->getAdmin()->reorderFormGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return static
     *
     * @phpstan-param class-string|null $type
     * @phpstan-param FieldDescriptionOptions $fieldDescriptionOptions
     */
    public function add(string $name, ?string $type = null, array $options = [], array $fieldDescriptionOptions = []): self
    {
        if (!$this->shouldApply()) {
            return $this;
        }

        if (isset($fieldDescriptionOptions['role']) && !$this->getAdmin()->isGranted($fieldDescriptionOptions['role'])) {
            return $this;
        }

        if (SymfonyCollectionType::class === $type) {
            $type = CollectionType::class;
        }

        // We're accessing form fields with the name added to the group.
        // Since the sanitized name is used by the form builder, the group keep a reference to it.
        $sanitizedName = $this->sanitizeFieldName($name);
        $group = $this->addFieldToCurrentGroup($name, $sanitizedName);

        if (!isset($fieldDescriptionOptions['type']) && \is_string($type)) {
            $fieldDescriptionOptions['type'] = $type;
        }

        if (!isset($fieldDescriptionOptions['translation_domain'])) {
            $fieldDescriptionOptions['translation_domain'] = $group['translation_domain'] ?? null;
        }

        $fieldDescription = $this->getAdmin()->createFieldDescription(
            $name,
            $fieldDescriptionOptions
        );

        // Note that the builder var is actually the formContractor:
        $this->builder->fixFieldDescription($fieldDescription);

        // Note that the builder var is actually the formContractor:
        $options = array_replace_recursive(
            $this->builder->getDefaultOptions($type, $fieldDescription, $options),
            $options
        );

        // be compatible with mopa if not installed, avoid generating an exception for invalid option
        // force the default to false ...
        if (!isset($options['label_render'])) {
            $options['label_render'] = false;
        }

        if (!isset($options['label'])) {
            $options['label'] = $this->getAdmin()->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'form', 'label');
        }

        // "Dot" notation is not allowed as form name, but can be used as property path to access nested data.
        if (!isset($options['property_path'])) {
            $options['property_path'] = $name;
        }

        $this->getAdmin()->addFormFieldDescription($fieldDescription->getName(), $fieldDescription);
        $this->formBuilder->add($sanitizedName, $type, $options);

        return $this;
    }

    public function get(string $key): FormBuilderInterface
    {
        $name = $this->sanitizeFieldName($key);

        return $this->formBuilder->get($name);
    }

    public function has(string $key): bool
    {
        $key = $this->sanitizeFieldName($key);

        return $this->formBuilder->has($key);
    }

    public function keys(): array
    {
        return array_keys($this->formBuilder->all());
    }

    /**
     * @return static
     */
    public function remove(string $key): self
    {
        $this->getAdmin()->removeFormFieldDescription($key);
        $this->getAdmin()->removeFieldFromFormGroup($key);

        $sanitizedKey = $this->sanitizeFieldName($key);
        $this->formBuilder->remove($sanitizedKey);

        return $this;
    }

    public function getFormBuilder(): FormBuilderInterface
    {
        return $this->formBuilder;
    }

    /**
     * @param class-string<FormTypeInterface>|null $type
     * @param array<string, mixed>                 $options
     */
    public function create(string $name, ?string $type = null, array $options = []): FormBuilderInterface
    {
        return $this->formBuilder->create($name, $type, $options);
    }

    protected function getGroups(): array
    {
        return $this->getAdmin()->getFormGroups();
    }

    protected function setGroups(array $groups): void
    {
        $this->getAdmin()->setFormGroups($groups);
    }

    protected function getTabs(): array
    {
        return $this->getAdmin()->getFormTabs();
    }

    protected function setTabs(array $tabs): void
    {
        $this->getAdmin()->setFormTabs($tabs);
    }

    protected function getName(): string
    {
        return 'form';
    }

    /**
     * Symfony default form class can't handle form element with dots in its
     * name (when data get bound, the default dataMapper is a PropertyPathMapper).
     * So use this trick to avoid any issue.
     */
    private function sanitizeFieldName(string $fieldName): string
    {
        return str_replace(['__', '.'], ['____', '__'], $fieldName);
    }
}
