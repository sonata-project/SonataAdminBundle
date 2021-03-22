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

namespace Sonata\AdminBundle\Tests\FieldDescription;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserChain;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class TypeGuesserChainTest extends TestCase
{
    public function testConstructorWithException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @psalm-suppress InvalidArgument */
        // @phpstan-ignore-next-line
        new TypeGuesserChain([new \stdClass()]);
    }

    public function testGuessType(): void
    {
        $typeGuess1 = new TypeGuess('foo1', [], Guess::MEDIUM_CONFIDENCE);
        $guesser1 = $this->createStub(TypeGuesserInterface::class);
        $guesser1
                ->method('guess')
                ->willReturn($typeGuess1);

        $typeGuess2 = new TypeGuess('foo2', [], Guess::HIGH_CONFIDENCE);
        $guesser2 = $this->createStub(TypeGuesserInterface::class);
        $guesser2
                ->method('guess')
                ->willReturn($typeGuess2);

        $typeGuess3 = new TypeGuess('foo3', [], Guess::LOW_CONFIDENCE);
        $guesser3 = $this->createStub(TypeGuesserInterface::class);
        $guesser3
                ->method('guess')
                ->willReturn($typeGuess3);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $typeGuesserChain = new TypeGuesserChain([$guesser1, $guesser2, $guesser3]);
        $this->assertSame($typeGuess2, $typeGuesserChain->guess($fieldDescription));

        $typeGuess4 = new TypeGuess('foo4', [], Guess::LOW_CONFIDENCE);
        $guesser4 = $this->createStub(TypeGuesserInterface::class);
        $guesser4
                ->method('guess')
                ->willReturn($typeGuess4);

        $typeGuesserChain = new TypeGuesserChain([$guesser4, $typeGuesserChain]);
        $this->assertSame($typeGuess2, $typeGuesserChain->guess($fieldDescription));
    }
}
