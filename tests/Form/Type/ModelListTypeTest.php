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

use PHPUnit\Framework\MockObject\MockObject;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ModelListTypeTest extends TypeTestCase
{
    /**
     * @var MockObject&ModelManagerInterface<object>
     */
    private $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->createMock(ModelManagerInterface::class);

        parent::setUp();
    }

    public function testGetDefaultOptions(): void
    {
        $type = new ModelListType();
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $optionResolver = new OptionsResolver();

        $type->configureOptions($optionResolver);

        $options = $optionResolver->resolve(['model_manager' => $modelManager, 'class' => '\Foo']);

        static::assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        static::assertSame('\Foo', $options['class']);
        static::assertSame('link_add', $options['btn_add']);
        static::assertSame('link_edit', $options['btn_edit']);
        static::assertSame('link_list', $options['btn_list']);
        static::assertSame('link_delete', $options['btn_delete']);
        static::assertSame('SonataAdminBundle', $options['btn_catalogue']);
    }

    public function testSubmitValidData(): void
    {
        $form = $this->factory->create(
            ModelListType::class,
            null,
            [
                'model_manager' => $this->modelManager,
                'class' => 'My\Entity',
            ]
        );

        $this->modelManager->expects(static::once())->method('find')->with('My\Entity', '42');
        $form->submit('42');
        static::assertTrue($form->isSynchronized());
    }
}
