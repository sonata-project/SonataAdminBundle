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

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Datagrid\ORM\Pager;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Entity\ORM\User;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Entity\ORM\UserBrowser;
use SensioLabs\AdminBundle\Tests\Fixtures\ORM\TestEntityManagerFactory;

final class PagerTest extends TestCase
{
    public function testGetCurrentPageResults(): void
    {
        $iterator = new \ArrayIterator([new \stdClass()]);

        $paginator = $this->createMock(Paginator::class);
        $paginator->expects(static::once())->method('getIterator')->willReturn($iterator);

        $pq = $this->createMock(ProxyQueryInterface::class);
        $pq->method('execute')->willReturn($paginator);

        $pager = new Pager();
        $pager->setQuery($pq);

        static::assertSame($iterator, $pager->getCurrentPageResults());
    }

    /**
     * @phpstan-return iterable<array-key, array{class-string}>
     */
    public static function provideCountResultsCases(): iterable
    {
        yield [User::class];
        // single identifier
        yield [UserBrowser::class];
    }

    /**
     * @phpstan-param class-string $className
     */
    #[DataProvider('provideCountResultsCases')]
    public function testCountResults(string $className): void
    {
        $em = TestEntityManagerFactory::create();
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema([
            $em->getClassMetadata($className),
        ]);

        $qb = $em->createQueryBuilder()->select('e')->from($className, 'e');
        $pq = new ProxyQuery($qb);

        $pager = new Pager();
        $pager->setQuery($pq);
        $pager->init();

        static::assertSame(0, $pager->countResults());
    }
}
