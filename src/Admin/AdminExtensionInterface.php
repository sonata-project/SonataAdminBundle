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
     * @param string $action
     *
     * @deprecated
     */
    public function configureSideMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        AdminInterface $childAdmin = null
    );

    /**
     * Builds the tab menu.
     *
     * @param string $action
     */
    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        AdminInterface $childAdmin = null
    );

    /**
     * @param object $object
     */
    public function validate(AdminInterface $admin, ErrorElement $errorElement, $object);

    /**
     * @param string $context
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list');

    /**
     * Get a chance to modify a newly created instance.
     *
     * @param object $object
     */
    public function alterNewInstance(AdminInterface $admin, $object);

    /**
     * Get a chance to modify object instance.
     *
     * @param object $object
     */
    public function alterObject(AdminInterface $admin, $object);

    /**
     * Get a chance to add persistent parameters.
     *
     * @return array
     */
    public function getPersistentParameters(AdminInterface $admin);

    /**
     * Return the controller access mapping.
     */
    // NEXT_MAJOR: Uncomment this method
    // public function getAccessMapping(AdminInterface $admin): array;

    /**
     * Returns the list of batch actions.
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureBatchActions(AdminInterface $admin, array $actions): array;

    /**
     * Get a chance to modify export fields.
     *
     * @return string[]
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureExportFields(AdminInterface $admin, array $fields): array;

    /**
     * @param object $object
     */
    public function preUpdate(AdminInterface $admin, $object);

    /**
     * @param object $object
     */
    public function postUpdate(AdminInterface $admin, $object);

    /**
     * @param object $object
     */
    public function prePersist(AdminInterface $admin, $object);

    /**
     * @param object $object
     */
    public function postPersist(AdminInterface $admin, $object);

    /**
     * @param object $object
     */
    public function preRemove(AdminInterface $admin, $object);

    /**
     * @param object $object
     */
    public function postRemove(AdminInterface $admin, $object);

    /*
     * Get all action buttons for an action
     */
    // NEXT_MAJOR: Uncomment this method
    // public function configureActionButtons(AdminInterface $admin, array $list, string $action, object $object): array;

    /*
     * NEXT_MAJOR: Uncomment this method
     *
     * Returns a list of default filters
     */
    // public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void;
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
