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

namespace Sonata\AdminBundle\Tests\App\Audit;

use Sonata\AdminBundle\Model\AuditReaderInterface;
use Sonata\AdminBundle\Model\Revision;
use Sonata\AdminBundle\Tests\App\Model\Bar;
use Sonata\AdminBundle\Tests\App\Model\BarRepository;

/**
 * Pretends that every bar has the revisions 1 and 2, both returning the current object. It is only
 * registered as audit reader in the "role_security" environment.
 *
 * @phpstan-implements AuditReaderInterface<Bar>
 */
final class BarAuditReader implements AuditReaderInterface
{
    public function __construct(
        private BarRepository $repository,
    ) {
    }

    public function find(string $className, $id, $revisionId): ?object
    {
        return $this->repository->byId((string) $id);
    }

    public function findRevisionHistory(string $className, int $limit = 20, int $offset = 0): array
    {
        return $this->createRevisions();
    }

    public function findRevision(string $className, $revisionId): ?Revision
    {
        return $this->createRevisions()[(int) $revisionId] ?? null;
    }

    public function findRevisions(string $className, $id): array
    {
        return $this->createRevisions();
    }

    public function diff(string $className, $id, $oldRevisionId, $newRevisionId): array
    {
        return [];
    }

    /**
     * @return array<int, Revision>
     */
    private function createRevisions(): array
    {
        return [
            1 => new Revision(1, new \DateTimeImmutable('2026-01-01'), 'admin'),
            2 => new Revision(2, new \DateTimeImmutable('2026-01-02'), 'admin'),
        ];
    }
}
