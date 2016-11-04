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

use Knp\Menu\ItemInterface;

/**
 * @author Christian Gripp <mail@core23.de>
 */
interface ActionBuilderInterface
{
    /**
     * Get action menu for $action.
     *
     * @param AdminInterface $admin
     * @param string         $action     the name of the action we want to get a menu for
     * @param AdminInterface $childAdmin
     *
     * @return ItemInterface the menu
     */
    public function getActionMenu(AdminInterface $admin, $action, AdminInterface $childAdmin = null);
}
