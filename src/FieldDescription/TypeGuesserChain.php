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

use Symfony\Component\Form\Guess\TypeGuess;

/**
 * The code is based on Symfony2 Form Components.
 */
final class TypeGuesserChain implements TypeGuesserInterface
{
    /**
     * @var TypeGuesserInterface[]
     */
    private array $guessers = [];

    /**
     * @param TypeGuesserInterface[] $guessers
     */
    public function __construct(array $guessers)
    {
        $allGuessers = [];

        foreach ($guessers as $guesser) {
            if (!$guesser instanceof TypeGuesserInterface) {
                throw new \InvalidArgumentException(\sprintf(
                    'Expected argument of type "%s", "%s" given',
                    TypeGuesserInterface::class,
                    get_debug_type($guesser)
                ));
            }

            if ($guesser instanceof self) {
                $allGuessers[] = $guesser->guessers;
            } else {
                $allGuessers[] = [$guesser];
            }
        }

        $this->guessers = array_merge(...$allGuessers);
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion @see https://github.com/vimeo/psalm/issues/5938
     */
    public function guess(FieldDescriptionInterface $fieldDescription): ?TypeGuess
    {
        $guesses = [];

        foreach ($this->guessers as $guesser) {
            $guess = $guesser->guess($fieldDescription);
            if (null !== $guess) {
                $guesses[] = $guess;
            }
        }

        return TypeGuess::getBestGuess($guesses);
    }
}
