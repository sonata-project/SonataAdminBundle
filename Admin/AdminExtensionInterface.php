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

use Knp\Menu\ItemInterface as MenuItemInterface;

interface AdminExtensionInterface
{
    /**
     * @abstract
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     * @return void
     */
    function configureFormFields(FormMapper $form);

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $list
     * @return void
     */
    function configureListFields(ListMapper $list);

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $filter
     * @return void
     */
    function configureDatagridFilters(DatagridMapper $filter);

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Show\ShowMapper $filter
     * @return void
     */
    function configureShowFields(ShowMapper $filter);

    /**
     * @abstract
     * @param Admin $admin
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     * @return void
     */
    function configureRoutes(Admin $admin, RouteCollection $collection);

    /**
     * @abstract
     * @param Admin $admin
     * @param \Knp\Menu\MenuItem $menu
     * @param string $action
     * @param null|Admin $childAdmin
     * @return void
     */
    function configureSideMenu(Admin $admin, MenuItemInterface $menu, $action, Admin $childAdmin = null);

    /**
     * @abstract
     * @param Admin $admin
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param $object
     * @return void
     */
    function validate(Admin $admin, ErrorElement $errorElement, $object);
}
