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
 */
abstract class AbstractAdminExtension implements AdminExtensionInterface
{
    public function configureFormFields(FormMapper $formMapper): void
    {
    }

    public function configureListFields(ListMapper $listMapper): void
    {
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
    }

    public function configureShowFields(ShowMapper $showMapper): void
    {
    }

    public function configureRoutes(AdminInterface $admin, RouteCollection $collection): void
    {
    }

    public function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null): void
    {
    }

    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, $action, AdminInterface $childAdmin = null): void
    {
        // Use configureSideMenu not to mess with previous overrides
        // TODO remove once deprecation period is over
        $this->configureSideMenu($admin, $menu, $action, $childAdmin);
    }

    public function validate(AdminInterface $admin, ErrorElement $errorElement, $object): void
    {
    }

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list'): void
    {
    }

    public function alterNewInstance(AdminInterface $admin, $object): void
    {
    }

    public function alterObject(AdminInterface $admin, $object): void
    {
    }

    public function getPersistentParameters(AdminInterface $admin)
    {
        return [];
    }

    public function getAccessMapping(AdminInterface $admin)
    {
        return [];
    }

    public function configureBatchActions(AdminInterface $admin, array $actions)
    {
        return $actions;
    }

    public function configureExportFields(AdminInterface $admin, array $fields)
    {
        return $fields;
    }

    public function preUpdate(AdminInterface $admin, $object): void
    {
    }

    public function postUpdate(AdminInterface $admin, $object): void
    {
    }

    public function prePersist(AdminInterface $admin, $object): void
    {
    }

    public function postPersist(AdminInterface $admin, $object): void
    {
    }

    public function preRemove(AdminInterface $admin, $object): void
    {
    }

    public function postRemove(AdminInterface $admin, $object): void
    {
    }

    public function configureActionButtons(AdminInterface $admin, $list, $action, $object)
    {
        return $list;
    }

    /**
     * Returns a list of default filters.
     */
    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void
    {
    }
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
