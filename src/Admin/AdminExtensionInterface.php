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
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Validator\ErrorElement;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @method array getAccessMapping(AdminInterface $admin)
 * @method array configureBatchActions(AdminInterface $admin, array $actions)
 * @method array configureExportFields(AdminInterface $admin, array $fields)
 * @method array configureActionButtons(AdminInterface $admin, array $list, string $action, object $object)
 * @method void  configureDefaultFilterValues(AdminInterface $admin, array &$filterValues)
 * @method void  configureDefaultSortValues(AdminInterface $admin, array &$sortValues)
 */
interface AdminExtensionInterface
{
    /**
     * @param FormMapper $formMapper
     *
     * @return void
     */
    public function configureFormFields(FormMapper $formMapper): void;

    /**
     * @param ListMapper $listMapper
     *
     * @return void
     */
    public function configureListFields(ListMapper $listMapper): void;

    /**
     * @param DatagridMapper $datagridMapper
     *
     * @return void
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper): void;

    /**
     * @param ShowMapper $showMapper
     *
     * @return void
     */
    public function configureShowFields(ShowMapper $showMapper): void;

    /**
     * @param AdminInterface  $admin
     * @param RouteCollection $collection
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection): void;

    /**
     * DEPRECATED: Use configureTabMenu instead.
     *
     * NEXT_MAJOR: remove this method.
     *
     * @param AdminInterface      $admin
     * @param MenuItemInterface   $menu
     * @param string              $action
     * @param AdminInterface|null $childAdmin
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     * @phpstan-param AdminInterface<object>|null $childAdmin
     *
     * @deprecated
     */
    public function configureSideMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        string $action,
        ?AdminInterface $childAdmin = null
    ): void;

    /**
     * Builds the tab menu.
     *
     * @param AdminInterface      $admin
     * @param MenuItemInterface   $menu
     * @param string              $action
     * @param AdminInterface|null $childAdmin
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     * @phpstan-param AdminInterface<object>|null $childAdmin
     */
    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        string $action,
        ?AdminInterface $childAdmin = null
    ): void;

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @param AdminInterface $admin
     * @param ErrorElement   $errorElement
     * @param object         $object
     *
     * @return void
     *
     * @deprecated    since sonata-project/admin-bundle 3.x.
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function validate(AdminInterface $admin, ErrorElement $errorElement, object $object): void;

    /**
     * @param AdminInterface      $admin
     * @param ProxyQueryInterface $query
     * @param string              $context
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, string $context = 'list'): void;

    /**
     * Get a chance to modify a newly created instance.
     *
     * @param AdminInterface $admin
     * @param object         $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function alterNewInstance(AdminInterface $admin, object $object): void;

    /**
     * Get a chance to modify object instance.
     *
     * @param AdminInterface $admin
     * @param object         $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function alterObject(AdminInterface $admin, object $object): void;

    /**
     * Get a chance to add persistent parameters.
     *
     * @param AdminInterface $admin
     *
     * @return array<string, mixed>
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function getPersistentParameters(AdminInterface $admin): array;

    /**
     * Return the controller access mapping.
     *
     * @return array<string, string|string[]>
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    // NEXT_MAJOR: Uncomment this method
    // public function getAccessMapping(AdminInterface $admin): array;

    /**
     * Returns the list of batch actions.
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureBatchActions(AdminInterface $admin, array $actions): array;

    /**
     * Get a chance to modify export fields.
     *
     * @return string[]
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureExportFields(AdminInterface $admin, array $fields): array;

    /**
     * @param AdminInterface $admin
     * @param object         $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function preUpdate(AdminInterface $admin, object $object): void;

    /**
     * @param AdminInterface $admin
     * @param object         $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function postUpdate(AdminInterface $admin, object $object): void;

    /**
     * @param AdminInterface $admin
     * @param object         $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function prePersist(AdminInterface $admin, object $object): void;

    /**
     * @param AdminInterface $admin
     * @param object         $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function postPersist(AdminInterface $admin, object $object): void;

    /**
     * @param AdminInterface $admin
     * @param object         $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function preRemove(AdminInterface $admin, object $object): void;

    /**
     * @param AdminInterface $admin
     * @param object         $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function postRemove(AdminInterface $admin, object $object): void;

    /*
     * Get all action buttons for an action
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureActionButtons(AdminInterface $admin, array $list, string $action, object $object): array;

    /*
     * NEXT_MAJOR: Uncomment this method
     *
     * Returns a list of default filters
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    // public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void;

    /*
     * NEXT_MAJOR: Uncomment this method
     *
     * Returns a list of default sort values
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    // public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void;
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
