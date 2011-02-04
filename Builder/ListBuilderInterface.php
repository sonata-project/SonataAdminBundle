<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Builder;

use Sonata\BaseApplicationBundle\Admin\FieldDescription;
use Sonata\BaseApplicationBundle\Admin\Admin;
use Sonata\BaseApplicationBundle\Datagrid\ListCollection;


interface ListBuilderInterface
{
    function getBaseList(array $options = array());

    function addField(ListCollection $list, FieldDescription $fieldDescription);

    function fixFieldDescription(Admin $admin, FieldDescription $fieldDescription, array $options = array());

}
