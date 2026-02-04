<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Fixtures\Model;

use SensioLabs\AdminBundle\Model\AuditReaderInterface;
use SensioLabs\AdminBundle\Model\Revision;

/**
 * @phpstan-implements AuditReaderInterface<object>
 */
final class AuditReader implements AuditReaderInterface
{
    public function find(string $className, $id, $revisionId): object
    {
        return new $className();
    }

    public function findRevisionHistory(string $className, int $limit = 20, int $offset = 0): array
    {
        return [
            new Revision(1, new \DateTime(), 'Jack'),
        ];
    }

    public function findRevision(string $className, $revisionId): Revision
    {
        return new Revision(1, new \DateTime(), 'Jack');
    }

    public function findRevisions(string $className, $id): array
    {
        return [
            new Revision(1, new \DateTime(), 'Jack'),
        ];
    }

    public function diff(string $className, $id, $oldRevisionId, $newRevisionId): array
    {
        return [];
    }
}
