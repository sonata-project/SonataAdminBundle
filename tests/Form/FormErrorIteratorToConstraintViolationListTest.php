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

namespace Sonata\AdminBundle\Tests\Form;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\FormErrorIteratorToConstraintViolationList;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @author Jordi Sala <jordism91@gmail.com>
 */
final class FormErrorIteratorToConstraintViolationListTest extends TestCase
{
    /**
     * @param list<FormError> $errors
     */
    #[DataProvider('provideTransformCases')]
    public function testTransform(int $expectedCount, array $errors): void
    {
        $form = static::createStub(FormInterface::class);
        $form->method('getName')->willReturn('name');

        $violationList = FormErrorIteratorToConstraintViolationList::transform(new FormErrorIterator($form, $errors));

        static::assertInstanceOf(ConstraintViolationList::class, $violationList);
        static::assertCount($expectedCount, $violationList);
    }

    /**
     * @phpstan-return iterable<array{int, list<FormError>}>
     */
    public static function provideTransformCases(): iterable
    {
        yield [0, []];

        yield [0, [
            new FormError('error'),
        ]];

        yield [1, [
            new FormError(
                'error',
                null,
                [],
                null,
                new ConstraintViolation('error', null, [], null, 'path', 'invalid value')
            ),
        ]];
    }
}
