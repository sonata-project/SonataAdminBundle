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

namespace SensioLabs\AdminBundle\Util\ORM;

use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;

/**
 * @internal
 */
final class SmartPaginatorFactory
{
    /**
     * @param array<string, mixed> $hints
     *
     * @phpstan-template T of object
     * @phpstan-param ProxyQueryInterface<T> $proxyQuery
     * @phpstan-return Paginator<T>
     */
    public static function create(ProxyQueryInterface $proxyQuery, array $hints = []): Paginator
    {
        $queryBuilder = $proxyQuery->getQueryBuilder();

        $rootEntity = current($queryBuilder->getRootEntities());

        if (false === $rootEntity) {
            throw new \RuntimeException('There are not root entities defined in the query.');
        }

        $identifierFieldNames = $queryBuilder
            ->getEntityManager()
            ->getClassMetadata($rootEntity)
            ->getIdentifierFieldNames();

        $hasSingleIdentifierName = 1 === \count($identifierFieldNames);
        $hasJoins = \count($queryBuilder->getDQLPart('join')) > 0;

        $query = $proxyQuery->getDoctrineQuery();

        if (!$hasJoins) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        foreach ($hints as $name => $value) {
            $query->setHint($name, $value);
        }

        // Paginator with fetchJoinCollection doesn't work with composite primary keys
        // https://github.com/doctrine/orm/issues/2910
        // To stay safe fetch join only when we have single primary key and joins
        $paginator = new Paginator($query, $hasSingleIdentifierName && $hasJoins);

        // it is only safe to disable output walkers for really simple queries
        if (self::canDisableOutPutWalkers($proxyQuery)) {
            $paginator->setUseOutputWalkers(false);
        }

        return $paginator;
    }

    /**
     * @see https://github.com/doctrine/orm/issues/8278#issue-705517756
     *
     * @param ProxyQueryInterface<object> $proxyQuery
     */
    private static function canDisableOutPutWalkers(ProxyQueryInterface $proxyQuery): bool
    {
        $queryBuilder = $proxyQuery->getQueryBuilder();

        // does not support queries using HAVING
        if (null !== $queryBuilder->getDQLPart('having')) {
            return false;
        }

        $fromParts = $queryBuilder->getDQLPart('from');

        // does not support queries using multiple entities in FROM
        if (1 !== \count($fromParts)) {
            return false;
        }

        $fromPart = current($fromParts);

        $classMetadata = $queryBuilder
            ->getEntityManager()
            ->getClassMetadata($fromPart->getFrom());

        $identifierFieldNames = $classMetadata->getIdentifierFieldNames();

        // does not support entities using a composite identifier
        if (1 !== \count($identifierFieldNames)) {
            return false;
        }

        $identifierName = current($identifierFieldNames);

        // does not support entities using a foreign key as identifier
        if ($classMetadata->hasAssociation($identifierName)) {
            return false;
        }

        // does not support queries using a field from a toMany relation in the ORDER BY clause
        if (self::hasOrderByWithToManyAssociation($proxyQuery)) {
            return false;
        }

        return true;
    }

    /**
     * @param ProxyQueryInterface<object> $proxyQuery
     */
    private static function hasOrderByWithToManyAssociation(ProxyQueryInterface $proxyQuery): bool
    {
        $queryBuilder = $proxyQuery->getQueryBuilder();

        $joinParts = $queryBuilder->getDQLPart('join');

        if (0 === \count($joinParts)) {
            return false;
        }

        $sortBy = $proxyQuery->getSortBy();
        $orderByParts = $queryBuilder->getDQLPart('orderBy');

        if (null === $sortBy && 0 === \count($orderByParts)) {
            return false;
        }

        $joinAliases = [];

        foreach ($joinParts as $joinPart) {
            foreach ($joinPart as $join) {
                $joinAliases[] = $join->getAlias();
            }
        }

        if (null !== $sortBy) {
            foreach ($joinAliases as $joinAlias) {
                if (str_starts_with($sortBy, $joinAlias.'.')) {
                    return true;
                }
            }
        }

        foreach ($orderByParts as $orderByPart) {
            foreach ($orderByPart->getParts() as $part) {
                foreach ($joinAliases as $joinAlias) {
                    if (str_starts_with((string) $part, $joinAlias.'.')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
