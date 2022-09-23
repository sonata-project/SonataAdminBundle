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

namespace Sonata\AdminBundle\Form;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Jordi Sala <jordism91@gmail.com>
 */
final class FormErrorIteratorToConstraintViolationList
{
    /**
     * @param FormErrorIterator<FormError> $errors
     */
    public static function transform(FormErrorIterator $errors): ConstraintViolationListInterface
    {
        $closure = \Closure::bind(static function (ConstraintViolation $violation, string $newPropertyPath): ConstraintViolation {
            /**
             * @psalm-suppress InaccessibleProperty
             */
            $violation->propertyPath = $newPropertyPath;

            return $violation;
        }, null, ConstraintViolation::class);

        $list = new ConstraintViolationList();

        foreach ($errors as $error) {
            $origin = $error->getOrigin();
            $cause = $error->getCause();

            if (null === $origin || !$cause instanceof ConstraintViolation) {
                continue;
            }

            /**
             * @psalm-suppress PossiblyInvalidFunctionCall
             */
            $list->add($closure($cause, self::buildName($origin)));
        }

        return $list;
    }

    private static function buildName(FormInterface $form): string
    {
        $parent = $form->getParent();

        if (null === $parent) {
            return $form->getName();
        }

        return self::buildName($parent).'['.$form->getName().']';
    }
}
