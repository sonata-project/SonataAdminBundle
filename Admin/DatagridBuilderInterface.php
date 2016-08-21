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

use Sonata\AdminBundle\Datagrid\DatagridInterface;

/**
 * Builds admin datagrids.
 *
 * @author Christian Gripp <mail@core23.de>
 */
interface DatagridBuilderInterface
{
    /**
     * @param AdminInterface $admin
     *
     * @return DatagridInterface
     */
    public function getDatagrid(AdminInterface $admin);

    /**
     * @param AdminInterface $admin
     *
     * @return FieldDescriptionCollection
     */
    public function getList(AdminInterface $admin);
}
