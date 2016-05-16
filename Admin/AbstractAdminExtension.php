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
abstract class AbstractAdminExtension implements AdminExtensionInterface
{
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
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        // Use configureSideMenu not to mess with previous overrides
        // TODO remove once deprecation period is over
        $this->configureSideMenu($admin, $menu, $action, $childAdmin);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(AdminInterface $admin, ErrorElement $errorElement, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list')
    {
    }

    /**
     * {@inheritdoc}
     */
    public function alterNewInstance(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function alterObject(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters(AdminInterface $admin)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessMapping(AdminInterface $admin)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function configureBatchActions(AdminInterface $admin, array $actions)
    {
        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function configureExportFields(AdminInterface $admin, array $fields)
    {
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove(AdminInterface $admin, $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureActionButtons(AdminInterface $admin, $list, $action, $object)
    {
        return $list;
    }
}
