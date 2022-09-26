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
use Symfony\Component\Validator\ConstraintViolationInterface;
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
        $list = new ConstraintViolationList();

        foreach ($errors as $error) {
            $violation = static::buildViolation($error);

            if (null === $violation) {
                continue;
            }

            $list->add($violation);
        }

        return $list;
    }

    private static function buildViolation(FormError $error): ?ConstraintViolationInterface
    {
        $origin = $error->getOrigin();
        $cause = $error->getCause();

        if (null === $origin || !$cause instanceof ConstraintViolationInterface) {
            return null;
        }

        return new ConstraintViolation(
            $cause->getMessage(),
            $cause->getMessageTemplate(),
            $cause->getParameters(),
            $cause->getRoot(),
            self::buildName($origin),
            $cause->getInvalidValue(),
            $cause->getPlural(),
            $cause->getCode(),
        );
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
