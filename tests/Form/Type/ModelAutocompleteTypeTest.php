<?php

declare(strict_types=1);

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
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelAutocompleteTypeTest extends TypeTestCase
{
    /**
     * @var ModelAutocompleteType
     */
    private $type;

    protected function setUp()
    {
        $this->type = new ModelAutocompleteType();

        parent::setUp();
    }

    public function testGetDefaultOptions()
    {
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $optionResolver = new OptionsResolver();

        $this->type->configureOptions($optionResolver);

        $options = $optionResolver->resolve(['model_manager' => $modelManager, 'class' => 'Foo', 'property' => 'bar']);

        $this->assertSame([], $options['attr']);
        $this->assertFalse($options['compound']);
        $this->assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        $this->assertSame($modelManager, $options['model_manager']);
        $this->assertSame('Foo', $options['class']);
        $this->assertSame('bar', $options['property']);
        $this->assertNull($options['callback']);

        $this->assertSame('', $options['placeholder']);
        $this->assertSame(3, $options['minimum_input_length']);
        $this->assertSame(10, $options['items_per_page']);
        $this->assertSame(100, $options['quiet_millis']);
        $this->assertFalse($options['cache']);
        $this->assertSame('', $options['width']);
        $this->assertFalse($options['dropdown_auto_width']);

        $this->assertSame('', $options['url']);
        $this->assertSame(['name' => 'sonata_admin_retrieve_autocomplete_items', 'parameters' => []], $options['route']);
        $this->assertSame([], $options['req_params']);
        $this->assertSame('q', $options['req_param_name_search']);
        $this->assertSame('_page', $options['req_param_name_page_number']);
        $this->assertSame('_per_page', $options['req_param_name_items_per_page']);

        $this->assertSame('list', $options['target_admin_access_action']);

        $this->assertSame('', $options['container_css_class']);
        $this->assertSame('', $options['dropdown_css_class']);
        $this->assertSame('', $options['dropdown_item_css_class']);

        $this->assertSame('@SonataAdmin/Form/Type/sonata_type_model_autocomplete.html.twig', $options['template']);

        $this->assertSame('', $options['context']);

        // NEXT_MAJOR: Set this value to 'link_add'
        $this->assertFalse($options['btn_add']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
    }

    public function testGetBlockPrefix()
    {
        $this->assertSame('sonata_type_model_autocomplete', $this->type->getBlockPrefix());
    }
}
