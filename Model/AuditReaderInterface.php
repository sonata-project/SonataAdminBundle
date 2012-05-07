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
     * @param string $className
     * @param string $id
     * @param string $revision
     */
    function find($className, $id, $revision);

    /**
     * @param string $className
     * @param int    $limit
     * @param int    $offset
     */
    function findRevisionHistory($className, $limit = 20, $offset = 0);

    /**
     * @param string $classname
     * @param string $revision
     */
    function findRevision($classname, $revision);

    /**
     * @param string $className
     * @param string $id
     */
    function findRevisions($className, $id);
}