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

        $options = $optionResolver->resolve(array('model_manager' => $modelManager, 'class' => 'Foo', 'property' => 'bar'));

        $this->assertSame(array(), $options['attr']);
        $this->assertTrue($options['compound']);
        $this->assertInstanceOf('Sonata\AdminBundle\Model\ModelManagerInterface', $options['model_manager']);
        $this->assertSame($modelManager, $options['model_manager']);
        $this->assertSame('Foo', $options['class']);
        $this->assertSame('bar', $options['property']);
        $this->assertNull($options['callback']);

        $this->assertSame('', $options['placeholder']);
        $this->assertSame(3, $options['minimum_input_length']);
        $this->assertSame(10, $options['items_per_page']);

        $this->assertSame('', $options['url']);
        $this->assertSame(array('name' => 'sonata_admin_retrieve_autocomplete_items', 'parameters' => array()), $options['route']);
        $this->assertSame(array(), $options['req_params']);
        $this->assertSame('q', $options['req_param_name_search']);
        $this->assertSame('_page', $options['req_param_name_page_number']);
        $this->assertSame('_per_page', $options['req_param_name_items_per_page']);
        $this->assertSame('sonata-autocomplete-dropdown', $options['dropdown_css_class']);
    }
}
