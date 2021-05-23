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

namespace Sonata\AdminBundle\Tests\App\Model;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Model\LockInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * Class ModelManager.
 *
 * @phpstan-implements LockInterface<Foo>
 * @phpstan-implements ModelManagerInterface<Foo>
 */
class ModelManager implements ModelManagerInterface, LockInterface
{
    /**
     * @var FooRepository
     */
    private $repository;

    public function __construct(FooRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(object $object): void
    {
    }

    public function update(object $object): void
    {
    }

    public function delete(object $object): void
    {
    }

    public function findBy(string $class, array $criteria = []): array
    {
        return [];
    }

    public function findOneBy(string $class, array $criteria = []): ?object
    {
        return null;
    }

    public function find(string $class, $id): ?object
    {
        return $this->repository->byId($id);
    }

    public function batchDelete(string $class, ProxyQueryInterface $query): void
    {
    }

    public function createQuery(string $class, string $alias = 'o'): ProxyQueryInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getIdentifierValues(object $model): array
    {
        return [];
    }

    public function getIdentifierFieldNames(string $class): array
    {
        return [];
    }

    public function getNormalizedIdentifier(object $model): string
    {
        return $model->getId();
    }

    public function getUrlSafeIdentifier(object $model): string
    {
        return $this->getNormalizedIdentifier($model);
    }

    public function reverseTransform(object $object, array $array = []): void
    {
    }

    public function supportsQuery(object $query): bool
    {
        return true;
    }

    /**
     * @return Foo[]
     */
    public function executeQuery(object $query): array
    {
        return [];
    }

    public function getExportFields(string $class): array
    {
        return [];
    }

    public function addIdentifiersToQuery(string $class, ProxyQueryInterface $query, array $idx): void
    {
    }

    public function getLockVersion(object $object)
    {
        return null;
    }

    public function lock(object $object, ?int $expectedVersion): void
    {
    }
}
