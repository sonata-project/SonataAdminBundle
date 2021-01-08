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
use Symfony\Component\Form\Extension\Core\Type\CollectionType as SymfonyCollectionType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * This class is use to simulate the Form API.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class FormMapper extends BaseGroupedMapper
{
    /**
     * @var FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * @var FormContractorInterface
     */
    protected $builder;

    public function __construct(
        FormContractorInterface $formContractor,
        FormBuilderInterface $formBuilder,
        AdminInterface $admin
    ) {
        parent::__construct($formContractor, $admin);
        $this->formBuilder = $formBuilder;
    }

    public function reorder(array $keys): self
    {
        $this->admin->reorderFormGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    /**
     * @param FormBuilderInterface|string $name
     * @param array<string, mixed>        $options
     * @param array<string, mixed>        $fieldDescriptionOptions
     *
     * @return static
     */
    public function add($name, ?string $type = null, array $options = [], array $fieldDescriptionOptions = []): self
    {
        if (!$this->shouldApply()) {
            return $this;
        }

        if (isset($fieldDescriptionOptions['role']) && !$this->admin->isGranted($fieldDescriptionOptions['role'])) {
            return $this;
        }

        if ($name instanceof FormBuilderInterface) {
            $fieldName = $name->getName();
        } else {
            $fieldName = $name;
        }

        // "Dot" notation is not allowed as form name, but can be used as property path to access nested data.
        if (!$name instanceof FormBuilderInterface && !isset($options['property_path'])) {
            $options['property_path'] = $fieldName;

            // fix the form name
            $fieldName = $this->sanitizeFieldName($fieldName);
        }

        // change `collection` to `sonata_type_native_collection` form type to
        // avoid BC break problems
        if ('collection' === $type || SymfonyCollectionType::class === $type) {
            $type = CollectionType::class;
        }

        $group = $this->addFieldToCurrentGroup($fieldName);

        // Try to autodetect type
        if ($name instanceof FormBuilderInterface && null === $type) {
            $fieldDescriptionOptions['type'] = \get_class($name->getType()->getInnerType());
        }

        if (!isset($fieldDescriptionOptions['type']) && \is_string($type)) {
            $fieldDescriptionOptions['type'] = $type;
        }

        if ($group['translation_domain'] && !isset($fieldDescriptionOptions['translation_domain'])) {
            $fieldDescriptionOptions['translation_domain'] = $group['translation_domain'];
        }

        $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
            $this->admin->getClass(),
            $name instanceof FormBuilderInterface ? $name->getName() : $name,
            $fieldDescriptionOptions
        );

        // Note that the builder var is actually the formContractor:
        $this->builder->fixFieldDescription($this->admin, $fieldDescription);

        if ($fieldName !== $name) {
            $fieldDescription->setName($fieldName);
        }

        if ($name instanceof FormBuilderInterface) {
            $child = $name;
            $type = null;
            $options = [];
        } else {
            $child = $fieldDescription->getName();

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
                /*
                 * NEXT_MAJOR: Replace $child by $name in the next line.
                 * And add the following BC-break in the upgrade note:
                 *
                 * The form label are now correctly using the label translator strategy
                 * for field with `.` (which won't be replaced by `__`). For instance,
                 * with the underscore label strategy, the label `foo.barBaz` was
                 * previously `form.label_foo__bar_baz` and now is `form.label_foo_bar_baz`
                 * to be consistent with others labels like `show.label_foo_bar_baz`.
                 */
                $options['label'] = $this->admin->getLabelTranslatorStrategy()->getLabel($child, 'form', 'label');
            }
        }

        $this->admin->addFormFieldDescription($fieldName, $fieldDescription);
        $this->formBuilder->add($child, $type, $options);

        return $this;
    }

    public function get(string $name): FormBuilderInterface
    {
        $name = $this->sanitizeFieldName($name);

        return $this->formBuilder->get($name);
    }

    public function has(string $key): bool
    {
        $key = $this->sanitizeFieldName($key);

        return $this->formBuilder->has($key);
    }

    /**
     * @return string[]
     */
    final public function keys(): array
    {
        return array_keys($this->formBuilder->all());
    }

    public function remove(string $key): self
    {
        $key = $this->sanitizeFieldName($key);
        $this->admin->removeFormFieldDescription($key);
        $this->admin->removeFieldFromFormGroup($key);
        $this->formBuilder->remove($key);

        return $this;
    }

    public function getFormBuilder(): FormBuilderInterface
    {
        return $this->formBuilder;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function create(string $name, ?string $type = null, array $options = []): FormBuilderInterface
    {
        return $this->formBuilder->create($name, $type, $options);
    }

    /**
     * Symfony default form class sadly can't handle
     * form element with dots in its name (when data
     * get bound, the default dataMapper is a PropertyPathMapper).
     * So use this trick to avoid any issue.
     */
    protected function sanitizeFieldName(string $fieldName): string
    {
        return str_replace(['__', '.'], ['____', '__'], $fieldName);
    }

    protected function getGroups(): array
    {
        return $this->admin->getFormGroups();
    }

    protected function setGroups(array $groups): void
    {
        $this->admin->setFormGroups($groups);
    }

    protected function getTabs(): array
    {
        return $this->admin->getFormTabs();
    }

    protected function setTabs(array $tabs): void
    {
        $this->admin->setFormTabs($tabs);
    }

    protected function getName(): string
    {
        return 'form';
    }
}
