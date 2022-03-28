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

use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ModelAutocompleteTypeTest extends TypeTestCase
{
    private ModelAutocompleteType $type;

    protected function setUp(): void
    {
        $this->type = new ModelAutocompleteType();

        parent::setUp();
    }

    public function testGetDefaultOptions(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $optionResolver = new OptionsResolver();

        $this->type->configureOptions($optionResolver);

        $options = $optionResolver->resolve(['model_manager' => $modelManager, 'class' => 'Foo', 'property' => 'bar']);

        static::assertSame([], $options['attr']);
        static::assertFalse($options['compound']);
        static::assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        static::assertSame($modelManager, $options['model_manager']);
        static::assertSame('Foo', $options['class']);
        static::assertSame('bar', $options['property']);
        static::assertNull($options['callback']);

        static::assertSame('', $options['placeholder']);
        static::assertSame(3, $options['minimum_input_length']);
        static::assertSame(10, $options['items_per_page']);
        static::assertSame(100, $options['quiet_millis']);
        static::assertFalse($options['cache']);
        static::assertSame('', $options['width']);
        static::assertFalse($options['dropdown_auto_width']);

        static::assertSame('', $options['url']);
        static::assertSame(['name' => 'sonata_admin_retrieve_autocomplete_items', 'parameters' => []], $options['route']);
        static::assertSame([], $options['req_params']);
        static::assertSame('q', $options['req_param_name_search']);
        static::assertSame(DatagridInterface::PAGE, $options['req_param_name_page_number']);
        static::assertSame(DatagridInterface::PER_PAGE, $options['req_param_name_items_per_page']);

        static::assertSame('list', $options['target_admin_access_action']);
        static::assertNull($options['response_item_callback']);

        static::assertSame('', $options['container_css_class']);
        static::assertSame('', $options['dropdown_css_class']);
        static::assertSame('', $options['dropdown_item_css_class']);

        static::assertSame('@SonataAdmin/Form/Type/sonata_type_model_autocomplete.html.twig', $options['template']);

        static::assertSame('', $options['context']);

        static::assertSame('link_add', $options['btn_add']);
        static::assertSame('SonataAdminBundle', $options['btn_catalogue']);
        static::assertSame('SonataAdminBundle', $options['btn_translation_domain']);
    }

    public function testGetBlockPrefix(): void
    {
        static::assertSame('sonata_type_model_autocomplete', $this->type->getBlockPrefix());
    }
}
