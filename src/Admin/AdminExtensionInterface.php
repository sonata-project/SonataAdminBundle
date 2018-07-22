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

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
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
     * @param mixed $object
     */
    public function validate(AdminInterface $admin, ErrorElement $errorElement, $object);

    /**
     * @param string $context
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list');

    /**
     * Get a chance to modify a newly created instance.
     *
     * @param mixed $object
     */
    public function alterNewInstance(AdminInterface $admin, $object);

    /**
     * Get a chance to modify object instance.
     *
     * @param mixed $object
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
     *
     * @return array
     */
    // TODO: Uncomment in next major release
    // public function getAccessMapping(AdminInterface $admin);

    /**
     * Returns the list of batch actions.
     *
     * @param array $actions
     *
     * @return array
     */
    // TODO: Uncomment in next major release
    // public function configureBatchActions(AdminInterface $admin, array $actions);

    /**
     * Get a chance to modify export fields.
     *
     * @param string[] $fields
     *
     * @return string[]
     */
    // TODO: Uncomment in next major release
    // public function configureExportFields(AdminInterface $admin, array $fields);

    /**
     * @param mixed $object
     */
    public function preUpdate(AdminInterface $admin, $object);

    /**
     * @param mixed $object
     */
    public function postUpdate(AdminInterface $admin, $object);

    /**
     * @param mixed $object
     */
    public function prePersist(AdminInterface $admin, $object);

    /**
     * @param mixed $object
     */
    public function postPersist(AdminInterface $admin, $object);

    /**
     * @param mixed $object
     */
    public function preRemove(AdminInterface $admin, $object);

    /**
     * @param mixed $object
     */
    public function postRemove(AdminInterface $admin, $object);

    /*
     * Get all action buttons for an action
     *
     * @param array          $list
     * @param string         $action
     * @param mixed          $object
     *
     * @return array
     */
    // TODO: Uncomment in next major release
    // public function configureActionButtons(AdminInterface $admin, $list, $action, $object);

    /*
     * NEXT_MAJOR: Uncomment in next major release
     *
     * Returns a list of default filters
     *
     * @param array          $filterValues
     */
    // public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues);
}
