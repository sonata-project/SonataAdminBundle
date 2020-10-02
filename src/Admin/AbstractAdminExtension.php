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
use Sonata\Form\Validator\ErrorElement;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class AbstractAdminExtension implements AdminExtensionInterface
{
    public function configureFormFields(FormMapper $formMapper): void
    {
    }

    public function configureListFields(ListMapper $listMapper): void
    {
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
    }

    public function configureShowFields(ShowMapper $showMapper): void
    {
    }

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
    }

    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
    }

    public function validate(AdminInterface $admin, ErrorElement $errorElement, object $object): void
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

    public function getPersistentParameters(AdminInterface $admin): array
    {
        return [];
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getAccessMapping(AdminInterface $admin): array
    {
        return [];
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

    /**
     * @param array<string, mixed> $list
     *
     * @return array<string, mixed>
     */
    public function configureActionButtons(
        AdminInterface $admin,
        array $list,
        string $action,
        ?object $object = null
    ): array {
        return $list;
    }

    /**
     * Returns a list of default filters.
     */
    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void
    {
    }

    public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void
    {
    }
}
