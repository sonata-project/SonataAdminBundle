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

namespace Sonata\AdminBundle\Tests\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ArrayToModelTransformerTest extends TestCase
{
    private $modelManager = null;

    public function setUp(): void
    {
        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
    }

    public function testReverseTransformEntity(): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, FooEntity::class);

        $entity = new FooEntity();
        $this->assertSame($entity, $transformer->reverseTransform($entity));
    }

    /**
     * @dataProvider getReverseTransformTests
     */
    public function testReverseTransform($value): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, FooEntity::class);

        $this->modelManager
            ->method('modelReverseTransform')
            ->willReturn(new FooEntity());

        $this->assertInstanceOf(FooEntity::class, $transformer->reverseTransform($value));
    }

    public function getReverseTransformTests()
    {
        return [
            [FooEntity::class],
            [[]],
            [['foo' => 'bar']],
            ['foo'],
            [123],
            [null],
            [false],
        ];
    }

    /**
     * @dataProvider getTransformTests
     */
    public function testTransform($expected, $value): void
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, FooEntity::class);

        $this->assertSame($expected, $transformer->transform($value));
    }

    public function getTransformTests()
    {
        return [
            [123, 123],
            ['foo', 'foo'],
            [false, false],
            [null, null],
            [0, 0],
        ];
    }
}
