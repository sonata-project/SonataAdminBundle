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
use Symfony\Component\PropertyAccess\PropertyAccess;

class ModelTypeTest extends TypeTestCase
{
    protected $type;

    public function setUp()
    {
        parent::setUp();

        $this->type = new ModelType(PropertyAccess::createPropertyAccessor());
    }

    public function testGetDefaultOptions()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $optionResolver = new OptionsResolver();

        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $this->type->setDefaultOptions($optionResolver);
        } else {
            $this->type->configureOptions($optionResolver);
        }

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
        if (interface_exists('Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface')) { // SF2.7+
            $this->assertInstanceOf('Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader', $options['choice_loader']);
        } else {
            $this->assertInstanceOf('Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList', $options['choice_list']);
        }
    }

    /**
     * @dataProvider getCompoundOptionTests
     */
    public function testCompoundOption($expectedCompound, $multiple, $expanded)
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $optionResolver = new OptionsResolver();

        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $this->type->setDefaultOptions($optionResolver);
        } else {
            $this->type->configureOptions($optionResolver);
        }

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

        if (interface_exists('Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface')) { // SF2.7+
            $this->assertInstanceOf('Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader', $options['choice_loader']);
        } else {
            $this->assertInstanceOf('Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList', $options['choice_list']);
        }
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
