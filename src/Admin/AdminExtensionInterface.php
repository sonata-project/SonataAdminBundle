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
interface AdminExtensionInterface
{
    public function configureFormFields(FormMapper $formMapper): void;

    public function configureListFields(ListMapper $listMapper): void;

    public function configureDatagridFilters(DatagridMapper $datagridMapper): void;

    public function configureShowFields(ShowMapper $showMapper): void;

    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void;

    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        string $action,
        ?AdminInterface $childAdmin = null
    ): void;

    public function validate(AdminInterface $admin, ErrorElement $errorElement, object $object): void;

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query): void;

    /**
     * Get a chance to modify a newly created instance.
     */
    public function alterNewInstance(AdminInterface $admin, object $object): void;

    /**
     * Get a chance to modify object instance.
     */
    public function alterObject(AdminInterface $admin, object $object): void;

    /**
     * Get a chance to add persistent parameters.
     *
     * @return array<string, mixed>
     */
    public function getPersistentParameters(AdminInterface $admin): array;

    /**
     * Return the controller access mapping.
     *
     * @return array<string, string[]|string>
     */
    public function getAccessMapping(AdminInterface $admin): array;

    /**
     * Returns the list of batch actions.
     *
     * @param array<string, array<string, mixed>> $actions
     *
     * @return array<string, array<string, mixed>>
     */
    public function configureBatchActions(AdminInterface $admin, array $actions): array;

    /**
     * Get a chance to modify export fields.
     *
     * @param string[] $fields
     *
     * @return string[]
     */
    public function configureExportFields(AdminInterface $admin, array $fields): array;

    public function preUpdate(AdminInterface $admin, object $object): void;

    public function postUpdate(AdminInterface $admin, object $object): void;

    public function prePersist(AdminInterface $admin, object $object): void;

    public function postPersist(AdminInterface $admin, object $object): void;

    public function preRemove(AdminInterface $admin, object $object): void;

    public function postRemove(AdminInterface $admin, object $object): void;

    /**
     * Get all action buttons for an action.
     *
     * @param array<string, array<string, mixed>> $list
     *
     * @return array<string, array<string, mixed>>
     */
    public function configureActionButtons(
        AdminInterface $admin,
        array $list,
        string $action,
        ?object $object = null
    ): array;

    /**
     * Returns a list of default filters.
     *
     * @param array<string, array<string, mixed>> $filterValues
     */
    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void;

    /**
     * Returns a list of default sort values.
     *
     * @param array{_page?: int, _per_page?: int, _sort_by?: string, _sort_order?: string} $sortValues
     */
    public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void;
}
