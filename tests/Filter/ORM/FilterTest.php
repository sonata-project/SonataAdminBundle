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
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\Attributes\DataProvider;
use SensioLabs\AdminBundle\Filter\Model\FilterData;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Filter\ORM\Filter;
use SensioLabs\AdminBundle\Filter\ORM\StringFilter;

final class FilterTest extends FilterTestCase
{
    /**
     * @phpstan-param array<array{string|null, array<string, mixed>, string, FilterData}> $filterOptionsCollection
     */
    #[DataProvider('provideOrExpressionCases')]
    public function testOrExpression(string $expected, array $filterOptionsCollection = []): void
    {
        $entityManager = static::createStub(EntityManagerInterface::class);
        $entityManager->method('getExpressionBuilder')->willReturn(new Expr());
        $queryBuilder = new TestQueryBuilder($entityManager);

        $queryBuilder->select('e')->from(MyEntity::class, 'e');

        // Some custom conditions set previous to the filters.
        $queryBuilder
            ->where($queryBuilder->expr()->eq(1, 2))
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq(':parameter_1', 4),
                    $queryBuilder->expr()->eq(5, 6)
                )
            )
            ->setParameter('parameter_1', 3);

        static::assertSame('SELECT e FROM Sonata\DoctrineORMAdminBundle\Tests\Filter\MyEntity e WHERE 1 = 2 AND (:parameter_1 = 4 OR 5 = 6)', $queryBuilder->getDQL());

        $proxyQuery = new ProxyQuery($queryBuilder);

        foreach ($filterOptionsCollection as [$orGroup, $defaultOptions, $field, $filterData]) {
            $filter = new StringFilter();
            $filter->initialize($field, $defaultOptions);
            $filter->setCondition(Filter::CONDITION_OR);
            if (null !== $orGroup) {
                $filter->setOption('or_group', $orGroup);
            }

            $filter->apply($proxyQuery, $filterData);
        }

        // More custom conditions set after the filters.
        $queryBuilder->andWhere($queryBuilder->expr()->eq(7, 8));

        static::assertSame($expected, $queryBuilder->getDQL());
    }

    /**
     * @phpstan-return iterable<array-key, array{string, array<array{string|null, array<string, mixed>, string, FilterData}>}>
     */
    public static function provideOrExpressionCases(): iterable
    {
        yield 'Default behavior' => [
            'SELECT e FROM Sonata\DoctrineORMAdminBundle\Tests\Filter\MyEntity e WHERE 1 = 2 AND (:parameter_1 = 4 OR 5 = 6)'
            .' AND e.project LIKE :project_0 AND e.version LIKE :version_1 AND 7 = 8',
            [
                [
                    null,
                    [
                        'field_name' => 'project',
                        'allow_empty' => false,
                    ],
                    'project',
                    FilterData::fromArray([
                        'value' => 'sonata-project',
                    ]),
                ],
                [
                    null,
                    [
                        'field_name' => 'version',
                        'allow_empty' => false,
                    ],
                    'version',
                    FilterData::fromArray([
                        'value' => '3.x',
                    ]),
                ],
            ],
        ];
    }
}

final class MyEntity
{
}
