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

namespace Sonata\AdminBundle\Tests\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\DataTransformer\FilterDataTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

final class FilterDataTransformerTest extends TestCase
{
    public function testReverseTransformThrowsExceptionIfValueIsNotArray(): void
    {
        $transformer = new FilterDataTransformer();

        $this->expectException(UnexpectedTypeException::class);

        $transformer->reverseTransform(1);
    }

    /**
     * @dataProvider getDataValues
     * @phpstan-param array{type: int, value: mixed} $value
     */
    public function testReverseTransform(array $value): void
    {
        $transformer = new FilterDataTransformer();

        $filterData = $transformer->reverseTransform($value);

        $this->assertSame($value['type'], $filterData->getType());
        $this->assertSame($value['value'], $filterData->getValue());
    }

    public function testTransformReturnsNullOnNull(): void
    {
        $transformer = new FilterDataTransformer();

        $this->assertNull($transformer->transform(null));
    }

    /**
     * @dataProvider getDataValues
     * @phpstan-param array{type: int, value: mixed} $value
     */
    public function testTransform(array $value): void
    {
        $transformer = new FilterDataTransformer();

        $this->assertSame($value, $transformer->transform(FilterData::fromArray($value)));
    }

    /**
     * @phpstan-return iterable<array<array{type: int, value: mixed}>>
     */
    public function getDataValues(): iterable
    {
        yield [[
            'type' => 1,
            'value' => 'value',
        ]];

        yield [[
            'type' => 1,
            'value' => null,
        ]];
    }
}
