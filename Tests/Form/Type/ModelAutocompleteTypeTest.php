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

use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;

use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelAutocompleteTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new ModelAutocompleteType();
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $optionResolver = new OptionsResolver();

        $type->setDefaultOptions($optionResolver);

        $options = $optionResolver->resolve(array('model_manager' => $modelManager, 'class' => 'Foo', 'property'=>'bar'));

        $this->assertEquals(array(), $options['attr']);
        $this->assertTrue($options['compound']);
        $this->assertInstanceOf('Sonata\AdminBundle\Model\ModelManagerInterface', $options['model_manager']);
        $this->assertEquals($modelManager, $options['model_manager']);
        $this->assertEquals('Foo', $options['class']);
        $this->assertEquals('bar', $options['property']);
        $this->assertNull($options['callback']);

        $this->assertEquals('', $options['placeholder']);
        $this->assertEquals(3, $options['minimum_input_length']);
        $this->assertEquals(10, $options['items_per_page']);

        $this->assertEquals('', $options['url']);
        $this->assertEquals(array('name'=>'sonata_admin_retrieve_autocomplete_items', 'parameters'=>array()), $options['route']);
        $this->assertEquals(array(), $options['req_params']);
        $this->assertEquals('q', $options['req_param_name_search']);
        $this->assertEquals('_page', $options['req_param_name_page_number']);
        $this->assertEquals('_per_page', $options['req_param_name_items_per_page']);
        $this->assertEquals('sonata-autocomplete-dropdown', $options['dropdown_css_class']);
    }
}
