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
     */
    public function add($name, ?string $type = null, array $options = [], array $fieldDescriptionOptions = []): self
    {
        if (!$this->shouldApply()) {
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

        $label = $fieldName;

        $group = $this->addFieldToCurrentGroup($label);

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
        $this->builder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);

        if ($fieldName !== $name) {
            $fieldDescription->setName($fieldName);
        }

        $this->admin->addFormFieldDescription($fieldName, $fieldDescription);

        if ($name instanceof FormBuilderInterface) {
            $type = null;
            $options = [];
        } else {
            $name = $fieldDescription->getName();

            // Note that the builder var is actually the formContractor:
            $options = array_replace_recursive($this->builder->getDefaultOptions($type, $fieldDescription) ?? [], $options);

            // be compatible with mopa if not installed, avoid generating an exception for invalid option
            // force the default to false ...
            if (!isset($options['label_render'])) {
                $options['label_render'] = false;
            }

            if (!isset($options['label'])) {
                $options['label'] = $this->admin->getLabelTranslatorStrategy()->getLabel($name, 'form', 'label');
            }

            $help = null;
            if (isset($options['help'])) {
                $help = $options['help'];
                unset($options['help']);
            }

            if (null !== $help) {
                $this->admin->getFormFieldDescription($name)->setHelp($help);
            }
        }

        if (!isset($fieldDescriptionOptions['role']) || $this->admin->isGranted($fieldDescriptionOptions['role'])) {
            $this->formBuilder->add($name, $type, $options);
        }

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

    /**
     * Removes a group.
     *
     * @param string $group          The group to delete
     * @param string $tab            The tab the group belongs to, defaults to 'default'
     * @param bool   $deleteEmptyTab Whether or not the Tab should be deleted, when the deleted group leaves the tab empty after deletion
     */
    public function removeGroup(string $group, string $tab = 'default', bool $deleteEmptyTab = false): self
    {
        $groups = $this->getGroups();

        // When the default tab is used, the tabname is not prepended to the index in the group array
        if ('default' !== $tab) {
            $group = $tab.'.'.$group;
        }

        if (isset($groups[$group])) {
            foreach ($groups[$group]['fields'] as $field) {
                $this->remove($field);
            }
        }
        unset($groups[$group]);

        $tabs = $this->getTabs();
        $key = array_search($group, $tabs[$tab]['groups'], true);

        if (false !== $key) {
            unset($tabs[$tab]['groups'][$key]);
        }
        if ($deleteEmptyTab && 0 === \count($tabs[$tab]['groups'])) {
            unset($tabs[$tab]);
        }

        $this->setTabs($tabs);
        $this->setGroups($groups);

        return $this;
    }

    public function getFormBuilder(): FormBuilderInterface
    {
        return $this->formBuilder;
    }

    public function create(string $name, ?string $type = null, array $options = []): FormBuilderInterface
    {
        return $this->formBuilder->create($name, $type, $options);
    }

    public function setHelps(array $helps = []): self
    {
        foreach ($helps as $name => $help) {
            $this->addHelp($name, $help);
        }

        return $this;
    }

    public function addHelp(string $name, string $help): self
    {
        if ($this->admin->hasFormFieldDescription($name)) {
            $this->admin->getFormFieldDescription($name)->setHelp($help);
        }

        return $this;
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
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.

        return $this->admin->getFormGroups('sonata_deprecation_mute');
    }

    protected function setGroups(array $groups): void
    {
        $this->admin->setFormGroups($groups);
    }

    protected function getTabs(): array
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.

        return $this->admin->getFormTabs('sonata_deprecation_mute');
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
