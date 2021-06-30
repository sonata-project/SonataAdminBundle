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
     * @var ModelManagerInterface<object>&MockObject
     */
    private $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
    }

    /**
     * @param int|string $value
     *
     * @dataProvider getReverseTransformValues
     */
    public function testReverseTransform($value): void
    {
        $className = \stdClass::class;
        $transformer = new ModelToIdTransformer($this->modelManager, $className);

        $found = new \stdClass();
        $this->modelManager
            ->expects(self::once())
            ->method('find')
            ->willReturn($found);

        self::assertSame($found, $transformer->reverseTransform($value));
    }

    /**
     * @return array<array{int|string}>
     */
    public function getReverseTransformValues(): array
    {
        return [
            [0],
            ['0'],
        ];
    }

    public function testReverseTransformEmpty(): void
    {
        $className = \stdClass::class;
        $transformer = new ModelToIdTransformer($this->modelManager, $className);

        $this->modelManager->expects(self::never())->method('find');

        self::assertNull($transformer->reverseTransform(null));
        self::assertNull($transformer->reverseTransform(''));
    }

    public function testTransform(): void
    {
        $this->modelManager->expects(self::once())
            ->method('getNormalizedIdentifier')
            ->willReturn('123');

        $className = \stdClass::class;
        $transformer = new ModelToIdTransformer($this->modelManager, $className);

        self::assertNull($transformer->transform(null));

        self::assertSame('123', $transformer->transform(new \stdClass()));
    }
}
