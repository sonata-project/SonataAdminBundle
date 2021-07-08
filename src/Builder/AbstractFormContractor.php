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

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelReferenceType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Form\Type\ModelTypeList;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;

abstract class AbstractFormContractor implements FormContractorInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * NEXT_MAJOR: make not nullable.
     *
     * @var FormRegistryInterface|null
     */
    protected $formRegistry;

    // NEXT_MAJOR: make $formRegistry mandatory
    public function __construct(FormFactoryInterface $formFactory, ?FormRegistryInterface $formRegistry = null)
    {
        $this->formFactory = $formFactory;
        $this->formRegistry = $formRegistry;

        // NEXT_MAJOR: remove this block
        if (null === $formRegistry) {
            @trigger_error(sprintf(
                'Not passing argument 2 of type %s to %s::__construct() is deprecated since sonata-project/admin-bundle 3.x and will fail in 4.0.',
                FormRegistryInterface::class,
                static::class
            ), \E_USER_DEPRECATED);
        }
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));

        // NEXT_MAJOR: Change "$this->hasAssociation($fieldDescription)" with: "$fieldDescription->describesAssociation()".
        if ($this->hasAssociation($fieldDescription) || $fieldDescription->getOption('admin_code')) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     *
     * @return FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     */
    public function getFormBuilder($name, array $formOptions = [])
    {
        return $this->getFormFactory()->createNamedBuilder($name, FormType::class, null, $formOptions);
    }

    /**
     * @final since sonata-project/admin-bundle 3.99.
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        // NEXT_MAJOR: Remove this line and update the function signature.
        $formOptions = \func_get_args()[2] ?? [];

        $options = [];
        $options['sonata_field_description'] = $fieldDescription;

        if ($this->isAnyInstanceOf($type, [
            ModelType::class,
            ModelTypeList::class,
            ModelListType::class,
            ModelHiddenType::class,
            ModelAutocompleteType::class,
            ModelReferenceType::class,
        ])) {
            // NEXT_MAJOR: Remove this check.
            if ('list' === $fieldDescription->getOption('edit')) {
                throw new \LogicException(sprintf(
                    'The `%s` type does not accept an `edit` option anymore,'
                    .' please review the `UPGRADE-2.1.md` file at "sonata-project/admin-bundle".',
                    ModelType::class
                ));
            }

            $options['class'] = $fieldDescription->getTargetModel();
            $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();

            if ($this->isAnyInstanceOf($type, [ModelAutocompleteType::class])) {
                if (!$fieldDescription->getAssociationAdmin()) {
                    // NEXT_MAJOR: Use \InvalidArgumentException instead.
                    throw new \RuntimeException(sprintf(
                        'The current field `%s` is not linked to an admin.'
                        .' Please create one for the target model: `%s`.',
                        $fieldDescription->getName(),
                        $fieldDescription->getTargetModel()
                    ));
                }
            }
        } elseif ($this->isAnyInstanceOf($type, [AdminType::class])) {
            if (!$fieldDescription->getAssociationAdmin()) {
                // NEXT_MAJOR: Use \InvalidArgumentException instead.
                throw new \RuntimeException(sprintf(
                    'The current field `%s` is not linked to an admin.'
                    .' Please create one for the target model: `%s`.',
                    $fieldDescription->getName(),
                    $fieldDescription->getTargetModel()
                ));
            }

            // NEXT_MAJOR: Change this check with: "if (!$fieldDescription->hasSingleValueAssociation())".
            if (!$this->hasSingleValueAssociation($fieldDescription)) {
                // NEXT_MAJOR: Use \InvalidArgumentException instead.
                throw new \RuntimeException(sprintf(
                    'You are trying to add `%s` field `%s` which is not a One-To-One or Many-To-One association.'
                    .' You SHOULD use `%s` instead.',
                    AdminType::class,
                    $fieldDescription->getName(),
                    CollectionType::class
                ));
            }

            // set sensitive default value to have a component working fine out of the box
            $options['btn_add'] = false;
            $options['delete'] = false;

            $options['data_class'] = $fieldDescription->getAssociationAdmin()->getClass();
            // Add "object" return type
            $options['empty_data'] = static function () use ($fieldDescription) {
                return $fieldDescription->getAssociationAdmin()->getNewInstance();
            };
            $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'admin'));
        // @phpstan-ignore-next-line
        } elseif ($this->isAnyInstanceOf($type, [
            CollectionType::class,
            // NEXT_MAJOR: remove 'Sonata\CoreBundle\Form\Type\CollectionType'
            'Sonata\CoreBundle\Form\Type\CollectionType',
        ])) {
            if (!$fieldDescription->getAssociationAdmin()) {
                // NEXT_MAJOR: Use \InvalidArgumentException instead.
                throw new \RuntimeException(sprintf(
                    'The current field `%s` is not linked to an admin.'
                    .' Please create one for the target model: `%s`.',
                    $fieldDescription->getName(),
                    $fieldDescription->getTargetModel()
                ));
            }

            $options['type'] = AdminType::class;
            $options['modifiable'] = true;
            $options['type_options'] = $this->getDefaultAdminTypeOptions($fieldDescription, $formOptions);
        }

        return $options;
    }

    // NEXT_MAJOR: Remove this method.
    abstract protected function hasAssociation(FieldDescriptionInterface $fieldDescription): bool;

    // NEXT_MAJOR: Remove this method.
    abstract protected function hasSingleValueAssociation(FieldDescriptionInterface $fieldDescription): bool;

    /**
     * @param string[] $classes
     *
     * @phpstan-param class-string[] $classes
     */
    private function isAnyInstanceOf(?string $type, array $classes): bool
    {
        if (null === $type) {
            return false;
        }

        foreach ($classes as $class) {
            if (is_a($type, $class, true)) {
                return true;
            }
        }

        // NEXT_MAJOR: remove if condition
        if (null !== $this->formRegistry) {
            // handle form type inheritance and check all parent types
            $resolvedType = $this->formRegistry->getType($type);
            if (null !== $resolvedType->getParent()) {
                $parentType = \get_class($resolvedType->getParent()->getInnerType());

                // all types have "Symfony\Component\Form\Extension\Core\Type\FormType" as parent
                // so we ignore it here for performance reasons
                if (FormType::class !== $parentType) {
                    return $this->isAnyInstanceOf($parentType, $classes);
                }
            }
        }

        return false;
    }

    private function getDefaultAdminTypeOptions(FieldDescriptionInterface $fieldDescription, array $formOptions): array
    {
        $typeOptions = [
            'sonata_field_description' => $fieldDescription,
            'data_class' => $fieldDescription->getAssociationAdmin()->getClass(),
            // Add "object" return type
            'empty_data' => static function () use ($fieldDescription) {
                return $fieldDescription->getAssociationAdmin()->getNewInstance();
            },
        ];

        if (isset($formOptions['by_reference'])) {
            $typeOptions['collection_by_reference'] = $formOptions['by_reference'];
        }

        return $typeOptions;
    }
}
