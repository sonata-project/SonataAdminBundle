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
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;

class ModelToIdTransformerTest extends TestCase
{
    private $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
    }

    public function testReverseTransformWhenPassing0AsId(): void
    {
        $transformer = new ModelToIdTransformer($this->modelManager, 'TEST');

        $this->modelManager
                ->expects(static::exactly(2))
                ->method('find')
                ->willReturn(true);

        static::assertFalse(\in_array(false, ['0', 0], true));

        // we pass 0 as integer
        static::assertTrue($transformer->reverseTransform(0));

        // we pass 0 as string
        static::assertTrue($transformer->reverseTransform('0'));

        // we pass null must return null
        static::assertNull($transformer->reverseTransform(null));

        // we pass false, must return null
        static::assertNull($transformer->reverseTransform(false));
    }

    /**
     * @dataProvider getReverseTransformValues
     */
    public function testReverseTransform($value, $expected): void
    {
        $transformer = new ModelToIdTransformer($this->modelManager, 'TEST2');

        $this->modelManager->method('find');

        static::assertSame($expected, $transformer->reverseTransform($value));
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
        $this->modelManager->expects(static::once())
            ->method('getNormalizedIdentifier')
            ->willReturn(123);

        $transformer = new ModelToIdTransformer($this->modelManager, 'TEST');

        static::assertNull($transformer->transform(null));
        static::assertNull($transformer->transform(false));
        static::assertNull($transformer->transform(0));
        static::assertNull($transformer->transform('0'));

        static::assertSame(123, $transformer->transform(new \stdClass()));
    }
}
