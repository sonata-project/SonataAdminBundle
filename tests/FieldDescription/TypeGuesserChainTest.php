<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\FieldDescription;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\FieldDescription\TypeGuesserChain;
use SensioLabs\AdminBundle\FieldDescription\TypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class TypeGuesserChainTest extends TestCase
{
    public function testConstructorWithException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // @phpstan-ignore-next-line
        new TypeGuesserChain([new \stdClass()]);
    }

    public function testGuess(): void
    {
        $typeGuess1 = new TypeGuess('foo1', [], Guess::MEDIUM_CONFIDENCE);
        $guesser1 = static::createStub(TypeGuesserInterface::class);
        $guesser1
                ->method('guess')
                ->willReturn($typeGuess1);

        $typeGuess2 = new TypeGuess('foo2', [], Guess::HIGH_CONFIDENCE);
        $guesser2 = static::createStub(TypeGuesserInterface::class);
        $guesser2
                ->method('guess')
                ->willReturn($typeGuess2);

        $typeGuess3 = new TypeGuess('foo3', [], Guess::LOW_CONFIDENCE);
        $guesser3 = static::createStub(TypeGuesserInterface::class);
        $guesser3
                ->method('guess')
                ->willReturn($typeGuess3);

        $fieldDescription = static::createStub(FieldDescriptionInterface::class);

        $typeGuesserChain = new TypeGuesserChain([$guesser1, $guesser2, $guesser3]);
        static::assertSame($typeGuess2, $typeGuesserChain->guess($fieldDescription));

        $typeGuess4 = new TypeGuess('foo4', [], Guess::LOW_CONFIDENCE);
        $guesser4 = static::createStub(TypeGuesserInterface::class);
        $guesser4
                ->method('guess')
                ->willReturn($typeGuess4);

        $typeGuesserChain = new TypeGuesserChain([$guesser4, $typeGuesserChain]);
        static::assertSame($typeGuess2, $typeGuesserChain->guess($fieldDescription));
    }
}
