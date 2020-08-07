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

use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModelListTypeTest extends TypeTestCase
{
    private $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->prophesize(ModelManagerInterface::class);

        parent::setUp();
    }

    public function testGetDefaultOptions(): void
    {
        $type = new ModelListType();

        $optionResolver = new OptionsResolver();

        $type->configureOptions($optionResolver);

        $options = $optionResolver->resolve();

        $this->assertNull($options['model_manager']);
        $this->assertNull($options['class']);
        $this->assertSame('link_add', $options['btn_add']);
        $this->assertSame('link_edit', $options['btn_edit']);
        $this->assertSame('link_list', $options['btn_list']);
        $this->assertSame('link_delete', $options['btn_delete']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
    }

    public function testSubmitValidData(): void
    {
        $model = new \stdClass();
        $this->modelManager->find('My\Entity', 42)->willReturn($model);
        $this->modelManager->getNormalizedIdentifier($model)->willReturn('42');

        $form = $this->factory->create(
            ModelListType::class,
            null,
            [
                'model_manager' => $this->modelManager->reveal(),
                'class' => 'My\Entity',
            ]
        );

        $form->submit(42);
        $this->assertTrue($form->isSynchronized());
    }
}
