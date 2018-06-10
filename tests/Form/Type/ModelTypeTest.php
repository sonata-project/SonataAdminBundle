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

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
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
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);

        $optionResolver = new OptionsResolver();

        $this->type->configureOptions($optionResolver);

        $options = $optionResolver->resolve(['model_manager' => $modelManager, 'choices' => []]);

        $this->assertFalse($options['compound']);
        $this->assertSame('choice', $options['template']);
        $this->assertFalse($options['multiple']);
        $this->assertFalse($options['expanded']);
        $this->assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        $this->assertNull($options['class']);
        $this->assertNull($options['property']);
        $this->assertNull($options['query']);
        $this->assertCount(0, $options['choices']);
        $this->assertCount(0, $options['preferred_choices']);
        $this->assertSame('link_add', $options['btn_add']);
        $this->assertSame('link_list', $options['btn_list']);
        $this->assertSame('link_delete', $options['btn_delete']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
        $this->assertInstanceOf(ModelChoiceLoader::class, $options['choice_loader']);
    }

    /**
     * @dataProvider getCompoundOptionTests
     */
    public function testCompoundOption($expectedCompound, $multiple, $expanded)
    {
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $optionResolver = new OptionsResolver();

        $this->type->configureOptions($optionResolver);

        $options = $optionResolver->resolve(['model_manager' => $modelManager, 'choices' => [], 'multiple' => $multiple, 'expanded' => $expanded]);

        $this->assertSame($expectedCompound, $options['compound']);
        $this->assertSame('choice', $options['template']);
        $this->assertSame($multiple, $options['multiple']);
        $this->assertSame($expanded, $options['expanded']);
        $this->assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        $this->assertNull($options['class']);
        $this->assertNull($options['property']);
        $this->assertNull($options['query']);
        $this->assertCount(0, $options['choices']);
        $this->assertCount(0, $options['preferred_choices']);
        $this->assertSame('link_add', $options['btn_add']);
        $this->assertSame('link_list', $options['btn_list']);
        $this->assertSame('link_delete', $options['btn_delete']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
        $this->assertInstanceOf(ModelChoiceLoader::class, $options['choice_loader']);
    }

    public function getCompoundOptionTests()
    {
        return [
            [true, true, true], //checkboxes
            [false, true, false], //select tag (with multiple attribute)
            [true, false, true], //radio buttons
            [false, false, false], //select tag
        ];
    }
}
