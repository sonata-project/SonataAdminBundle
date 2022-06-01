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

use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\DateType as FormDateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DateTypeTest extends BaseTypeTest
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
        $type = new DateType();

        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve();

        $expected = [
            'operator_type' => DateOperatorType::class,
            'picker' => false,
            'field_options' => ['format' => FormDateType::HTML5_FORMAT],
            'field_type' => FormDateType::class,
        ];
        static::assertSame($expected, $options);
    }

    public function testGetPickerDefaultOptions(): void
    {
        $type = new DateType();

        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve([
            'picker' => true
        ]);

        $expected = [
            'operator_type' => DateOperatorType::class,
            'field_options' => ['format' => FormDateType::HTML5_FORMAT],
            'picker' => true,
            'field_type' => DatePickerType::class,
        ];
        static::assertSame($expected, $options);
    }

    protected function getTestedType(): string
    {
        return DateType::class;
    }
}
