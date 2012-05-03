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
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpDocSignatureInspection */
interface AdminExtensionInterface
{
    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     *
     * @return void
     */
    function configureFormFields(FormMapper $form);

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $list
     *
     * @return void
     */
    function configureListFields(ListMapper $list);

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $filter
     *
     * @return void
     */
    function configureDatagridFilters(DatagridMapper $filter);

    /**
     * @param \Sonata\AdminBundle\Show\ShowMapper $filter
     *
     * @return void
     */
    function configureShowFields(ShowMapper $filter);

    /**
     * @param AdminInterface                                $admin
     * @param \Sonata\AdminBundle\Route\RouteCollection     $collection
     *
     * @return void
     */
    function configureRoutes(AdminInterface $admin, RouteCollection $collection);

    /**
     * @param AdminInterface           $admin
     * @param \Knp\Menu\ItemInterface  $menu
     * @param string                   $action
     * @param null|AdminInterface      $childAdmin
     *
     * @return mixed
     */
    function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null);

    /**
     * @param AdminInterface                             $admin
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param mixed                                      $object
     *
     * @return void
     */
    function validate(AdminInterface $admin, ErrorElement $errorElement, $object);

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $query
     * @param string                                           $context
     *
     * @return void
     */
    function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list');
}
