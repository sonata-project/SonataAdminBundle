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

namespace SensioLabs\AdminBundle\Tests\Datagrid\ORM;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\DoctrineType\UuidType;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Entity\DoubleNameEntity;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Query\FooWalker;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\TestEntityManagerFactory;

final class ProxyQueryTest extends TestCase
{
    private EntityManagerInterface $em;

    public static function setUpBeforeClass(): void
    {
        if (!Type::hasType(UuidType::NAME)) {
            Type::addType(UuidType::NAME, UuidType::class);
        }
    }

    protected function setUp(): void
    {
        $this->em = TestEntityManagerFactory::create();

        $schemaTool = new SchemaTool($this->em);
        $classes = [
            $this->em->getClassMetadata(DoubleNameEntity::class),
        ];

        try {
            $schemaTool->dropSchema($classes);
        } catch (\Exception) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (\Exception) {
        }
    }

    protected function tearDown(): void
    {
        unset($this->em);
    }

    public function testSetHint(): void
    {
        $entity1 = new DoubleNameEntity(1, 'Foo', null);
        $entity2 = new DoubleNameEntity(2, 'Bar', null);

        $this->em->persist($entity1);
        $this->em->persist($entity2);
        $this->em->flush();

        $qb = $this->em->createQueryBuilder()->select('o')->from(DoubleNameEntity::class, 'o');

        $pq = new ProxyQuery($qb);
        $pq->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            FooWalker::class
        );
        $pq->setHint('hint', 'value');

        $result = iterator_to_array($pq->execute());

        static::assertSame([$entity2, $entity1], $result);
    }

    public function testSortOrderValidatesItsInput(): void
    {
        $query = new ProxyQuery($this->em->createQueryBuilder());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '"ASC,injection" is not a valid sort order, valid values are "ASC, DESC"'
        );
        $query->setSortOrder('ASC,injection');
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public static function provideItAllowsSortOrdersWithStrangeCaseCases(): iterable
    {
        yield ['ASC'];
        yield ['DESC'];
        yield ['asc'];
        yield ['desc'];
        yield ['AsC'];
        yield ['deSc'];
    }

    #[DataProvider('provideItAllowsSortOrdersWithStrangeCaseCases')]
    public function testItAllowsSortOrdersWithStrangeCase(string $validValue): void
    {
        $query = new ProxyQuery($this->em->createQueryBuilder());
        $query->setSortOrder($validValue);
        static::assertSame($validValue, $query->getSortOrder());
    }

    public function testExecuteWithOrderBy(): void
    {
        $entity1 = new DoubleNameEntity(1, 'Foo', 'Bar');
        $entity2 = new DoubleNameEntity(2, 'Bar', 'Bar');
        $entity3 = new DoubleNameEntity(3, 'Bar', 'Foo');

        $this->em->persist($entity1);
        $this->em->persist($entity2);
        $this->em->persist($entity3);
        $this->em->flush();

        $query = new ProxyQuery(
            $this->em->createQueryBuilder()->select('o')->from(DoubleNameEntity::class, 'o')
        );
        $query->setSortBy([], ['fieldName' => 'name2'])->setSortOrder('ASC');

        static::assertSame([$entity1, $entity2, $entity3], iterator_to_array($query->execute()));

        $query2 = new ProxyQuery(
            $this->em->createQueryBuilder()->select('o')->from(DoubleNameEntity::class, 'o')->addOrderBy('o.name')
        );

        static::assertSame([$entity2, $entity3, $entity1], iterator_to_array($query2->execute()));

        $query2->setSortBy([], ['fieldName' => 'name2'])->setSortOrder('ASC');

        static::assertSame([$entity2, $entity1, $entity3], iterator_to_array($query2->execute()));

        $query2->setSortBy([], []);

        static::assertSame([$entity2, $entity3, $entity1], iterator_to_array($query2->execute()));
    }
}
