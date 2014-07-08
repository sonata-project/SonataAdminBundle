<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

use Knp\Menu\ItemInterface as MenuItemInterface;

/**
 *
 */
interface AdminExtensionInterface
{
    /**
     * @param FormMapper $form
     */
    public function configureFormFields(FormMapper $form);

    /**
     * @param ListMapper $list
     */
    public function configureListFields(ListMapper $list);

    /**
     * @param DatagridMapper $filter
     */
    public function configureDatagridFilters(DatagridMapper $filter);

    /**
     * @param ShowMapper $filter
     */
    public function configureShowFields(ShowMapper $filter);

    /**
     * @param AdminInterface  $admin
     * @param RouteCollection $collection
     */
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection);

    /**
     * DEPRECATED: Use configureTabMenu instead
     *
     * @param AdminInterface    $admin
     * @param MenuItemInterface $menu
     * @param string            $action
     * @param AdminInterface    $childAdmin
     *
     * @return mixed
     *
     * @deprecated
     */
    public function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null);

    /**
     * Builds the tab menu
     *
     * @param AdminInterface    $admin
     * @param MenuItemInterface $menu
     * @param string            $action
     * @param AdminInterface    $childAdmin
     *
     * @return mixed
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
     *
     * @return mixed
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
     * Get a chance to modify object instance
     *
     * @param  AdminInterface $admin
     * @param  $object
     * @return mixed
     */
    public function alterObject(AdminInterface $admin, $object);

    /**
     * Get a chance to add persistent parameters
     *
     * @param  AdminInterface $admin
     * @return array
     */
    public function getPersistentParameters(AdminInterface $admin);

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
}
