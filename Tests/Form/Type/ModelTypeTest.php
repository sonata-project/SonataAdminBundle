<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\ModelType;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new ModelType();
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $optionResolver = new OptionsResolver();

        $type->setDefaultOptions($optionResolver);

        $options = $optionResolver->resolve(array('model_manager' => $modelManager, 'choices' => array()));

        $this->assertFalse($options['compound']);
        $this->assertEquals('choice', $options['template']);
        $this->assertFalse($options['multiple']);
        $this->assertFalse($options['expanded']);
        $this->assertInstanceOf('Sonata\AdminBundle\Model\ModelManagerInterface', $options['model_manager']);
        $this->assertNull($options['class']);
        $this->assertNull($options['property']);
        $this->assertNull($options['query']);
        $this->assertEquals(0, count($options['choices']));
        $this->assertEquals(0, count($options['preferred_choices']));
        $this->assertEquals('link_add', $options['btn_add']);
        $this->assertEquals('link_list', $options['btn_list']);
        $this->assertEquals('link_delete', $options['btn_delete']);
        $this->assertEquals('SonataAdminBundle', $options['btn_catalogue']);
        $this->assertInstanceOf('Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList', $options['choice_list']);
    }
}
