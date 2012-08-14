<?php

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\BooleanType;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new BooleanType();

        $optionResolver = new OptionsResolver();

        $this->assertEquals('sonata_type_translatable_choice', $type->getParent());

        $type->setDefaultOptions($optionResolver);

        $options = $optionResolver->resolve();

        $this->assertEquals(2, count($options['choices']));
    }
}