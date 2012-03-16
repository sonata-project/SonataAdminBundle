<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Model;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

interface AuditReaderInterface
{
    /**
     * @abstract
     * @param $className
     * @param $id
     * @param $revision
     */
    function find($className, $id, $revision);

    /**
     * @abstract
     * @param $className
     * @param int $limit
     * @param int $offset
     */
    function findRevisionHistory($className, $limit = 20, $offset = 0);

    /**
     * @abstract
     * @param $classname
     * @param $revision
     */
    function findRevision($classname, $revision);

    /**
     * @abstract
     * @param $className
     * @param $id
     */
    function findRevisions($className, $id);
}