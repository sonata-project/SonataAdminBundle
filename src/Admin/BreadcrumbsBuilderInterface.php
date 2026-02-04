<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Admin;

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
     * @param AdminInterface<object> $admin
     * @param string                 $action the name of the action we want to get a breadcrumbs for
     *
     * @return ItemInterface[] the breadcrumbs
     */
    public function getBreadcrumbs(AdminInterface $admin, string $action): array;
}
