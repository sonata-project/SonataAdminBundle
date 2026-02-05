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

use SensioLabs\AdminBundle\Filter\Model\FilterData;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;
use SensioLabs\AdminBundle\Filter\ORM\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

final class CallbackFilterTest extends FilterTestCase
{
    public function testRenderSettings(): void
    {
        $filter = new CallbackFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);
        $options = $filter->getRenderSettings()[1];

        static::assertSame(HiddenType::class, $options['operator_type']);
        static::assertSame([], $options['operator_options']);
    }

    public function testFilterClosure(): void
    {
        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => static function (ProxyQueryInterface $query, string $alias, string $field, FilterData $data): bool {
                $query->getQueryBuilder()->andWhere(\sprintf('CUSTOM QUERY %s.%s', $alias, $field));
                $query->getQueryBuilder()->setParameter('value', $data->getValue());

                return true;
            },
        ]);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => 'myValue']));

        self::assertSameQuery(['WHERE CUSTOM QUERY alias.field'], $proxyQuery);
        self::assertSameQueryParameters(['value' => 'myValue'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterMethod(): void
    {
        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => $this->customCallback(...),
        ]);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => 'myValue']));

        self::assertSameQuery(['WHERE CUSTOM QUERY alias.field'], $proxyQuery);
        self::assertSameQueryParameters(['value' => 'myValue'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    /**
     * @param ProxyQueryInterface<object> $query
     */
    public function customCallback(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): bool
    {
        $query->getQueryBuilder()->andWhere(\sprintf('CUSTOM QUERY %s.%s', $alias, $field));
        $query->getQueryBuilder()->setParameter('value', $data->getValue());

        return true;
    }

    public function testFilterException(): void
    {
        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', []);

        $this->expectException(\RuntimeException::class);
        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => 'myValue']));
    }

    public function testApplyMethod(): void
    {
        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter = new CallbackFilter();
        $filter->initialize('field_name_test', [
            'callback' => static function (ProxyQueryInterface $query, string $alias, string $field, FilterData $data): bool {
                $query->getQueryBuilder()->andWhere(\sprintf('CUSTOM QUERY %s.%s', $alias, $field));
                $query->getQueryBuilder()->setParameter('value', $data->getValue());

                return true;
            },
            'field_name' => 'field_name_test',
        ]);

        $filter->apply($proxyQuery, FilterData::fromArray(['value' => 'myValue']));

        self::assertSameQuery(['WHERE CUSTOM QUERY o.field_name_test'], $proxyQuery);
        self::assertSameQueryParameters(['value' => 'myValue'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }
}
