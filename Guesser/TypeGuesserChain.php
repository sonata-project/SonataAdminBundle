<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Guesser;

use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Guess\Guess;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 *
 * The code is based on Symfony2 Form Components
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony.com>
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TypeGuesserChain implements TypeGuesserInterface
{
    protected $guessers = array();

    /**
     * @param array $guessers
     */
    public function __construct(array $guessers)
    {
        foreach ($guessers as $guesser) {
            if (!$guesser instanceof TypeGuesserInterface) {
                throw new UnexpectedTypeException($guesser, 'Sonata\AdminBundle\Guesser\TypeGuesserInterface');
            }

            if ($guesser instanceof self) {
                $this->guessers = array_merge($this->guessers, $guesser->guessers);
            } else {
                $this->guessers[] = $guesser;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        return $this->guess(function ($guesser) use ($class, $property, $modelManager) {
            return $guesser->guessType($class, $property, $modelManager);
        });
    }

    /**
     * Executes a closure for each guesser and returns the best guess from the
     * return values
     *
     * @param \Closure $closure The closure to execute. Accepts a guesser
     *                            as argument and should return a Guess instance
     *
     * @return Guess The guess with the highest confidence
     */
    private function guess(\Closure $closure)
    {
        $guesses = array();

        foreach ($this->guessers as $guesser) {
            if ($guess = $closure($guesser)) {
                $guesses[] = $guess;
            }
        }

        return Guess::getBestGuess($guesses);
    }
}
