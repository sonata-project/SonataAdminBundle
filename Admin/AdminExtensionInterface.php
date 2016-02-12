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
 * Interface AdminExtensionInterface.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AdminExtensionInterface
{
    /**
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper);

    /**
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper);

    /**
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper);

    /**
     * @param ShowMapper $showMapper
     */
    public function configureShowFields(ShowMapper $showMapper);

    /**
     * @param AdminInterface  $admin
     * @param RouteCollection $collection
     */
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection);

    /**
     * DEPRECATED: Use configureTabMenu instead.
     *
     * @param AdminInterface    $admin
     * @param MenuItemInterface $menu
     * @param string            $action
     * @param AdminInterface    $childAdmin
     *
     * @deprecated
     */
    public function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null);

    /**
     * Builds the tab menu.
     *
     * @param AdminInterface    $admin
     * @param MenuItemInterface $menu
     * @param string            $action
     * @param AdminInterface    $childAdmin
     */
    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null);

    /**
     * @param AdminInterface $admin
     * @param ErrorElement   $errorElement
     * @param mixed          $object
     */
    public function validate(AdminInterface $admin, ErrorElement $errorElement, $object);

    /**
     * @param AdminInterface      $admin
     * @param ProxyQueryInterface $query
     * @param string              $context
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list');

    /**
     * Get a chance to modify a newly created instance.
     *
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function alterNewInstance(AdminInterface $admin, $object);

    /**
     * Get a chance to modify object instance.
     *
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function alterObject(AdminInterface $admin, $object);

    /**
     * Get a chance to add persistent parameters.
     *
     * @param AdminInterface $admin
     *
     * @return array
     */
    public function getPersistentParameters(AdminInterface $admin);

    /**
     * Return the controller access mapping.
     *
     * @param AdminInterface $admin
     *
     * @return array
     */
    // TODO: Uncomment in next major release
    // public function getAccessMapping(AdminInterface $admin);

    /**
     * Returns the list of batch actions.
     *
     * @param AdminInterface $admin
     * @param array          $actions
     *
     * @return array
     */
    // TODO: Uncomment in next major release
    // public function configureBatchActions(AdminInterface $admin, array $actions);

    /**
     * Get a chance to modify export fields.
     *
     * @param AdminInterface $admin
     * @param string[]       $fields
     *
     * @return string[]
     */
    // TODO: Uncomment in next major release
    // public function configureExportFields(AdminInterface $admin, array $fields);

    /**
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function preUpdate(AdminInterface $admin, $object);

    /**
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function postUpdate(AdminInterface $admin, $object);

    /**
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function prePersist(AdminInterface $admin, $object);

    /**
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function postPersist(AdminInterface $admin, $object);

    /**
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function preRemove(AdminInterface $admin, $object);

    /**
     * @param AdminInterface $admin
     * @param mixed          $object
     */
    public function postRemove(AdminInterface $admin, $object);

    /*
     * Get all action buttons for an action
     *
     * @param AdminInterface $admin
     * @param array          $list
     * @param string         $action
     * @param mixed          $object
     *
     * @return array
     */
    // TODO: Uncomment in next major release
    // public function configureActionButtons(AdminInterface $admin, $list, $action, $object);
}
