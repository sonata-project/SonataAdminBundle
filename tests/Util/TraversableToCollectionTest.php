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
     * @param \Traversable<mixed>|array<mixed> $value
     *
     * @dataProvider provideTraversableValues
     */
    public function testTransform(int $expectedCount, $value): void
    {
        $collection = TraversableToCollection::transform($value);

        self::assertInstanceOf(Collection::class, $collection);
        self::assertCount($expectedCount, $collection);
    }

    /**
     * @phpstan-return iterable<array-key, array{int, \Traversable<mixed>|array<mixed>}>
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

    /**
     * @param mixed $value
     *
     * @dataProvider provideInvalidValues
     */
    public function testFailedTransform(string $invalidType, $value): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 1 passed to "Sonata\AdminBundle\Util\TraversableToCollection::transform()" must be of type "Traversable" or "array", %s given.',
            $invalidType
        ));

        // @phpstan-ignore-next-line
        TraversableToCollection::transform($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string, mixed}>
     */
    public function provideInvalidValues(): iterable
    {
        yield ['"NULL"', null];
        yield ['"integer"', 0];
        yield ['"integer"', 1];
        yield ['"string"', 'a'];
        yield ['instance of "stdClass"', new \stdClass()];
    }
}
