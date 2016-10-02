<?php

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
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceFieldMaskTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new ChoiceFieldMaskType();

        $optionResolver = new OptionsResolver();

        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $type->setDefaultOptions($optionResolver);
        } else {
            $type->configureOptions($optionResolver);
        }

        $options = $optionResolver->resolve(
            array(
                'map' => array(
                    'foo' => array('field1', 'field2'),
                    'bar' => array('field3'),
            ),
        ));

        $this->assertSame(array('foo' => array('field1', 'field2'), 'bar' => array('field3')), $options['map']);
    }

    public function testGetName()
    {
        $type = new ChoiceFieldMaskType();
        $this->assertSame('sonata_type_choice_field_mask', $type->getName());
    }

    public function testGetParent()
    {
        $type = new ChoiceFieldMaskType();
        // NEXT_MAJOR: Remove ternary (when requirement of Symfony is >= 2.8)
        $this->assertSame(method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType'
            : 'choice', $type->getParent());
    }
}
