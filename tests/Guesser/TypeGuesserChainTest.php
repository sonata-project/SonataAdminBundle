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

namespace Sonata\AdminBundle\Tests\Guesser;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserChain;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class TypeGuesserChainTest extends TestCase
{
    use ExpectDeprecationTrait;

    public function testConstructorWithException(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        new TypeGuesserChain([new \stdClass()]);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGuessType(): void
    {
        $typeGuess1 = new TypeGuess('foo1', [], Guess::MEDIUM_CONFIDENCE);
        $guesser1 = $this->getMockForAbstractClass(TypeGuesserInterface::class);
        $guesser1
                ->method('guessType')
                ->willReturn($typeGuess1);

        $typeGuess2 = new TypeGuess('foo2', [], Guess::HIGH_CONFIDENCE);
        $guesser2 = $this->getMockForAbstractClass(TypeGuesserInterface::class);
        $guesser2
                ->method('guessType')
                ->willReturn($typeGuess2);

        $typeGuess3 = new TypeGuess('foo3', [], Guess::LOW_CONFIDENCE);
        $guesser3 = $this->getMockForAbstractClass(TypeGuesserInterface::class);
        $guesser3
                ->method('guessType')
                ->willReturn($typeGuess3);

        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);

        $class = \stdClass::class;
        $property = 'firstName';

        $typeGuesserChain = new TypeGuesserChain([$guesser1, $guesser2, $guesser3]);

        $this->expectDeprecation('Method "Sonata\AdminBundle\Guesser\TypeGuesserChain::guessType()" is deprecated since sonata-project/admin-bundle 3.x and will be removed in version 4.0. Use "Sonata\AdminBundle\Guesser\TypeGuesserChain::guessTypeForFieldDescription()" instead.');

        $this->assertSame($typeGuess2, $typeGuesserChain->guessType($class, $property, $modelManager));

        $typeGuess4 = new TypeGuess('foo4', [], Guess::LOW_CONFIDENCE);
        $guesser4 = $this->getMockForAbstractClass(TypeGuesserInterface::class);
        $guesser4
                ->method('guessType')
                ->willReturn($typeGuess4);

        $typeGuesserChain = new TypeGuesserChain([$guesser4, $typeGuesserChain]);
        $this->assertSame($typeGuess2, $typeGuesserChain->guessType($class, $property, $modelManager));
    }

    public function testGuessTypeForFieldDescription(): void
    {
        $typeGuess1 = new TypeGuess('foo1', [], Guess::MEDIUM_CONFIDENCE);
        // NEXT_MAJOR: Change this mock with a stub.
        $guesser1 = $this->getMockBuilder(TypeGuesserInterface::class)
            ->addMethods(['guessTypeForFieldDescription'])
            ->getMockForAbstractClass();
        $guesser1
                ->method('guessTypeForFieldDescription')
                ->willReturn($typeGuess1);

        $typeGuess2 = new TypeGuess('foo2', [], Guess::HIGH_CONFIDENCE);
        // NEXT_MAJOR: Change this mock with a stub.
        $guesser2 = $this->getMockBuilder(TypeGuesserInterface::class)
            ->addMethods(['guessTypeForFieldDescription'])
            ->getMockForAbstractClass();
        $guesser2
                ->method('guessTypeForFieldDescription')
                ->willReturn($typeGuess2);

        $typeGuess3 = new TypeGuess('foo3', [], Guess::LOW_CONFIDENCE);
        // NEXT_MAJOR: Change this mock with a stub.
        $guesser3 = $this->getMockBuilder(TypeGuesserInterface::class)
            ->addMethods(['guessTypeForFieldDescription'])
            ->getMockForAbstractClass();
        $guesser3
                ->method('guessTypeForFieldDescription')
                ->willReturn($typeGuess3);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $typeGuesserChain = new TypeGuesserChain([$guesser1, $guesser2, $guesser3]);
        $this->assertSame($typeGuess2, $typeGuesserChain->guessTypeForFieldDescription($fieldDescription));

        $typeGuess4 = new TypeGuess('foo4', [], Guess::LOW_CONFIDENCE);
        // NEXT_MAJOR: Change this mock with a stub.
        $guesser4 = $this->getMockBuilder(TypeGuesserInterface::class)
            ->addMethods(['guessTypeForFieldDescription'])
            ->getMockForAbstractClass();
        $guesser4
                ->method('guessTypeForFieldDescription')
                ->willReturn($typeGuess4);

        $typeGuesserChain = new TypeGuesserChain([$guesser4, $typeGuesserChain]);
        $this->assertSame($typeGuess2, $typeGuesserChain->guessTypeForFieldDescription($fieldDescription));
    }
}
