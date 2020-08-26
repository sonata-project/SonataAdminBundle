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
     * @template T of object
     *
     * @param mixed $id
     * @param mixed $revisionId
     *
     * @phpstan-param class-string<T> $className
     * @phpstan-return T|null
     */
    public function find(string $className, $id, $revisionId): ?object;

    /**
     * @return object[]
     *
     * @phpstan-param class-string $className
     */
    public function findRevisionHistory(string $className, int $limit = 20, int $offset = 0): array;

    /**
     * @param mixed $revisionId
     *
     * @phpstan-param class-string $className
     */
    public function findRevision(string $className, $revisionId): ?object;

    /**
     * @param mixed $id
     *
     * @return object[]
     *
     * @phpstan-param class-string $className
     */
    public function findRevisions(string $className, $id): array;

    /**
     * @param mixed $id
     * @param mixed $oldRevisionId
     * @param mixed $newRevisionId
     *
     * @return array<string, array{old: mixed, new: mixed, same: mixed}>
     *
     * @phpstan-param class-string $className
     */
    public function diff(string $className, $id, $oldRevisionId, $newRevisionId): array;
}
