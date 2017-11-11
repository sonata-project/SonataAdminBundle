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
 * Builds a breacrumbs. There is a dependency on the AdminInterface because
 * this object holds useful object to deal with this task, but there is
 * probably a better design.
 *
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
interface BreadcrumbsBuilderInterface
{
    /**
     * Get breadcrumbs for $action.
     *
     * @param AdminInterface $admin
     * @param string         $action the name of the action we want to get a
     *                               breadcrumbs for
     *
     * @return mixed array|Traversable the breadcrumbs
     */
    public function getBreadcrumbs(AdminInterface $admin, $action);

    /**
     * Builds breadcrumbs for $action, starting from $menu.
     *
     * Note: the method will be called by the top admin instance (parent => child)
     * NEXT_MAJOR : remove this method from the public interface.
     *
     * @param AdminInterface     $admin
     * @param string             $action
     * @param ItemInterface|null $menu
     */
    public function buildBreadcrumbs(
        AdminInterface $admin,
        $action,
        ItemInterface $menu = null
    );
}
