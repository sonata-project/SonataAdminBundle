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
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
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
 *
 * @phpstan-template T of object
 * @phpstan-extends BaseGroupedMapper<T>
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

    /**
     * NEXT_MAJOR: Make the property private.
     *
     * @var AdminInterface
     * @phpstan-var AdminInterface<T>
     */
    protected $admin;

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

    public function getAdmin()
    {
        return $this->admin;
    }

    public function reorder(array $keys)
    {
        $this->getAdmin()->reorderFormGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    /**
     * NEXT_MAJOR: Restrict the type of the $name parameter to string.
     *
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

        if (isset($fieldDescriptionOptions['role']) && !$this->getAdmin()->isGranted($fieldDescriptionOptions['role'])) {
            return $this;
        }

        // NEXT_MAJOR: Only keep the else part.
        if ($name instanceof FormBuilderInterface) {
            @trigger_error(
                sprintf(
                    'Passing a %s instance as first param of %s is deprecated since sonata-project/admin-bundle 3.103'
                    .' and will throw an exception in 4.0. You should pass a string instead.',
                    FormBuilderInterface::class,
                    __METHOD__
                ),
                \E_USER_DEPRECATED
            );

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

        // NEXT_MAJOR: Remove the check with "collection".
        if ('collection' === $type || SymfonyCollectionType::class === $type) {
            // NEXT_MAJOR Remove this "if" block.
            if ('collection' === $type) {
                @trigger_error(sprintf(
                    'Passing "collection" as argument 2 for "%s()" is deprecated since'
                    .' sonata-project/admin-bundle 3.103 and will not work in version 4.0.'
                    .' You MUST pass "%s" or "%s" instead.',
                    __METHOD__,
                    SymfonyCollectionType::class,
                    CollectionType::class
                ), \E_USER_DEPRECATED);
            }

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

        if (!isset($fieldDescriptionOptions['translation_domain'])) {
            $fieldDescriptionOptions['translation_domain'] = $group['translation_domain'] ?? null;
        }

        // NEXT_MAJOR: Remove the check and use `createFieldDescription`.
        if (method_exists($this->getAdmin(), 'createFieldDescription')) {
            $fieldDescription = $this->getAdmin()->createFieldDescription(
                $name instanceof FormBuilderInterface ? $name->getName() : $name,
                $fieldDescriptionOptions
            );
        } else {
            $fieldDescription = $this->getAdmin()->getModelManager()->getNewFieldDescriptionInstance(
                $this->getAdmin()->getClass(),
                $name instanceof FormBuilderInterface ? $name->getName() : $name,
                $fieldDescriptionOptions
            );
        }

        // Note that the builder var is actually the formContractor:
        $this->builder->fixFieldDescription($this->getAdmin(), $fieldDescription);

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
                $options['label'] = $this->getAdmin()->getLabelTranslatorStrategy()->getLabel($child, 'form', 'label');
            }

            // NEXT_MAJOR: Remove this block.
            if (isset($options['help']) && !isset($options['help_html'])) {
                $containsHtml = $options['help'] !== strip_tags($options['help']);

                if ($containsHtml) {
                    @trigger_error(
                        'Using HTML syntax within the "help" option and not setting the "help_html" option to "true" is deprecated'
                        .' since sonata-project/admin-bundle 3.74 and it will not work in version 4.0.',
                        \E_USER_DEPRECATED
                    );

                    $options['help_html'] = true;
                }
            }
        }

        $this->getAdmin()->addFormFieldDescription($fieldName, $fieldDescription);
        $this->formBuilder->add($child, $type, $options);

        return $this;
    }

    public function get($key)
    {
        $name = $this->sanitizeFieldName($key);

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
        $this->getAdmin()->removeFormFieldDescription($key);
        $this->getAdmin()->removeFieldFromFormGroup($key);
        $this->formBuilder->remove($key);

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
        ), \E_USER_DEPRECATED);

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
            ), \E_USER_DEPRECATED);
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

        return $this->getAdmin()->getFormGroups('sonata_deprecation_mute');
    }

    protected function setGroups(array $groups)
    {
        $this->getAdmin()->setFormGroups($groups);
    }

    protected function getTabs()
    {
        // NEXT_MAJOR: Remove the argument "sonata_deprecation_mute" in the following call.

        return $this->getAdmin()->getFormTabs('sonata_deprecation_mute');
    }

    protected function setTabs(array $tabs)
    {
        $this->getAdmin()->setFormTabs($tabs);
    }

    protected function getName()
    {
        return 'form';
    }
}

// NEXT_MAJOR: Remove next line.
interface_exists(FieldDescriptionInterface::class);
