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

namespace Sonata\AdminBundle\Tests\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Form\Type\Operator\DateRangeOperatorType;
use Sonata\Form\Type\DateRangePickerType;
use Sonata\Form\Type\DateRangeType as FormDateRangeType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DateRangeTypeTest extends BaseTypeTest
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
        $type = new DateRangeType();

        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve();

        $expected = [
            'operator_type' => DateRangeOperatorType::class,
            'picker' => false,
            'field_options' => ['field_options' => ['format' => DateType::HTML5_FORMAT]],
            'field_type' => FormDateRangeType::class,
        ];
        static::assertSame($expected, $options);
    }

    public function testGetPickerDefaultOptions(): void
    {
        $type = new DateRangeType();

        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'picker' => true
        ]);

        $expected = [
            'operator_type' => DateRangeOperatorType::class,
            'field_options' => ['field_options' => ['format' => DateType::HTML5_FORMAT]],
            'picker' => true,
            'field_type' => DateRangePickerType::class,
        ];
        static::assertSame($expected, $options);
    }
    protected function getTestedType(): string
    {
        return DateRangeType::class;
    }
}
