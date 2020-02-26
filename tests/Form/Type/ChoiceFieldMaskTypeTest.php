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

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceFieldMaskTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions(): void
    {
        $options = $this->resolveOptions([
            'map' => [
                'foo' => ['field1', 'field2'],
                'bar' => ['field3'],
            ],
        ]);

        $this->assertSame(['foo' => ['field1', 'field2'], 'bar' => ['field3']], $options['map']);
    }

    public function testGetDefaultOptions2(): void
    {
        $options = $this->resolveOptions([]);

        $this->assertSame(['map' => []], $options);
    }

    public function setAllowedTypesProvider(): array
    {
        return [
            'null' => [null],
            'integer' => [1],
            'boolean' => [false],
            'string' => ['string'],
            'class' => [new \stdClass()],
        ];
    }

    /**
     * @dataProvider setAllowedTypesProvider
     */
    public function testSetAllowedTypes($map): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessageMatches('/The option "map" with value .* is expected to be of type "array", but is of type ".*"/');

        $this->resolveOptions(['map' => $map]);
    }

    public function testGetBlockPrefix(): void
    {
        $type = new ChoiceFieldMaskType();
        $this->assertSame('sonata_type_choice_field_mask', $type->getBlockPrefix());
    }

    public function testGetParent(): void
    {
        $type = new ChoiceFieldMaskType();
        $this->assertSame(ChoiceType::class, $type->getParent());
    }

    public function testBuildView(): void
    {
        $choiceFieldMaskType = new ChoiceFieldMaskType();

        $view = $this->prophesize(FormView::class);
        $form = $this->prophesize(FormInterface::class);
        $options = [
            'map' => [
                'choice_1' => ['field1', 'field2'],
                'choice_2' => ['field__3', 'field.4'],
                'choice_3' => ['field1', 'field5'],
            ],
        ];

        $choiceFieldMaskType->buildView(
            $view->reveal(),
            $form->reveal(),
            $options
        );

        $expectedAllFields = [
            'field1',
            'field2',
            'field____3',
            'field__4',
            'field5',
        ];

        $expectedMap = [
            'choice_1' => [
                'field1',
                'field2',
            ],
            'choice_2' => [
                'field____3',
                'field__4',
            ],
            'choice_3' => [
                'field1',
                'field5',
            ],
        ];

        $this->assertSame(array_values($expectedAllFields), array_values($view->reveal()->vars['all_fields']), '"all_fields" is not as expected');
        $this->assertSame($expectedMap, $view->reveal()->vars['map'], '"map" is not as expected');
    }

    public function testBuildViewWithFaultyMapValues(): void
    {
        $options = ['map' => [
            'int' => 1,
            'string' => 'string',
            'boolean' => false,
            'array' => ['field_1', 'field_2'],
            'empty_array' => [],
            'class' => new \stdClass(),
        ]];

        $choiceFieldMaskType = new ChoiceFieldMaskType();

        $view = $this->prophesize(FormView::class);
        $form = $this->prophesize(FormInterface::class);

        $choiceFieldMaskType->buildView(
            $view->reveal(),
            $form->reveal(),
            $options
        );

        $expectedAllFields = ['field_1', 'field_2'];
        $expectedMap = [
            'array' => ['field_1', 'field_2'],
        ];

        $this->assertSame(array_values($expectedAllFields), array_values($view->reveal()->vars['all_fields']), '"all_fields" is not as expected');
        $this->assertSame($expectedMap, $view->reveal()->vars['map'], '"map" is not as expected');
    }

    private function resolveOptions(array $options): array
    {
        $type = new ChoiceFieldMaskType();
        $optionResolver = new OptionsResolver();

        $type->configureOptions($optionResolver);

        return $optionResolver->resolve($options);
    }
}
