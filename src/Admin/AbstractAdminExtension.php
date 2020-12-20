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

    public function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
    }

    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
        // Use configureSideMenu not to mess with previous overrides
        // NEXT_MAJOR: remove this line
        $this->configureSideMenu($admin, $menu, $action, $childAdmin);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param AdminInterface $admin
     * @param ErrorElement   $errorElement
     * @param object         $object
     */
    public function validate(AdminInterface $admin, ErrorElement $errorElement, object $object): void
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[3] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated since version 3.x and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }
    }

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, string $context = 'list'): void
    {
    }

    public function alterNewInstance(AdminInterface $admin, object $object): void
    {
    }

    public function alterObject(AdminInterface $admin, object $object): void
    {
    }

    public function getPersistentParameters(AdminInterface $admin): array
    {
        return [];
    }

    /**
     * @param AdminInterface $admin
     *
     * @return array<string, string|string[]>
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function getAccessMapping(AdminInterface $admin): array
    {
        return [];
    }

    /**
     * @phpstan-param AdminInterface<object> $admin
     *
     * @param AdminInterface $admin
     * @param array          $actions
     *
     * @return array
     */
    public function configureBatchActions(AdminInterface $admin, array $actions): array
    {
        return $actions;
    }

    /**
     * @phpstan-param AdminInterface<object> $admin
     *
     * @param AdminInterface $admin
     * @param array          $fields
     *
     * @return array
     */
    public function configureExportFields(AdminInterface $admin, array $fields): array
    {
        return $fields;
    }

    public function preUpdate(AdminInterface $admin, object $object): void
    {
    }

    public function postUpdate(AdminInterface $admin, object $object): void
    {
    }

    public function prePersist(AdminInterface $admin, object $object): void
    {
    }

    public function postPersist(AdminInterface $admin, object $object): void
    {
    }

    public function preRemove(AdminInterface $admin, object $object): void
    {
    }

    public function postRemove(AdminInterface $admin, object $object): void
    {
    }

    /**
     * @param AdminInterface       $admin
     * @param array<string, mixed> $list
     * @param string               $action
     * @param object               $object
     *
     * @return array<string, mixed>
     *
     * @phpstan-param AdminInterface<object> $admin
     */
    public function configureActionButtons(AdminInterface $admin, array $list, string $action, object $object): array
    {
        return $list;
    }

    /**
     * @phpstan-param AdminInterface<object> $admin
     *
     * @param AdminInterface $admin
     * @param array          $filterValues
     */
    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues): void
    {
    }

    /**
     * @phpstan-param AdminInterface<object> $admin
     *
     * @param AdminInterface $admin
     * @param array          $sortValues
     */
    public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void
    {
    }
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
