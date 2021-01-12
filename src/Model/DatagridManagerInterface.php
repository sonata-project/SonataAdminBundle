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

namespace Sonata\AdminBundle\Model;

/**
 * A datagrid manager is a bridge between the model classes and the admin datagrid functionality.
 *
 * NEXT_MAJOR: Remove this interface
 *
 * @deprecated since sonata-project/admin-bundle 3.79
 *
 * @method array getDefaultPerPageOptions(string $class)
 */
interface DatagridManagerInterface
{
    /**
     * Return _sort_order, _sort_by, _page and _per_page values.
     *
     * @param string $class
     *
     * @return array<string, int|string>
     */
    public function getDefaultSortValues($class);
}
