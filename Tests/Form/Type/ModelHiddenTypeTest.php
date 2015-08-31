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

use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelHiddenTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new ModelHiddenType();
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $optionResolver = new OptionsResolver();

        $type->setDefaultOptions($optionResolver);

        $options = $optionResolver->resolve(array('model_manager' => $modelManager, 'class' => '\Foo'));

        $this->assertInstanceOf('Sonata\AdminBundle\Model\ModelManagerInterface', $options['model_manager']);
        $this->assertSame($modelManager, $options['model_manager']);
        $this->assertSame('\Foo', $options['class']);
    }

    public function testGetName()
    {
        $type = new ModelHiddenType();
        $this->assertSame('sonata_type_model_hidden', $type->getName());
    }

    public function testGetParent()
    {
        $type = new ModelHiddenType();
        $this->assertSame('hidden', $type->getParent());
    }
}
