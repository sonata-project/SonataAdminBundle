<?php

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
 * Interface AuditReaderInterface.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AuditReaderInterface
{
    /**
     * @param string $className
     * @param string $id
     * @param string $revision
     */
    public function find($className, $id, $revision);

    /**
     * @param string $className
     * @param int    $limit
     * @param int    $offset
     */
    public function findRevisionHistory($className, $limit = 20, $offset = 0);

    /**
     * @param string $classname
     * @param string $revision
     */
    public function findRevision($classname, $revision);

    /**
     * @param string $className
     * @param string $id
     */
    public function findRevisions($className, $id);

    /**
     * @param string $className
     * @param int    $id
     * @param int    $oldRevision
     * @param int    $newRevision
     */
    public function diff($className, $id, $oldRevision, $newRevision);
}
