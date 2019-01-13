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

use Sonata\AdminBundle\Form\Type\ModelReferenceType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class ModelReferenceTypeTest extends TypeTestCase
{
    private $modelManager;

    protected function setUp()
    {
        $this->modelManager = $this->prophesize(ModelManagerInterface::class);

        parent::setUp();
    }

    public function testSubmitValidData()
    {
        $formData = 42;

        $form = $this->factory->create(
            ModelReferenceType::class,
            null,
            [
                'model_manager' => $this->modelManager->reveal(),
                'class' => 'My\Entity',
            ]
        );
        $this->modelManager->find('My\Entity', 42)->shouldBeCalled();
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
    }

    protected function getExtensions()
    {
        return [
            new PreloadedExtension([
                new ModelReferenceType($this->modelManager->reveal()),
            ], []),
        ];
    }
}
