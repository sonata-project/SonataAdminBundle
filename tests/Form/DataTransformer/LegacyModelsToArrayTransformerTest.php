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

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\DataTransformer\LegacyModelsToArrayTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class LegacyModelsToArrayTransformerTest extends TestCase
{
    private $choiceList;
    private $modelManager;

    /**
     * @group legacy
     */
    public function setUp(): void
    {
        if (!class_exists(SimpleChoiceList::class)) {
            $this->markTestSkipped('Test only available for < SF2.8');
        }

        $this->choiceList = $this->getMockBuilder(ModelChoiceList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);

        $this->choiceList->expects($this->any())
            ->method('getModelManager')
            ->willReturnCallback(function () {
                return $this->modelManager;
            });
    }

    /**
     * @dataProvider getTransformTests
     */
    public function testTransform($expected, $collection, $identifiers): void
    {
        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->choiceList->expects($this->any())
            ->method('getIdentifierValues')
            ->willReturnCallback(static function ($entity) use ($identifiers) {
                if ($entity instanceof FooEntity) {
                    return $identifiers;
                }

                return [];
            });

        $this->choiceList->expects($this->any())
            ->method('getIdentifier')
            ->willReturnCallback(static function () use ($identifiers) {
                return $identifiers;
            });

        $this->choiceList->expects($this->any())
            ->method('getEntities')
            ->willReturnCallback(static function () {
                return ['bcd' => new FooEntity(['bcd']), 'efg' => new FooEntity(['efg']), 'abc' => new FooEntity(['abc'])];
            });

        $this->assertSame($expected, $transformer->transform($collection));
    }

    public function getTransformTests()
    {
        return [
            [[], null, []],
            [[], [], []],
            [['id'], [new FooEntity()], ['id']],
            [['id', 'id'], [new FooEntity(), new FooEntity()], ['id']],
            [['abc', 'bcd', 'efg'], [new FooEntity(['abc']), new FooEntity(['bcd']), new FooEntity(['efg'])], ['id1', 'id2']],
        ];
    }

    public function testReverseTransformWithException1(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "\ArrayAccess", "NULL" given');

        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->willReturn(null);

        $transformer->reverseTransform([]);
    }

    public function testReverseTransformWithException2(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "array", "integer" given');

        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->willReturn(new ArrayCollection());

        $transformer->reverseTransform(123);
    }

    /**
     * @dataProvider getReverseTransformEmptyTests
     */
    public function testReverseTransformEmpty($keys): void
    {
        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->willReturn(new ArrayCollection());

        $this->assertInstanceOf(ArrayCollection::class, $transformer->reverseTransform($keys));
    }

    public function getReverseTransformEmptyTests()
    {
        return [
            [null],
            [''],
        ];
    }

    public function testReverseTransform(): void
    {
        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->willReturn(new ArrayCollection());

        $entity1 = new FooEntity(['foo']);
        $entity2 = new FooEntity(['bar']);
        $entity3 = new FooEntity(['baz']);

        $this->choiceList->expects($this->any())
            ->method('getEntity')
            ->willReturnCallback(static function ($key) use ($entity1, $entity2, $entity3) {
                switch ($key) {
                    case 'foo':
                        return $entity1;

                    case 'bar':
                        return $entity2;

                    case 'baz':
                        return $entity3;
                }
            });

        $collection = $transformer->reverseTransform(['foo', 'bar']);
        $this->assertInstanceOf(ArrayCollection::class, $collection);
        $this->assertSame([$entity1, $entity2], $collection->getValues());
        $this->assertCount(2, $collection);
    }

    public function testReverseTransformWithNonexistentEntityKey(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('The entities with keys "nonexistent" could not be found');

        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->willReturn(new ArrayCollection());

        $this->choiceList->expects($this->any())
            ->method('getEntity')
            ->willReturn(false);

        $transformer->reverseTransform(['nonexistent']);
    }
}
