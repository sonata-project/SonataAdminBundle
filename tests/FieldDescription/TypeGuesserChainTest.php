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
use Sonata\AdminBundle\Guesser\TypeGuesserInterface as DeprecatedTypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class TypeGuesserChainTest extends TestCase
{
    public function testConstructorWithException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TypeGuesserChain([new \stdClass()]);
    }

    public function testGuess(): void
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
        static::assertSame($typeGuess2, $typeGuesserChain->guess($fieldDescription));

        $typeGuess4 = new TypeGuess('foo4', [], Guess::LOW_CONFIDENCE);
        $guesser4 = $this->createStub(TypeGuesserInterface::class);
        $guesser4
                ->method('guess')
                ->willReturn($typeGuess4);

        $typeGuesserChain = new TypeGuesserChain([$guesser4, $typeGuesserChain]);
        static::assertSame($typeGuess2, $typeGuesserChain->guess($fieldDescription));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGuessType(): void
    {
        $typeGuess1 = new TypeGuess('foo1', [], Guess::MEDIUM_CONFIDENCE);
        $guesser1 = $this->createStub(DeprecatedTypeGuesserInterface::class);
        $guesser1
            ->method('guessType')
            ->willReturn($typeGuess1);

        $typeGuess2 = new TypeGuess('foo2', [], Guess::HIGH_CONFIDENCE);
        $guesser2 = $this->createStub(DeprecatedTypeGuesserInterface::class);
        $guesser2
            ->method('guessType')
            ->willReturn($typeGuess2);

        $typeGuess3 = new TypeGuess('foo3', [], Guess::LOW_CONFIDENCE);
        $guesser3 = $this->createStub(DeprecatedTypeGuesserInterface::class);
        $guesser3
            ->method('guessType')
            ->willReturn($typeGuess3);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $class = \stdClass::class;
        $property = 'firstName';

        $typeGuesserChain = new TypeGuesserChain([$guesser1, $guesser2, $guesser3]);
        static::assertSame($typeGuess2, $typeGuesserChain->guessType($class, $property, $modelManager));

        $typeGuess4 = new TypeGuess('foo4', [], Guess::LOW_CONFIDENCE);
        $guesser4 = $this->createStub(DeprecatedTypeGuesserInterface::class);
        $guesser4
            ->method('guessType')
            ->willReturn($typeGuess4);

        $typeGuesserChain = new TypeGuesserChain([$guesser4, $typeGuesserChain]);
        static::assertSame($typeGuess2, $typeGuesserChain->guessType($class, $property, $modelManager));
    }
}
