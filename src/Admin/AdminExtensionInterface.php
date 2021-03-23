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
 * @method array configureDashboardActions(AdminInterface $admin, array $actions)
 * @method void  configureDefaultFilterValues(AdminInterface $admin, array &$filterValues)
 * @method void  configureDefaultSortValues(AdminInterface $admin, array &$sortValues)
 * @method void  configureFormOptions(AdminInterface $admin, array &$formOptions)
 * @method array configurePersistentParameters(AdminInterface $admin, array $parameters)
 * @method array configureFilterParameters(AdminInterface $admin, array $parameters)
 *
 * @phpstan-template T of object
 */
interface AdminExtensionInterface
{
    /**
     * @return void
     */
    public function configureFormFields(FormMapper $formMapper);

    /**
     * @return void
     */
    public function configureListFields(ListMapper $listMapper);

    /**
     * @return void
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper);

    /**
     * @return void
     */
    public function configureShowFields(ShowMapper $showMapper);

    /**
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection);

    /**
     * DEPRECATED: Use configureTabMenu instead.
     *
     * NEXT_MAJOR: remove this method.
     *
     * @param string $action
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param AdminInterface<object>|null $childAdmin
     *
     * @deprecated
     */
    public function configureSideMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        ?AdminInterface $childAdmin = null
    );

    /**
     * Builds the tab menu.
     *
     * @param string $action
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param AdminInterface<object>|null $childAdmin
     */
    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        ?AdminInterface $childAdmin = null
    );

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @param object $object
     *
     * @return void
     *
     * @deprecated since sonata-project/admin-bundle 3.82.
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function validate(AdminInterface $admin, ErrorElement $errorElement, $object);

    /**
     * @param string $context
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list');

    /**
     * Get a chance to modify a newly created instance.
     *
     * @param object $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function alterNewInstance(AdminInterface $admin, $object);

    /**
     * Get a chance to modify object instance.
     *
     * @param object $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function alterObject(AdminInterface $admin, $object);

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.90, use configurePersistentParameters() instead.
     *
     * Get a chance to add persistent parameters.
     *
     * @return array<string, mixed>
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function getPersistentParameters(AdminInterface $admin);

    /**
     * Get a chance to add persistent parameters.
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     *
     * @phpstan-param AdminInterface<T> $admin
     */
//    public function configurePersistentParameters(AdminInterface $admin, array $parameters);

    /**
     * Return the controller access mapping.
     *
     * @return array<string, string|string[]>
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    // NEXT_MAJOR: Uncomment this method
    // public function getAccessMapping(AdminInterface $admin): array;

    /**
     * Returns the list of batch actions.
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureBatchActions(AdminInterface $admin, array $actions): array;

    /**
     * Get a chance to modify export fields.
     *
     * @return string[]
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureExportFields(AdminInterface $admin, array $fields): array;

    /**
     * @param object $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function preUpdate(AdminInterface $admin, $object);

    /**
     * @param object $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function postUpdate(AdminInterface $admin, $object);

    /**
     * @param object $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function prePersist(AdminInterface $admin, $object);

    /**
     * @param object $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function postPersist(AdminInterface $admin, $object);

    /**
     * @param object $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function preRemove(AdminInterface $admin, $object);

    /**
     * @param object $object
     *
     * @return void
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function postRemove(AdminInterface $admin, $object);

    /*
     * Get all action buttons for an action
     *
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureActionButtons(AdminInterface $admin, array $list, string $action, object $object): array;

    /*
     * @phpstan-param AdminInterface<T> $admin
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureDashboardActions(AdminInterface $admin, array $actions): array;

    /*
     * NEXT_MAJOR: Uncomment this method
     *
     * Returns a list of default filters
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    // public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void;

    /*
     * NEXT_MAJOR: Uncomment this method
     *
     * Returns a list of default sort values
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    // public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void;

    /*
     * NEXT_MAJOR: Uncomment this method and remove the corresponding @method annotation.
     *
     * Returns a list of form options
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    // public function configureFormOptions(AdminInterface $admin, array &$formOptions): void;

    /*
     * NEXT_MAJOR: Uncomment this method and remove the corresponding @method annotation.
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    //public function configureFilterParameters(AdminInterface $admin, array $parameters): array;
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
