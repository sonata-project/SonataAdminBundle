<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author Christian Gripp <mail@core23.de>
 */
final class AdminBuilder implements FormBuilderInterface, DatagridBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCreateForm(AdminInterface $admin)
    {
        return $this->getEditForm($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function getEditForm(AdminInterface $admin)
    {
        // append parent object if any
        // todo : clean the way the Admin class can retrieve set the object
        if ($admin->isChild() && $admin->getParentAssociationMapping()) {
            $parent = $admin->getParent()->getObject($admin->getRequest()->get($admin->getParent()->getIdParameter()));

            $propertyAccessor = $admin->getConfigurationPool()->getPropertyAccessor();
            $propertyPath = new PropertyPath($admin->getParentAssociationMapping());

            $object = $admin->getSubject();

            $value = $propertyAccessor->getValue($object, $propertyPath);

            if (is_array($value) || ($value instanceof \Traversable && $value instanceof \ArrayAccess)) {
                $value[] = $parent;
                $propertyAccessor->setValue($object, $propertyPath, $value);
            } else {
                $propertyAccessor->setValue($object, $propertyPath, $parent);
            }
        }

        // NEXT_MAJOR: Move AbstractAdmin::getFormBuilder method to AdminBuilder
        return $admin->getFormBuilder()->getForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getShowForm(AdminInterface $admin)
    {
        $show = new FieldDescriptionCollection();
        $mapper = new ShowMapper($admin->getShowBuilder(), $show, $admin);

        // NEXT_MAJOR: increase visiblity of configureShowFields method
        $reflection = new \ReflectionMethod($admin, 'configureShowFields');
        $reflection->setAccessible(true);
        $reflection->invoke($admin, $mapper);

        foreach ($admin->getExtensions() as $extension) {
            $extension->configureShowFields($mapper);
        }

        return $show;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagrid(AdminInterface $admin)
    {
        $filterParameters = $admin->getFilterParameters();

        // transform _sort_by from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($filterParameters['_sort_by']) && is_string($filterParameters['_sort_by'])) {
            if ($admin->hasListFieldDescription($filterParameters['_sort_by'])) {
                $filterParameters['_sort_by'] = $admin->getListFieldDescription($filterParameters['_sort_by']);
            } else {
                $filterParameters['_sort_by'] = $admin->getModelManager()->getNewFieldDescriptionInstance(
                    $admin->getClass(),
                    $filterParameters['_sort_by'],
                    array()
                );

                $admin->getListBuilder()->buildField(null, $filterParameters['_sort_by'], $admin);
            }
        }

        // initialize the datagrid
        $datagrid = $admin->getDatagridBuilder()->getBaseDatagrid($admin, $filterParameters);

        $datagrid->getPager()->setMaxPageLinks($admin->getMaxPageLinks());

        $mapper = new DatagridMapper($admin->getDatagridBuilder(), $datagrid, $admin);

        // build the datagrid filter

        // NEXT_MAJOR: increase visiblity of configureDatagridFilters method
        $reflection = new \ReflectionMethod($admin, 'configureDatagridFilters');
        $reflection->setAccessible(true);
        $reflection->invoke($admin, $mapper);

        // ok, try to limit to add parent filter
        if ($admin->isChild() && $admin->getParentAssociationMapping() && !$mapper->has($admin->getParentAssociationMapping())) {
            // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
            $modelHiddenType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Sonata\AdminBundle\Form\Type\ModelHiddenType'
                : 'sonata_type_model_hidden';

            // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
            $hiddenType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Symfony\Component\Form\Extension\Core\Type\HiddenType'
                : 'hidden';

            $mapper->add($admin->getParentAssociationMapping(), null, array(
                'show_filter' => false,
                'label' => false,
                'field_type' => $modelHiddenType,
                'field_options' => array(
                    'model_manager' => $admin->getModelManager(),
                ),
                'operator_type' => $hiddenType,
            ), null, null, array(
                'admin_code' => $admin->getParent()->getCode(),
            ));
        }

        foreach ($admin->getExtensions() as $extension) {
            $extension->configureDatagridFilters($mapper);
        }

        return $datagrid;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(AdminInterface $admin)
    {
        $list = $admin->getListBuilder()->getBaseList();

        $mapper = new ListMapper($admin->getListBuilder(), $list, $admin);

        if (count($admin->getBatchActions()) > 0) {
            $fieldDescription = $admin->getModelManager()->getNewFieldDescriptionInstance($admin->getClass(), 'batch', array(
                'label' => 'batch',
                'code' => '_batch',
                'sortable' => false,
                'virtual_field' => true,
            ));

            $fieldDescription->setAdmin($admin);
            $fieldDescription->setTemplate($admin->getTemplate('batch'));

            $mapper->add($fieldDescription, 'batch');
        }

        // NEXT_MAJOR: increase visiblity of configureListFields method
        $reflection = new \ReflectionMethod($admin, 'configureListFields');
        $reflection->setAccessible(true);
        $reflection->invoke($admin, $mapper);

        foreach ($admin->getExtensions() as $extension) {
            $extension->configureListFields($mapper);
        }

        if ($admin->hasRequest() && $admin->getRequest()->isXmlHttpRequest()) {
            $fieldDescription = $admin->getModelManager()->getNewFieldDescriptionInstance($admin->getClass(), 'select', array(
                'label' => false,
                'code' => '_select',
                'sortable' => false,
                'virtual_field' => false,
            ));

            $fieldDescription->setAdmin($admin);
            $fieldDescription->setTemplate($admin->getTemplate('select'));

            $mapper->add($fieldDescription, 'select');
        }

        return $list;
    }
}
