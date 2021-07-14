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

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 * @template-implements AdminExtensionInterface<T>
 */
abstract class AbstractAdminExtension implements AdminExtensionInterface
{
    /**
     * @phpstan-param FormMapper<T> $form
     */
    public function configureFormFields(FormMapper $form): void
    {
    }

    /**
     * @phpstan-param ListMapper<T> $list
     */
    public function configureListFields(ListMapper $list): void
    {
    }

    /**
     * @phpstan-param DatagridMapper<T> $filter
     */
    public function configureDatagridFilters(DatagridMapper $filter): void
    {
    }

    /**
     * @phpstan-param ShowMapper<T> $show
     */
    public function configureShowFields(ShowMapper $show): void
    {
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
    }

    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
    }

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query): void
    {
    }

    public function alterNewInstance(AdminInterface $admin, object $object): void
    {
    }

    public function alterObject(AdminInterface $admin, object $object): void
    {
    }

    public function configurePersistentParameters(AdminInterface $admin, array $parameters): array
    {
        return $parameters;
    }

    public function getAccessMapping(AdminInterface $admin): array
    {
        return [];
    }

    public function configure(AdminInterface $admin): void
    {
    }

    public function configureBatchActions(AdminInterface $admin, array $actions): array
    {
        return $actions;
    }

    public function configureExportFields(AdminInterface $admin, array $fields): array
    {
        return $fields;
    }

    public function preUpdate(AdminInterface $admin, object $object): void
    {
    }

    public function postUpdate(AdminInterface $admin, object $object): void
    {
    }

    public function prePersist(AdminInterface $admin, object $object): void
    {
    }

    public function postPersist(AdminInterface $admin, object $object): void
    {
    }

    public function preRemove(AdminInterface $admin, object $object): void
    {
    }

    public function postRemove(AdminInterface $admin, object $object): void
    {
    }

    public function configureActionButtons(
        AdminInterface $admin,
        array $list,
        string $action,
        ?object $object = null
    ): array {
        return $list;
    }

    public function configureDashboardActions(AdminInterface $admin, array $actions): array
    {
        return $actions;
    }

    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void
    {
    }

    public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void
    {
    }

    public function configureFormOptions(AdminInterface $admin, array &$formOptions): void
    {
    }

    public function configureFilterParameters(AdminInterface $admin, array $parameters): array
    {
        return $parameters;
    }
}
