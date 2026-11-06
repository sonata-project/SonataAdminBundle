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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AuditReaderInterface
{
    /**
     * @param string $className
     * @param string $id
     * @param string $revision
     *
     * @return object
     */
    public function find($className, $id, $revision);

    /**
     * @param string $className
     * @param int    $limit
     * @param int    $offset
     *
     * @return object[]
     */
    public function findRevisionHistory($className, $limit = 20, $offset = 0);

    /**
     * @param string $classname
     * @param string $revision
     *
     * @return object
     */
    public function findRevision($classname, $revision);

    /**
     * @param string $className
     * @param string $id
     *
     * @return object[]
     */
    public function findRevisions($className, $id);

    /**
     * @param string $className
     * @param int    $id
     * @param int    $oldRevision
     * @param int    $newRevision
     *
     * @return array
     */
    public function diff($className, $id, $oldRevision, $newRevision);
}
