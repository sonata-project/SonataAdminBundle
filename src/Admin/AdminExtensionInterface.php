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
 * @method void configureDefaultSortValues(AdminInterface $admin, array &$sortValues)
 */
interface AdminExtensionInterface
{
    public function configureFormFields(FormMapper $formMapper);

    public function configureListFields(ListMapper $listMapper);

    public function configureDatagridFilters(DatagridMapper $datagridMapper);

    public function configureShowFields(ShowMapper $showMapper);

    public function configureRoutes(AdminInterface $admin, RouteCollection $collection);

    /**
     * DEPRECATED: Use configureTabMenu instead.
     *
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated
     */
    public function configureSideMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        string $action,
        ?AdminInterface $childAdmin = null
    );

    /**
     * Builds the tab menu.
     */
    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        string $action,
        ?AdminInterface $childAdmin = null
    ): void;

    public function validate(AdminInterface $admin, ErrorElement $errorElement, object $object): void;

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, string $context = 'list'): void;

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
     */
    public function getPersistentParameters(AdminInterface $admin): array;

    /**
     * Return the controller access mapping.
     */
    public function getAccessMapping(AdminInterface $admin): array;

    /**
     * Returns the list of batch actions.
     */
    public function configureBatchActions(AdminInterface $admin, array $actions): array;

    /**
     * Get a chance to modify export fields.
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
     * @param object $object
     *
     * @return void
     */
    public function configureActionButtons(
        AdminInterface $admin,
        array $list,
        string $action,
        ?object $object = null
    ): array;

    /**
     * Returns a list of default filters.
     */
    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void;

    /*
     * NEXT_MAJOR: Uncomment this method
     *
     * Returns a list of default sort values
     */
    // public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void;
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
