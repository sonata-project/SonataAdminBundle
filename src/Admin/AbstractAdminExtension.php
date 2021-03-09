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
 *
 * @phpstan-template T of object
 * @template-implements AdminExtensionInterface<T>
 */
abstract class AbstractAdminExtension implements AdminExtensionInterface
{
    public function configureFormFields(FormMapper $formMapper)
    {
    }

    public function configureListFields(ListMapper $listMapper)
    {
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
    }

    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
    }

    public function configureSideMenu(AdminInterface $admin, MenuItemInterface $menu, $action, ?AdminInterface $childAdmin = null)
    {
    }

    public function configureTabMenu(AdminInterface $admin, MenuItemInterface $menu, $action, ?AdminInterface $childAdmin = null)
    {
        // Use configureSideMenu not to mess with previous overrides
        // NEXT_MAJOR: remove this line
        $this->configureSideMenu($admin, $menu, $action, $childAdmin);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function validate(AdminInterface $admin, ErrorElement $errorElement, $object)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[3] ?? null)) {
            @trigger_error(sprintf(
                'The %s method is deprecated since version 3.82 and will be removed in 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }
    }

    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list')
    {
    }

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configurePersistentParameters(AdminInterface $admin, array $parameters): array
    {
        // NEXT_MAJOR: Return $parameters instead.
        return array_merge($parameters, $this->getPersistentParameters($admin));
    }

    public function alterNewInstance(AdminInterface $admin, $object)
    {
    }

    public function alterObject(AdminInterface $admin, $object)
    {
    }

    public function getPersistentParameters(AdminInterface $admin)
    {
        return [];
    }

    /**
     * @return array<string, string|string[]>
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function getAccessMapping(AdminInterface $admin)
    {
        return [];
    }

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureBatchActions(AdminInterface $admin, array $actions)
    {
        return $actions;
    }

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureExportFields(AdminInterface $admin, array $fields)
    {
        return $fields;
    }

    public function preUpdate(AdminInterface $admin, $object)
    {
    }

    public function postUpdate(AdminInterface $admin, $object)
    {
    }

    public function prePersist(AdminInterface $admin, $object)
    {
    }

    public function postPersist(AdminInterface $admin, $object)
    {
    }

    public function preRemove(AdminInterface $admin, $object)
    {
    }

    public function postRemove(AdminInterface $admin, $object)
    {
    }

    /**
     * @param array<string, mixed> $list
     * @param string               $action
     * @param object               $object
     *
     * @return array<string, mixed>
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureActionButtons(AdminInterface $admin, $list, $action, $object)
    {
        return $list;
    }

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues)
    {
    }

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureDefaultSortValues(AdminInterface $admin, array &$sortValues): void
    {
    }

    /**
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureFormOptions(AdminInterface $admin, array &$formOptions): void
    {
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     *
     * @phpstan-param AdminInterface<T> $admin
     */
    public function configureFilterParameters(AdminInterface $admin, array $parameters): array
    {
        return $parameters;
    }
}

class_exists(\Sonata\Form\Validator\ErrorElement::class);
