<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\FieldDescription;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListCollection;


interface ListBuilderInterface
{
    /**
     * @abstract
     * @param array $options
     * @return void
     */
    function getBaseList(array $options = array());

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Datagrid\ListCollection $list
     * @param \Sonata\AdminBundle\Admin\FieldDescription $fieldDescription
     * @return void
     */
    function addField(ListCollection $list, FieldDescription $fieldDescription);

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Admin\Admin $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescription $fieldDescription
     * @param array $options
     * @return void
     */
    function fixFieldDescription(Admin $admin, FieldDescription $fieldDescription, array $options = array());
}
