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

namespace SensioLabs\AdminBundle\Model\ORM;

use SimpleThings\EntityAudit\AuditReader as SimpleThingsAuditReader;
use SimpleThings\EntityAudit\Revision as EntityAuditRevision;
use SensioLabs\AdminBundle\Model\AuditReaderInterface;
use SensioLabs\AdminBundle\Model\Revision;

/**
 * @phpstan-template T of object
 * @phpstan-implements AuditReaderInterface<T>
 */
final class AuditReader implements AuditReaderInterface
{
    public function __construct(private SimpleThingsAuditReader $auditReader)
    {
    }

    /**
     * @param int|string $id
     * @param int|string $revisionId
     *
     * @phpstan-param class-string<T> $className
     * @phpstan-return T|null
     */
    public function find(string $className, $id, $revisionId): ?object
    {
        try {
            return $this->auditReader->find($className, $id, $revisionId);
        } catch (\Throwable) {
            return null;
        }
    }

    public function findRevisionHistory(string $className, ?int $limit = null, int $offset = 0): array
    {
        return array_map(
            $this->createRevisionFromEntityAuditRevision(...),
            $this->auditReader->findRevisionHistory($limit, $offset)
        );
    }

    public function findRevision(string $className, $revisionId): ?Revision
    {
        try {
            return $this->createRevisionFromEntityAuditRevision($this->auditReader->findRevision($revisionId));
        } catch (\Throwable) {
            return null;
        }
    }

    public function findRevisions(string $className, $id): array
    {
        try {
            return array_map(
                $this->createRevisionFromEntityAuditRevision(...),
                $this->auditReader->findRevisions($className, $id)
            );
        } catch (\Throwable) {
            return [];
        }
    }

    public function diff(string $className, $id, $oldRevisionId, $newRevisionId): array
    {
        try {
            return $this->auditReader->diff($className, $id, $oldRevisionId, $newRevisionId);
        } catch (\Throwable) {
            return [];
        }
    }

    private function createRevisionFromEntityAuditRevision(EntityAuditRevision $revision): Revision
    {
        return new Revision($revision->getRev(), $revision->getTimestamp(), $revision->getUsername());
    }
}
