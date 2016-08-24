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

/**
 * @author Christian Gripp <mail@core23.de>
 */
interface AdminDefinitionInterface
{
    /**
     * Returns the array of allowed export formats.
     *
     * @return array
     */
    public function getExportFormats();

    /**
     * @param FormMapper $formMapperMapper
     * @param mixed      $subject
     */
    public function configureFormFields(FormMapper $formMapperMapper, $subject);

    /**
     * @param ShowMapper $showMapper
     * @param mixed      $subject
     */
    public function configureShowFields(ShowMapper $showMapper, $subject);

    /**
     * @param ListMapper $listMapperMapper
     */
    public function configureListFields(ListMapper $listMapperMapper);

    /**
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper);

    /**
     * @param RouteCollection $routeCollection
     */
    public function configureRoutes(RouteCollection $routeCollection);

    /**
     * Allows you to customize batch actions.
     *
     * @param array $actions List of actions
     */
    public function configureBatchActions(array &$actions);

    /**
     * Configures the tab menu in your admin.
     *
     * @param MenuItemInterface $menu
     * @param string            $action
     * @param AdminInterface    $childAdmin
     *
     * @return mixed
     */
    public function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null);
}
