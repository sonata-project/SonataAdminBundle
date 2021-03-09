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
 */
interface AdminExtensionInterface
{
    public function configureFormFields(FormMapper $formMapper): void;

    public function configureListFields(ListMapper $listMapper): void;

    public function configureDatagridFilters(DatagridMapper $datagridMapper): void;

    public function configureShowFields(ShowMapper $showMapper): void;

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void;

    /**
     * Builds the tab menu.
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param AdminInterface<object>|null $childAdmin
     */
    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        string $action,
        ?AdminInterface $childAdmin = null
    ): void;

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query): void;

    /**
     * Get a chance to modify a newly created instance.
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function alterNewInstance(AdminInterface $admin, object $object): void;

    /**
     * Get a chance to modify object instance.
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function alterObject(AdminInterface $admin, object $object): void;

    /**
     * Get a chance to add persistent parameters.
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configurePersistentParameters(AdminInterface $admin, array $parameters);

    /**
     * Return the controller access mapping.
     *
     * @return array<string, string[]|string>
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function getAccessMapping(AdminInterface $admin): array;

    /**
     * Returns the list of batch actions.
     *
     * @param array<string, array<string, mixed>> $actions
     *
     * @return array<string, array<string, mixed>>
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureBatchActions(AdminInterface $admin, array $actions): array;

    /**
     * Get a chance to modify export fields.
     *
     * @param string[] $fields
     *
     * @return string[]
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureExportFields(AdminInterface $admin, array $fields): array;

    /**
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function preUpdate(AdminInterface $admin, object $object): void;

    /**
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function postUpdate(AdminInterface $admin, object $object): void;

    /**
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function prePersist(AdminInterface $admin, object $object): void;

    /**
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function postPersist(AdminInterface $admin, object $object): void;

    /**
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function preRemove(AdminInterface $admin, object $object): void;

    /**
     * Get all action buttons for an action.
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function postRemove(AdminInterface $admin, object $object): void;

    /**
     * Get all action buttons for an action.
     *
     * @param array<string, array<string, mixed>> $list
     *
     * @return array<string, array<string, mixed>>
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
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
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void;

    /**
     * Returns a list of default sort values.
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void;

    /**
     * Returns a list of form options.
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureFormOptions(AdminInterface $admin, array &$formOptions): void;

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    public function configureFilterParameters(array $parameters): array;
}
