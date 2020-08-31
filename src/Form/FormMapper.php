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

    public function reorder(array $keys)
    {
        $this->admin->reorderFormGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    /**
     * @param FormBuilderInterface|string $name
     * @param string|null                 $type
     * @param array<string, mixed>        $options
     * @param array<string, mixed>        $fieldDescriptionOptions
     *
     * @return static
     */
    public function add($name, $type = null, array $options = [], array $fieldDescriptionOptions = [])
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
        $this->builder->fixFieldDescription($this->admin, $fieldDescription);

        if ($fieldName !== $name) {
            $fieldDescription->setName($fieldName);
        }

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

            // NEXT_MAJOR: Remove this block.
            if (isset($options['help']) && !isset($options['help_html'])) {
                $containsHtml = $options['help'] !== strip_tags($options['help']);

                if ($containsHtml) {
                    @trigger_error(
                        'Using HTML syntax within the "help" option and not setting the "help_html" option to "true" is deprecated'
                        .' since sonata-project/admin-bundle 3.74 and it will not work in version 4.0.',
                        E_USER_DEPRECATED
                    );

                    $options['help_html'] = true;
                }
            }
        }

        $this->admin->addFormFieldDescription($fieldName, $fieldDescription);

        if (!isset($fieldDescriptionOptions['role']) || $this->admin->isGranted($fieldDescriptionOptions['role'])) {
            $this->formBuilder->add($name, $type, $options);
        }

        return $this;
    }

    public function get($name)
    {
        $name = $this->sanitizeFieldName($name);

        return $this->formBuilder->get($name);
    }

    public function has($key)
    {
        $key = $this->sanitizeFieldName($key);

        return $this->formBuilder->has($key);
    }

    /**
     * @return string[]
     */
    final public function keys()
    {
        return array_keys($this->formBuilder->all());
    }

    public function remove($key)
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
     *
     * @return static
     */
    public function removeGroup($group, $tab = 'default', $deleteEmptyTab = false)
    {
        $groups = $this->getGroups();

        // When the default tab is used, the tabname is not prepended to the index in the group array
        if ('default' !== $tab) {
            $group = sprintf('%s.%s', $tab, $group);
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

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
    }

    /**
     * @param string               $name
     * @param mixed                $type
     * @param array<string, mixed> $options
     *
     * @return FormBuilderInterface
     */
    public function create($name, $type = null, array $options = [])
    {
        return $this->formBuilder->create($name, $type, $options);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.74 and will be removed in version 4.0. Use Symfony Form "help" option instead.
     *
     * @return FormMapper
     */
    public function setHelps(array $helps = [])
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since sonata-project/admin-bundle 3.74 and will be removed in version 4.0.'
            .' Use Symfony Form "help" option instead.',
            __METHOD__
        ), E_USER_DEPRECATED);

        foreach ($helps as $name => $help) {
            $this->addHelp($name, $help, 'sonata_deprecation_mute');
        }

        return $this;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.74 and will be removed in version 4.0. Use Symfony Form "help" option instead.
     *
     * @return FormMapper
     */
    public function addHelp($name, $help)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[2] ?? null)) {
            @trigger_error(sprintf(
                'The "%s()" method is deprecated since sonata-project/admin-bundle 3.74 and will be removed in version 4.0.'
                .' Use Symfony Form "help" option instead.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        if ($this->admin->hasFormFieldDescription($name)) {
            $this->admin->getFormFieldDescription($name)->setHelp($help, 'sonata_deprecation_mute');
        }

        return $this;
    }

    /**
     * Symfony default form class sadly can't handle
     * form element with dots in its name (when data
     * get bound, the default dataMapper is a PropertyPathMapper).
     * So use this trick to avoid any issue.
     *
     * @param string $fieldName
     *
     * @return string
     */
    protected function sanitizeFieldName($fieldName)
    {
        return str_replace(['__', '.'], ['____', '__'], $fieldName);
    }

    protected function getGroups()
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.

        return $this->admin->getFormGroups('sonata_deprecation_mute');
    }

    protected function setGroups(array $groups)
    {
        $this->admin->setFormGroups($groups);
    }

    protected function getTabs()
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.

        return $this->admin->getFormTabs('sonata_deprecation_mute');
    }

    protected function setTabs(array $tabs)
    {
        $this->admin->setFormTabs($tabs);
    }

    protected function getName()
    {
        return 'form';
    }
}
