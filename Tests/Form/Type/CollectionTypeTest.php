<?php

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionTypeTest extends TypeTestCase
{

    public function testGetDefaultOptions()
    {
        $type = new CollectionType();

        $optionResolver = new OptionsResolver();

        $type->setDefaultOptions($optionResolver);
        $options = $optionResolver->resolve(
            array(
                'btn_add' => 'add',
                'btn_catalogue' => 'catalogue',
                'btn_delete' => 'delete'
            ));

        $this->assertEquals('add', $options['btn_add']);
        $this->assertEquals('catalogue', $options['btn_catalogue']);
        $this->assertEquals('delete', $options['btn_delete']);
    }

    public function testGetName()
    {
        $type = new CollectionType();
        $this->assertEquals('sonata_type_native_collection', $type->getName());
    }

    public function testGetParent()
    {
        $type = new CollectionType();
        $this->assertEquals('collection', $type->getParent());
    }

}
