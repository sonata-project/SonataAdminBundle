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

namespace Sonata\AdminBundle\Guesser;

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * The code is based on Symfony2 Form Components.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony.com>
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TypeGuesserChain implements TypeGuesserInterface
{
    /**
     * @var TypeGuesserInterface[]
     */
    protected $guessers = [];

    public function __construct(array $guessers)
    {
        foreach ($guessers as $guesser) {
            if (!$guesser instanceof TypeGuesserInterface) {
                throw new UnexpectedTypeException($guesser, TypeGuesserInterface::class);
            }

            if ($guesser instanceof self) {
                $this->guessers = array_merge($this->guessers, $guesser->guessers);
            } else {
                $this->guessers[] = $guesser;
            }
        }
    }

    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        $guesses = [];

        foreach ($this->guessers as $guesser) {
            $guess = $guesser->guessType($class, $property, $modelManager);

            if (null !== $guess) {
                $guesses[] = $guess;
            }
        }

        $bestGuess = TypeGuess::getBestGuess($guesses);
        // todo - remove `assert` statement after https://github.com/symfony/symfony/pull/37725 is released
        \assert($bestGuess instanceof TypeGuess || null === $bestGuess);

        return $bestGuess;
    }
}
