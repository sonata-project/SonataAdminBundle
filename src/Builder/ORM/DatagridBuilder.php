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

namespace SensioLabs\AdminBundle\Builder\ORM;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Builder\DatagridBuilderInterface;
use SensioLabs\AdminBundle\Datagrid\Datagrid;
use SensioLabs\AdminBundle\Datagrid\DatagridInterface;
use SensioLabs\AdminBundle\Datagrid\PagerInterface;
use SensioLabs\AdminBundle\Datagrid\SimplePager;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\FieldDescription\TypeGuesserInterface;
use SensioLabs\AdminBundle\Filter\FilterFactoryInterface;
use SensioLabs\AdminBundle\Form\Type\ModelAutocompleteType;
use SensioLabs\AdminBundle\Datagrid\ORM\Pager;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;
use SensioLabs\AdminBundle\Filter\ORM\ModelAutocompleteFilter;
use SensioLabs\AdminBundle\Filter\ORM\ModelFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @phpstan-implements DatagridBuilderInterface<ProxyQueryInterface<object>>
 */
final class DatagridBuilder implements DatagridBuilderInterface
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private FilterFactoryInterface $filterFactory,
        private TypeGuesserInterface $guesser,
        private bool $csrfTokenEnabled = true,
    ) {
    }

    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        if ([] !== $fieldDescription->getFieldMapping()) {
            $fieldDescription->setOption('field_mapping', $fieldDescription->getOption('field_mapping', $fieldDescription->getFieldMapping()));
        }

        if ([] !== $fieldDescription->getAssociationMapping()) {
            $fieldDescription->setOption('association_mapping', $fieldDescription->getOption('association_mapping', $fieldDescription->getAssociationMapping()));
        }

        if ([] !== $fieldDescription->getParentAssociationMappings()) {
            $fieldDescription->setOption('parent_association_mappings', $fieldDescription->getOption('parent_association_mappings', $fieldDescription->getParentAssociationMappings()));
        }

        $fieldDescription->setOption('field_name', $fieldDescription->getOption('field_name', $fieldDescription->getFieldName()));

        if (
            ModelFilter::class === $fieldDescription->getType() && null === $fieldDescription->getOption('field_type')
            || EntityType::class === $fieldDescription->getOption('field_type')
        ) {
            $fieldDescription->setOption('field_options', array_merge([
                'class' => $fieldDescription->getTargetModel(),
            ], $fieldDescription->getOption('field_options', [])));
        }

        /*
         * NEXT_MAJOR: Remove the ModelAutocompleteFilter::class check.
         *
         * @see https://github.com/sonata-project/SonataDoctrineORMAdminBundle/pull/1545
         */
        if (
            ModelAutocompleteFilter::class === $fieldDescription->getType() && null === $fieldDescription->getOption('field_type')
            || ModelAutocompleteType::class === $fieldDescription->getOption('field_type')
        ) {
            $fieldDescription->setOption('field_options', array_merge([
                'class' => $fieldDescription->getTargetModel(),
                'model_manager' => $fieldDescription->getAdmin()->getModelManager(),
                'admin_code' => $fieldDescription->getAdmin()->getCode(),
                'context' => 'filter',
            ], $fieldDescription->getOption('field_options', [])));
        }

        if ($fieldDescription->describesAssociation()) {
            $fieldDescription->getAdmin()->attachAdminClass($fieldDescription);
        }
    }

    public function addFilter(DatagridInterface $datagrid, ?string $type, FieldDescriptionInterface $fieldDescription): void
    {
        if (null === $type) {
            $guessType = $this->guesser->guess($fieldDescription);
            if (null === $guessType) {
                throw new \InvalidArgumentException(\sprintf(
                    'Cannot guess a type for the field description "%s", You MUST provide a type.',
                    $fieldDescription->getName()
                ));
            }

            /** @phpstan-var class-string $type */
            $type = $guessType->getType();
            $fieldDescription->setType($type);

            foreach ($guessType->getOptions() as $name => $value) {
                if (\is_array($value)) {
                    $fieldDescription->setOption($name, array_merge($value, $fieldDescription->getOption($name, [])));
                } else {
                    $fieldDescription->setOption($name, $fieldDescription->getOption($name, $value));
                }
            }
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($fieldDescription);
        $fieldDescription->getAdmin()->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);

        $filter = $this->filterFactory->create($fieldDescription->getName(), $type, $fieldDescription->getOptions());
        $datagrid->addFilter($filter);
    }

    public function getBaseDatagrid(AdminInterface $admin, array $values = []): DatagridInterface
    {
        $pager = $this->getPager($admin->getPagerType());

        $defaultOptions = ['validation_groups' => false];
        if ($this->csrfTokenEnabled) {
            $defaultOptions['csrf_protection'] = false;
        }

        $formBuilder = $this->formFactory->createNamedBuilder('filter', FormType::class, [], $defaultOptions);

        $query = $admin->createQuery();
        if (!$query instanceof ProxyQueryInterface) {
            throw new \TypeError(\sprintf('The admin query MUST implement %s.', ProxyQueryInterface::class));
        }
        /** @phpstan-var ProxyQueryInterface<object> $query */

        return new Datagrid($query, $admin->getList(), $pager, $formBuilder, $values);
    }

    /**
     * Get pager by pagerType.
     *
     * @throws \RuntimeException If invalid pager type is set
     *
     * @return PagerInterface<ProxyQueryInterface<object>>
     */
    private function getPager(string $pagerType): PagerInterface
    {
        switch ($pagerType) {
            case Pager::TYPE_DEFAULT:
                return new Pager();

            case Pager::TYPE_SIMPLE:
                /** @var SimplePager<ProxyQueryInterface<object>> $simplePager */
                $simplePager = new SimplePager();

                return $simplePager;

            default:
                throw new \RuntimeException(\sprintf('Unknown pager type "%s".', $pagerType));
        }
    }
}
