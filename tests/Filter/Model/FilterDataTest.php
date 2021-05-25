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

namespace Sonata\AdminBundle\Tests\Filter\Model;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Filter\Model\FilterData;

final class FilterDataTest extends TestCase
{
    /**
     * @dataProvider getInvalidTypes
     *
     * @param mixed $type
     *
     * @psalm-suppress InvalidArgument
     */
    public function testTypeMustBeNumericOrNull($type): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The "type" parameter MUST be of type "integer" or "null", %s given.',
            \is_object($type) ? 'instance of "'.\get_class($type).'"' : '"'.\gettype($type).'"'
        ));

        // @phpstan-ignore-next-line
        FilterData::fromArray(['type' => $type]);
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function getInvalidTypes(): iterable
    {
        yield ['string'];
        yield [new \stdClass()];
        yield [[]];
        yield [42.0];
    }

    public function testEmptyArray(): void
    {
        $filterData = FilterData::fromArray([]);
        $this->assertFalse($filterData->hasValue());
        $this->assertNull($filterData->getType());
    }

    public function testHasValue(): void
    {
        $this->assertFalse(FilterData::fromArray([])->hasValue());
        $this->assertTrue(FilterData::fromArray(['value' => ''])->hasValue());
        $this->assertTrue(FilterData::fromArray(['value' => null])->hasValue());
    }

    /**
     * @dataProvider getTypes
     *
     * @param int|string|null $type
     *
     * @phpstan-param int|numeric-string|null $type
     */
    public function testGetType(?int $expected, $type): void
    {
        $this->assertSame($expected, FilterData::fromArray(['type' => $type])->getType());
    }

    /**
     * @phpstan-return iterable<array-key, array{int|null, int|numeric-string|null}>
     */
    public function getTypes(): iterable
    {
        yield 'nullable' => [null, null];
        yield 'int' => [3, 3];
        yield 'numeric string' => [3, '3'];
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value
     */
    public function testGetValue($value): void
    {
        $this->assertSame($value, FilterData::fromArray(['value' => $value])->getValue());
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function getValues(): iterable
    {
        yield [null];
        yield [new \stdClass()];
        yield [3];
        yield ['3'];
    }

    public function testSetValue(): void
    {
        $filterData = FilterData::fromArray(['type' => 1, 'value' => 'value']);
        $newFilterData = $filterData->changeValue('new_value');

        $this->assertSame(1, $newFilterData->getType());
        $this->assertSame('new_value', $newFilterData->getValue());
    }

    public function testGetValueThrowsExceptionIfValueNotPresent(): void
    {
        $filterData = FilterData::fromArray([]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The FilterData object does not have a value.');

        $filterData->getValue();
    }
}
