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
use Sonata\AdminBundle\Form\DataTransformer\BackedEnumTransformer;
use Sonata\AdminBundle\Tests\Fixtures\Enum\Suit;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

final class BackedEnumTransformerTest extends TestCase
{
    public function testReverseTransform(): void
    {
        $transformer = new BackedEnumTransformer(Suit::class);

        static::assertNull($transformer->reverseTransform(null));
        static::assertNull($transformer->reverseTransform(''));
        static::assertSame(Suit::Hearts, $transformer->reverseTransform(Suit::Hearts->value));
    }

    public function testReverseTransformNotValidValue(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Could not transform value "not_valid_value".');

        $transformer = new BackedEnumTransformer(Suit::class);
        $transformer->reverseTransform('not_valid_value');
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function testReverseTransformNotScalar(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Could not transform value: expecting an int or string, got "stdClass".');

        $transformer = new BackedEnumTransformer(Suit::class);
        // @phpstan-ignore-next-line
        $transformer->reverseTransform(new \stdClass());
    }

    public function testTransform(): void
    {
        $transformer = new BackedEnumTransformer(Suit::class);

        static::assertNull($transformer->transform(null));
        static::assertSame(Suit::Clubs->value, $transformer->transform(Suit::Clubs));
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function testTransformUnexpectedType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "BackedEnum", "stdClass" given');

        $transformer = new BackedEnumTransformer(Suit::class);
        // @phpstan-ignore-next-line
        $transformer->transform(new \stdClass());
    }
}
