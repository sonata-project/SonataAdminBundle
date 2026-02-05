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

namespace SensioLabs\AdminBundle\Tests\Util\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Address;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Author;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\Item;
use SensioLabs\AdminBundle\Tests\App\ORM\Entity\ProductAttribute;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\TestEntityManagerFactory;
use SensioLabs\AdminBundle\Util\ORM\SmartPaginatorFactory;

final class SmartPaginatorFactoryTest extends TestCase
{
    #[DataProvider('provideFetchJoinedCollectionCases')]
    public function testFetchJoinedCollection(QueryBuilder $queryBuilder, bool $expected): void
    {
        /** @var ProxyQueryInterface<object>&MockObject $proxyQuery */
        $proxyQuery = static::createStub(ProxyQueryInterface::class);
        $proxyQuery
            ->method('getQueryBuilder')
            ->willReturn($queryBuilder);

        $proxyQuery
            ->method('getDoctrineQuery')
            ->willReturn($queryBuilder->getQuery());

        $paginator = SmartPaginatorFactory::create($proxyQuery);

        static::assertSame($expected, $paginator->getFetchJoinCollection());
    }

    /**
     * @phpstan-return iterable<array-key, array{QueryBuilder, bool}>
     */
    public static function provideFetchJoinedCollectionCases(): iterable
    {
        yield 'Without joins' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author'),
            false,
        ];

        yield 'With joins and simple identifier' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author')
                ->leftJoin('author.books', 'book'),
            true,
        ];

        yield 'With joins and composite identifier' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Item::class, 'item')
                ->leftJoin('item.product', 'product'),
            false,
        ];
    }

    #[DataProvider('provideUseOutputWalkerCases')]
    public function testUseOutputWalker(QueryBuilder $queryBuilder, ?bool $expected, ?string $sortBy = null): void
    {
        /** @var ProxyQueryInterface<object>&MockObject $proxyQuery */
        $proxyQuery = static::createStub(ProxyQueryInterface::class);
        $proxyQuery
            ->method('getQueryBuilder')
            ->willReturn($queryBuilder);

        $proxyQuery
            ->method('getDoctrineQuery')
            ->willReturn($queryBuilder->getQuery());

        if (null !== $sortBy) {
            $proxyQuery->method('getSortBy')->willReturn($sortBy);
        }

        $paginator = SmartPaginatorFactory::create($proxyQuery);

        static::assertSame($expected, $paginator->getUseOutputWalkers());
    }

    /**
     * @phpstan-return iterable<array-key, array{0: QueryBuilder, 1: bool|null, 2?: string}>
     */
    public static function provideUseOutputWalkerCases(): iterable
    {
        yield 'Simple query without joins' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author'),
            false,
        ];

        yield 'Simple query with having' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author')
                ->groupBy('author.name')
                ->having('COUNT(author.id) > 0'),
            null,
        ];

        yield 'With joins and simple identifier' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author')
                ->leftJoin('author.books', 'book'),
            false,
        ];

        yield 'With joins and composite identifier' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Item::class, 'item')
                ->leftJoin('item.product', 'product'),
            null,
        ];

        yield 'With order by not from join field' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author')
                ->leftJoin('author.books', 'book')
                ->orderBy('author.name'),
            false,
        ];

        yield 'With order by not from join field using an alias contained in order by' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author')
                ->leftJoin('author.books', 'a')
                ->orderBy('author.name'),
            false,
        ];

        yield 'With order by from join field' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author')
                ->leftJoin('author.books', 'book')
                ->orderBy('book.title'),
            null,
        ];

        yield 'With order by from join field set on the ProxyQuery' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author')
                ->leftJoin('author.books', 'book'),
            null,
            'book.title',
        ];

        yield 'With foreign key as identifier' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(ProductAttribute::class, 'productAttribute'),
            null,
        ];

        yield 'With multiple FROM' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author')
                ->from(Address::class, 'address'),
            null,
        ];
    }

    #[DataProvider('provideCountWalkerDistinctCases')]
    public function testCountWalkerDistinct(QueryBuilder $queryBuilder, bool $hasHint, bool $expected): void
    {
        /** @var ProxyQueryInterface<object>&MockObject $proxyQuery */
        $proxyQuery = static::createStub(ProxyQueryInterface::class);
        $proxyQuery
            ->method('getQueryBuilder')
            ->willReturn($queryBuilder);

        $query = $queryBuilder->getQuery();

        $proxyQuery
            ->method('getDoctrineQuery')
            ->willReturn($query);

        SmartPaginatorFactory::create($proxyQuery);

        static::assertSame($hasHint, $query->hasHint(CountWalker::HINT_DISTINCT));
        static::assertSame($expected, $query->getHint(CountWalker::HINT_DISTINCT));
    }

    /**
     * @phpstan-return iterable<array-key, array{QueryBuilder, bool, bool}>
     */
    public static function provideCountWalkerDistinctCases(): iterable
    {
        yield 'Simple query without joins' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author'),
            true,
            false,
        ];

        yield 'With joins and simple identifier' => [
            TestEntityManagerFactory::create()
                ->createQueryBuilder()
                ->from(Author::class, 'author')
                ->leftJoin('author.books', 'book'),
            false,
            false,
        ];
    }
}
