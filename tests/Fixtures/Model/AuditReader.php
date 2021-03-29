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

namespace Sonata\AdminBundle\Tests\Fixtures\Model;

use Sonata\AdminBundle\Model\AuditReaderInterface;

final class AuditReader implements AuditReaderInterface
{
    public function find(string $className, $id, $revisionId): ?object
    {
        return new $className();
    }

    public function findRevisionHistory(string $className, int $limit = 20, int $offset = 0): array
    {
        return [
            new \stdClass(),
        ];
    }

    public function findRevision(string $className, $revisionId): ?object
    {
        return new \stdClass();
    }

    public function findRevisions(string $className, $id): array
    {
        return [
            new \stdClass(),
        ];
    }

    public function diff(string $className, $id, $oldRevisionId, $newRevisionId): array
    {
        return [];
    }
}
