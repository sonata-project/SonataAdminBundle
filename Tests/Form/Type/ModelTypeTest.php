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

use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Test\TypeTestCase;
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
        $this->assertSame('choice', $options['template']);
        $this->assertFalse($options['multiple']);
        $this->assertFalse($options['expanded']);
        $this->assertInstanceOf('Sonata\AdminBundle\Model\ModelManagerInterface', $options['model_manager']);
        $this->assertNull($options['class']);
        $this->assertNull($options['property']);
        $this->assertNull($options['query']);
        $this->assertSame(0, count($options['choices']));
        $this->assertSame(0, count($options['preferred_choices']));
        $this->assertSame('link_add', $options['btn_add']);
        $this->assertSame('link_list', $options['btn_list']);
        $this->assertSame('link_delete', $options['btn_delete']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
        $this->assertInstanceOf('Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList', $options['choice_list']);
    }

    /**
     * @dataProvider getCompoundOptionTests
     */
    public function testCompundOption($expectedCompound, $multiple, $expanded)
    {
        $type = new ModelType();
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $optionResolver = new OptionsResolver();

        $type->setDefaultOptions($optionResolver);

        $options = $optionResolver->resolve(array('model_manager' => $modelManager, 'choices' => array(), 'multiple' => $multiple, 'expanded' => $expanded));

        $this->assertSame($expectedCompound, $options['compound']);
        $this->assertSame('choice', $options['template']);
        $this->assertSame($multiple, $options['multiple']);
        $this->assertSame($expanded, $options['expanded']);
        $this->assertInstanceOf('Sonata\AdminBundle\Model\ModelManagerInterface', $options['model_manager']);
        $this->assertNull($options['class']);
        $this->assertNull($options['property']);
        $this->assertNull($options['query']);
        $this->assertSame(0, count($options['choices']));
        $this->assertSame(0, count($options['preferred_choices']));
        $this->assertSame('link_add', $options['btn_add']);
        $this->assertSame('link_list', $options['btn_list']);
        $this->assertSame('link_delete', $options['btn_delete']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
        $this->assertInstanceOf('Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList', $options['choice_list']);
    }

    public function getCompoundOptionTests()
    {
        return array(
            array(true, true, true), //checkboxes
            array(false, true, false), //select tag (with multiple attribute)
            array(true, false, true), //radio buttons
            array(false, false, false), //select tag
        );
    }
}
