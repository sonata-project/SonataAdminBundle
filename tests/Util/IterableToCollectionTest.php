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
use Sonata\AdminBundle\Util\IterableToCollection;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class IterableToCollectionTest extends TestCase
{
    /**
     * @param \Traversable<mixed>|array<mixed> $value
     *
     * @dataProvider provideTraversableValues
     */
    public function testTransform(int $expectedCount, $value): void
    {
        $collection = IterableToCollection::transform($value);

        static::assertInstanceOf(Collection::class, $collection);
        static::assertCount($expectedCount, $collection);
    }

    /**
     * @phpstan-return iterable<array-key, array{int, iterable<mixed, mixed>}>
     */
    public function provideTraversableValues(): iterable
    {
        yield [0, []];
        yield [1, [null]];
        yield [1, [0]];
        yield [1, ['a']];
        yield [3, ['a', 'b', 'other_offset' => 'c']];
        yield [2, (static function (): \Generator { yield from ['d', 'e']; })()];
        yield [4, new ArrayCollection(['f', 'g', 'h', 'i'])];
    }
}
