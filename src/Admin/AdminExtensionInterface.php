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
use Sonata\AdminBundle\Admin\Extension\AlterNewInstanceInterface;
use Sonata\AdminBundle\Admin\Extension\ConfigureDatagridFieldsInterface;
use Sonata\AdminBundle\Admin\Extension\ConfigureFormFieldsInterface;
use Sonata\AdminBundle\Admin\Extension\ConfigureListFieldsInterface;
use Sonata\AdminBundle\Admin\Extension\ConfigureQueryInterface;
use Sonata\AdminBundle\Admin\Extension\ConfigureRoutesInterface;
use Sonata\AdminBundle\Admin\Extension\ConfigureShowFieldsInterface;
use Sonata\AdminBundle\Admin\Extension\ConfigureTabMenuInterface;
use Sonata\AdminBundle\Admin\Extension\GetPersistentParametersInterface;
use Sonata\AdminBundle\Admin\Extension\PostPersistInterface;
use Sonata\AdminBundle\Admin\Extension\PostRemoveInterface;
use Sonata\AdminBundle\Admin\Extension\PostUpdateInterface;
use Sonata\AdminBundle\Admin\Extension\PrePersistInterface;
use Sonata\AdminBundle\Admin\Extension\PreRemoveInterface;
use Sonata\AdminBundle\Admin\Extension\PreUpdateInterface;
use Sonata\AdminBundle\Admin\Extension\ValidateInterface;

@trigger_error(sprintf(
    '"%s" is deprecated since 3.x and will be removed in 4.0.'
    .' Implement single interfaces from "Sonata\AdminBundle\Admin\Extension" namespace.',
    AdminExtensionInterface::class
), E_USER_DEPRECATED);

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AdminExtensionInterface extends ConfigureFormFieldsInterface, ConfigureListFieldsInterface, ConfigureDatagridFieldsInterface, ConfigureShowFieldsInterface, ConfigureRoutesInterface, ConfigureTabMenuInterface, ValidateInterface, ConfigureQueryInterface, AlterNewInstanceInterface, GetPersistentParametersInterface, PreUpdateInterface, PostUpdateInterface, PrePersistInterface, PostPersistInterface, PreRemoveInterface, PostRemoveInterface
{
    /**
     * DEPRECATED: Use configureTabMenu instead.
     *
     * NEXT_MAJOR: remove this method.
     *
     * @param string $action
     *
     * @deprecated
     */
    public function configureSideMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        AdminInterface $childAdmin = null
    );

    /*
     * Return the controller access mapping.
     *
     * @return array
     */
    // TODO: Uncomment in next major release
    // public function getAccessMapping(AdminInterface $admin);

    /*
     * Returns the list of batch actions.
     *
     * @param array $actions
     *
     * @return array
     */
    // TODO: Uncomment in next major release
    // public function configureBatchActions(AdminInterface $admin, array $actions);

    /*
     * Get a chance to modify export fields.
     *
     * @param string[] $fields
     *
     * @return string[]
     */
    // TODO: Uncomment in next major release
    // public function configureExportFields(AdminInterface $admin, array $fields);

    /*
     * Get all action buttons for an action
     *
     * @param array          $list
     * @param string         $action
     * @param mixed          $object
     *
     * @return array
     */
    // TODO: Uncomment in next major release
    // public function configureActionButtons(AdminInterface $admin, $list, $action, $object);

    /*
     * NEXT_MAJOR: Uncomment in next major release
     *
     * Returns a list of default filters
     *
     * @param array          $filterValues
     */
    // public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues);
}
