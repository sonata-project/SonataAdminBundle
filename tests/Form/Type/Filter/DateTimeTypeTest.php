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

use Sonata\AdminBundle\Form\Type\Filter\DateTimeType;
use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as FormDateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DateTimeTypeTest extends BaseTypeTest
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
        $type = new DateTimeType();

        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve();

        $expected = [
            'operator_type' => DateOperatorType::class,
            'picker' => false,
            'field_options' => ['date_format' => FormDateTimeType::HTML5_FORMAT],
            'field_type' => FormDateTimeType::class,
        ];
        static::assertSame($expected, $options);
    }

    public function testGetPickerDefaultOptions(): void
    {
        $type = new DateTimeType();

        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'picker' => true
        ]);

        $expected = [
            'operator_type' => DateOperatorType::class,
            'field_options' => ['date_format' => FormDateTimeType::HTML5_FORMAT],
            'picker' => true,
            'field_type' => DateTimePickerType::class,
        ];
        static::assertSame($expected, $options);
    }

    protected function getTestedType(): string
    {
        return DateTimeType::class;
    }
}
