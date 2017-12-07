<?php

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
use Sonata\AdminBundle\Guesser\TypeGuesserChain;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * TypeGuesserChain Test.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class TypeGuesserChainTest extends TestCase
{
    public function testConstructorWithException()
    {
        $this->expectException(UnexpectedTypeException::class);

        $typeGuesserChain = new TypeGuesserChain([new \stdClass()]);
    }

    public function testGuessType()
    {
        $typeGuess1 = new TypeGuess('foo1', [], Guess::MEDIUM_CONFIDENCE);
        $guesser1 = $this->getMockForAbstractClass(TypeGuesserInterface::class);
        $guesser1->expects($this->any())
                ->method('guessType')
                ->will($this->returnValue($typeGuess1));

        $typeGuess2 = new TypeGuess('foo2', [], Guess::HIGH_CONFIDENCE);
        $guesser2 = $this->getMockForAbstractClass(TypeGuesserInterface::class);
        $guesser2->expects($this->any())
                ->method('guessType')
                ->will($this->returnValue($typeGuess2));

        $typeGuess3 = new TypeGuess('foo3', [], Guess::LOW_CONFIDENCE);
        $guesser3 = $this->getMockForAbstractClass(TypeGuesserInterface::class);
        $guesser3->expects($this->any())
                ->method('guessType')
                ->will($this->returnValue($typeGuess3));

        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);

        $class = \stdClass::class;
        $property = 'firstName';

        $typeGuesserChain = new TypeGuesserChain([$guesser1, $guesser2, $guesser3]);
        $this->assertSame($typeGuess2, $typeGuesserChain->guessType($class, $property, $modelManager));

        $typeGuess4 = new TypeGuess('foo4', [], Guess::LOW_CONFIDENCE);
        $guesser4 = $this->getMockForAbstractClass(TypeGuesserInterface::class);
        $guesser4->expects($this->any())
                ->method('guessType')
                ->will($this->returnValue($typeGuess4));

        $typeGuesserChain = new TypeGuesserChain([$guesser4, $typeGuesserChain]);
        $this->assertSame($typeGuess2, $typeGuesserChain->guessType($class, $property, $modelManager));
    }
}
