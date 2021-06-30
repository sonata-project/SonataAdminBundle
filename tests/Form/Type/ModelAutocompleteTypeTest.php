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

class ModelAutocompleteTypeTest extends TypeTestCase
{
    /**
     * @var ModelAutocompleteType
     */
    private $type;

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

        self::assertSame([], $options['attr']);
        self::assertFalse($options['compound']);
        self::assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        self::assertSame($modelManager, $options['model_manager']);
        self::assertSame('Foo', $options['class']);
        self::assertSame('bar', $options['property']);
        self::assertNull($options['callback']);

        self::assertSame('', $options['placeholder']);
        self::assertSame(3, $options['minimum_input_length']);
        self::assertSame(10, $options['items_per_page']);
        self::assertSame(100, $options['quiet_millis']);
        self::assertFalse($options['cache']);
        self::assertSame('', $options['width']);
        self::assertFalse($options['dropdown_auto_width']);

        self::assertSame('', $options['url']);
        self::assertSame(['name' => 'sonata_admin_retrieve_autocomplete_items', 'parameters' => []], $options['route']);
        self::assertSame([], $options['req_params']);
        self::assertSame('q', $options['req_param_name_search']);
        self::assertSame(DatagridInterface::PAGE, $options['req_param_name_page_number']);
        self::assertSame(DatagridInterface::PER_PAGE, $options['req_param_name_items_per_page']);

        self::assertSame('list', $options['target_admin_access_action']);
        self::assertNull($options['response_item_callback']);

        self::assertSame('', $options['container_css_class']);
        self::assertSame('', $options['dropdown_css_class']);
        self::assertSame('', $options['dropdown_item_css_class']);

        self::assertSame('@SonataAdmin/Form/Type/sonata_type_model_autocomplete.html.twig', $options['template']);

        self::assertSame('', $options['context']);

        self::assertSame('link_add', $options['btn_add']);
        self::assertSame('SonataAdminBundle', $options['btn_catalogue']);
    }

    public function testGetBlockPrefix(): void
    {
        self::assertSame('sonata_type_model_autocomplete', $this->type->getBlockPrefix());
    }
}
