<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;

/**
 * Interface DatagridBuilderInterface.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface DatagridBuilderInterface extends BuilderInterface
{
    /**
     * @abstract
     *
     * @param \Sonata\AdminBundle\Datagrid\DatagridInterface      $datagrid
     * @param string                                              $type
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param \Sonata\AdminBundle\Admin\AdminInterface            $admin
     */
    public function addFilter(DatagridInterface $datagrid, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin);

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param array                                    $values
     *
     * @return \Sonata\AdminBundle\Datagrid\DatagridInterface
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = array());
}
