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
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceFieldMaskTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions(): void
    {
        $type = new ChoiceFieldMaskType();

        $optionResolver = new OptionsResolver();

        $type->configureOptions($optionResolver);

        $options = $optionResolver->resolve(
            [
                'map' => [
                    'foo' => ['field1', 'field2'],
                    'bar' => ['field3'],
            ],
        ]);

        $this->assertSame(['foo' => ['field1', 'field2'], 'bar' => ['field3']], $options['map']);
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
}
