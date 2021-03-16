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

namespace Sonata\AdminBundle\FieldDescription;

use Sonata\AdminBundle\Guesser\TypeGuesserInterface as DeprecatedTypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @final
 *
 * NEXT_MAJOR: Remove DeprecateTypeGuesserInterface.
 *
 * The code is based on Symfony2 Form Components.
 */
class TypeGuesserChain implements TypeGuesserInterface, DeprecatedTypeGuesserInterface
{
    /**
     * @var TypeGuesserInterface[]|DeprecatedTypeGuesserInterface[]
     */
    private $guessers = [];

    /**
     * @param TypeGuesserInterface[]|DeprecatedTypeGuesserInterface[] $guessers
     */
    public function __construct(array $guessers)
    {
        $allGuessers = [];

        foreach ($guessers as $guesser) {
            // NEXT_MAJOR: Remove DeprecateTypeGuesserInterface check.
            if (!$guesser instanceof TypeGuesserInterface && !$guesser instanceof DeprecatedTypeGuesserInterface) {
                // NEXT_MAJOR: Throw \InvalidArgumentException
                throw new UnexpectedTypeException($guesser, TypeGuesserInterface::class);
            }

            if ($guesser instanceof self) {
                $allGuessers[] = $guesser->guessers;
            } else {
                $allGuessers[] = [$guesser];
            }
        }

        $this->guessers = array_merge(...$allGuessers);
    }

    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        $guesses = [];

        foreach ($this->guessers as $guesser) {
            if (!$guesser instanceof TypeGuesserInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected guesser of type "%s", "%s" given',
                    TypeGuesserInterface::class,
                    DeprecatedTypeGuesserInterface::class
                ));
            }

            $guesses[] = $guesser->guess($fieldDescription);
        }

        return TypeGuess::getBestGuess($guesses);
    }

    // NEXT_MAJOR: Remove this method.
    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        @trigger_error(sprintf(
            'The %s method is deprecated since sonata-project/admin-bundle 3.92 and will be removed in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $guesses = [];

        foreach ($this->guessers as $guesser) {
            $guess = $guesser->guessType($class, $property, $modelManager);

            if (!$guesser instanceof DeprecatedTypeGuesserInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected guesser of type "%s", "%s" given',
                    DeprecatedTypeGuesserInterface::class,
                    TypeGuesserInterface::class
                ));
            }

            if (null !== $guess) {
                $guesses[] = $guess;
            }
        }

        return TypeGuess::getBestGuess($guesses);
    }
}

// NEXT_MAJOR: Remove next line.
class_exists(\Sonata\AdminBundle\Guesser\TypeGuesserChain::class);
