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

//    NEXT_MAJOR: Uncomment the following lines.
//    /**
//     * Return all the allowed _per_page values.
//     *
//     * @return array<int>
//     */
//    public function getDefaultPerPageOptions(string $class): array;
}
