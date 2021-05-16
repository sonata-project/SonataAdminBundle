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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;

class ModelToIdTransformerTest extends TestCase
{
    /**
     * @var ModelManagerInterface&MockObject
     */
    private $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
    }

    public function testReverseTransformWhenPassing0AsId(): void
    {
        $className = \stdClass::class;
        $transformer = new ModelToIdTransformer($this->modelManager, $className);

        $this->modelManager
                ->expects($this->exactly(2))
                ->method('find')
                ->willReturn(new \stdClass());

        // we pass 0 as integer
        $this->assertNotNull($transformer->reverseTransform(0));

        // we pass 0 as string
        $this->assertNotNull($transformer->reverseTransform('0'));

        // we pass null must return null
        $this->assertNull($transformer->reverseTransform(null));

        // we pass false, must return null
        $this->assertNull($transformer->reverseTransform(false));
    }

    /**
     * @dataProvider getReverseTransformValues
     */
    public function testReverseTransform($value, $expected): void
    {
        $className = \stdClass::class;
        $transformer = new ModelToIdTransformer($this->modelManager, $className);

        $this->modelManager->expects($this->never())->method('find');

        $this->assertSame($expected, $transformer->reverseTransform($value));
    }

    public function getReverseTransformValues()
    {
        return [
            [null, null],
            [false, null],
            [[], null],
            ['', null],
        ];
    }

    public function testTransform(): void
    {
        $this->modelManager->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->willReturn('123');

        $className = \stdClass::class;
        $transformer = new ModelToIdTransformer($this->modelManager, $className);

        $this->assertNull($transformer->transform(null));

        $this->assertSame('123', $transformer->transform(new \stdClass()));
    }
}
