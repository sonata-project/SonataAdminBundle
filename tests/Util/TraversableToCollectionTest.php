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

namespace Sonata\AdminBundle\Tests\Util;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Util\TraversableToCollection;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class TraversableToCollectionTest extends TestCase
{
    /**
     * @param iterable<mixed, mixed> $value
     *
     * @dataProvider provideTransformCases
     */
    public function testTransform(int $expectedCount, iterable $value): void
    {
        $collection = TraversableToCollection::transform($value);

        static::assertInstanceOf(Collection::class, $collection);
        static::assertCount($expectedCount, $collection);
    }

    /**
     * @phpstan-return iterable<array-key, array{int, iterable<mixed, mixed>}>
     */
    public function provideTransformCases(): iterable
    {
        yield [0, []];
        yield [1, [null]];
        yield [1, [0]];
        yield [1, ['a']];
        yield [3, ['a', 'b', 'other_offset' => 'c']];
        yield [2, (static function (): \Generator { yield from ['d', 'e']; })()];
        yield [4, new ArrayCollection(['f', 'g', 'h', 'i'])];
    }

    /**
     * @dataProvider provideFailedTransformCases
     */
    public function testFailedTransform(string $invalidType, mixed $value): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(\sprintf(
            'Argument 1 passed to "Sonata\AdminBundle\Util\TraversableToCollection::transform()" must be an iterable, %s given.',
            $invalidType
        ));

        // @phpstan-ignore-next-line
        TraversableToCollection::transform($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string, mixed}>
     */
    public function provideFailedTransformCases(): iterable
    {
        yield ['"NULL"', null];
        yield ['"integer"', 0];
        yield ['"integer"', 1];
        yield ['"string"', 'a'];
        yield ['"object"', new \stdClass()];
    }
}
