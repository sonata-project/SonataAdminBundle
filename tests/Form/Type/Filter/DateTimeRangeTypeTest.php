<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Form\Type\Filter;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use SensioLabs\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use SensioLabs\AdminBundle\Form\Type\Operator\DateRangeOperatorType;
use Sonata\Form\Type\DateTimeRangeType as FormDateTimeRangeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * NEXT_MAJOR: Remove this class.
 */
#[IgnoreDeprecations]
final class DateTimeRangeTypeTest extends BaseTypeTestCase
{
    public function testDefaultOptions(): void
    {
        $form = $this->factory->create($this->getTestedType());

        $view = $form->createView();

        static::assertFalse($view->children['type']->vars['required']);
        static::assertFalse($view->children['value']->vars['required']);
    }

    public function testGetDefaultOptions(): void
    {
        $type = new DateTimeRangeType();

        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve();

        $expected = [
            'operator_type' => DateRangeOperatorType::class,
            'field_type' => FormDateTimeRangeType::class,
            'field_options' => ['field_options' => []],
        ];
        static::assertSame($expected, $options);
    }

    protected function getTestedType(): string
    {
        return DateTimeRangeType::class;
    }
}
