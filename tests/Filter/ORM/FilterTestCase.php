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

namespace SensioLabs\AdminBundle\Tests\Filter\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;

abstract class FilterTestCase extends TestCase
{
    /**
     * @param string[]           $expected
     * @param ProxyQuery<object> $proxyQuery
     */
    final protected static function assertSameQuery(array $expected, ProxyQuery $proxyQuery): void
    {
        $queryBuilder = $proxyQuery->getQueryBuilder();
        if (!$queryBuilder instanceof TestQueryBuilder) {
            throw new \InvalidArgumentException('The query builder should be build with "createQueryBuilderStub()".');
        }

        static::assertSame($expected, $queryBuilder->query);
    }

    /**
     * @param mixed[]            $expected
     * @param ProxyQuery<object> $proxyQuery
     */
    final protected static function assertSameQueryParameters(array $expected, ProxyQuery $proxyQuery): void
    {
        $queryBuilder = $proxyQuery->getQueryBuilder();
        if (!$queryBuilder instanceof TestQueryBuilder) {
            throw new \InvalidArgumentException('The query builder should be build with "createQueryBuilderStub()".');
        }

        static::assertSame($expected, $queryBuilder->queryParameters);
    }

    final protected function createQueryBuilderStub(): TestQueryBuilder
    {
        $queryBuilder = static::createStub(TestQueryBuilder::class);

        $queryBuilder->method('getEntityManager')->willReturnCallback(
            fn (): EntityManagerInterface => $this->createEntityManagerStub()
        );

        $queryBuilder->method('setParameter')->willReturnCallback(
            static function (string $name, mixed $value) use ($queryBuilder): TestQueryBuilder {
                $queryBuilder->queryParameters[$name] = $value;

                return $queryBuilder;
            }
        );

        $queryBuilder->method('andWhere')->willReturnCallback(
            static function (mixed $query) use ($queryBuilder): TestQueryBuilder {
                $queryBuilder->query[] = \sprintf('WHERE %s', $query);

                return $queryBuilder;
            }
        );

        $queryBuilder->method('andHaving')->willReturnCallback(
            static function (mixed $query) use ($queryBuilder): TestQueryBuilder {
                $queryBuilder->query[] = \sprintf('HAVING %s', $query);

                return $queryBuilder;
            }
        );

        $queryBuilder->method('addGroupBy')->willReturnCallback(
            static function (string $groupBy) use ($queryBuilder): TestQueryBuilder {
                $queryBuilder->query[] = \sprintf('GROUP BY %s', $groupBy);

                return $queryBuilder;
            }
        );

        $queryBuilder->method('expr')->willReturnCallback(
            static fn (): Expr => new Expr()
        );

        $queryBuilder->method('getRootAliases')->willReturnCallback(
            static fn (): array => ['o']
        );

        $queryBuilder->method('getDQLPart')->willReturnCallback(
            static fn (): array => []
        );

        $queryBuilder->method('leftJoin')->willReturnCallback(
            static function (string $parameter, string $alias) use ($queryBuilder): TestQueryBuilder {
                $queryBuilder->query[] = \sprintf('LEFT JOIN %s AS %s', $parameter, $alias);

                return $queryBuilder;
            }
        );

        return $queryBuilder;
    }

    private function createEntityManagerStub(): EntityManagerInterface
    {
        $classMetadata = static::createStub(ClassMetadata::class);
        $entityManager = static::createStub(EntityManagerInterface::class);

        $classMetadata->method('getIdentifierValues')->willReturnCallback(
            static fn (mixed $value): array => ['id' => $value]
        );

        $entityManager->method('getClassMetadata')->willReturnCallback(
            static fn (string $class): ClassMetadata => $classMetadata
        );

        return $entityManager;
    }
}

class TestQueryBuilder extends QueryBuilder
{
    /** @var string[] */
    public $query = [];

    /** @var mixed[] */
    public $queryParameters = [];
}
