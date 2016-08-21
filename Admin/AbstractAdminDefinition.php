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
abstract class AbstractAdminDefinition implements AdminDefinitionInterface, AdminHookInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExportFormats()
    {
        return array(
            'json',
            'xml',
            'csv',
            'xls',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureBatchActions(array &$actions)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preBatchAction($action, ProxyQueryInterface $query, array &$idx, $allElements)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureRoutes(RouteCollection $routeCollection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
    }
}
