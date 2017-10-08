<?php

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
use Sonata\AdminBundle\Form\DataTransformer\LegacyModelsToArrayTransformer;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity;
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class LegacyModelsToArrayTransformerTest extends PHPUnit_Framework_TestCase
{
    private $choiceList;
    private $modelManager;

    /**
     * @group legacy
     */
    public function setUp()
    {
        if (interface_exists('Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface')) { // SF2.7+
            $this->markTestSkipped('Test only available for < SF2.7');
        }

        $this->choiceList = $this->getMockBuilder('Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList')
            ->disableOriginalConstructor()
            ->getMock();

        $this->modelManager = $this->getMockForAbstractClass('Sonata\AdminBundle\Model\ModelManagerInterface');

        // php 5.3 BC
        $modelManager = $this->modelManager;

        $this->choiceList->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnCallback(function () use ($modelManager) {
                return $modelManager;
            }));
    }

    /**
     * @dataProvider getTransformTests
     */
    public function testTransform($expected, $collection, $identifiers)
    {
        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->choiceList->expects($this->any())
            ->method('getIdentifierValues')
            ->will($this->returnCallback(function ($entity) use ($identifiers) {
                if ($entity instanceof FooEntity) {
                    return $identifiers;
                }

                return [];
            }));

        $this->choiceList->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnCallback(function () use ($identifiers) {
                return $identifiers;
            }));

        $this->choiceList->expects($this->any())
            ->method('getEntities')
            ->will($this->returnCallback(function () {
                return ['bcd' => new FooEntity(['bcd']), 'efg' => new FooEntity(['efg']), 'abc' => new FooEntity(['abc'])];
            }));

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

    public function testReverseTransformWithException1()
    {
        $this->expectException('Symfony\Component\Form\Exception\UnexpectedTypeException', 'Expected argument of type "\ArrayAccess", "NULL" given');

        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->will($this->returnValue(null));

        $transformer->reverseTransform([]);
    }

    public function testReverseTransformWithException2()
    {
        $this->expectException('Symfony\Component\Form\Exception\UnexpectedTypeException', 'Expected argument of type "array", "integer" given');

        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->will($this->returnValue(new ArrayCollection()));

        $transformer->reverseTransform(123);
    }

    /**
     * @dataProvider getReverseTransformEmptyTests
     */
    public function testReverseTransformEmpty($keys)
    {
        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->will($this->returnValue(new ArrayCollection()));

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $transformer->reverseTransform($keys));
    }

    public function getReverseTransformEmptyTests()
    {
        return [
            [null],
            [''],
        ];
    }

    public function testReverseTransform()
    {
        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->will($this->returnValue(new ArrayCollection()));

        $entity1 = new FooEntity(['foo']);
        $entity2 = new FooEntity(['bar']);
        $entity3 = new FooEntity(['baz']);

        $this->choiceList->expects($this->any())
            ->method('getEntity')
            ->will($this->returnCallback(function ($key) use ($entity1, $entity2, $entity3) {
                switch ($key) {
                    case 'foo':
                        return $entity1;

                    case 'bar':
                        return $entity2;

                    case 'baz':
                        return $entity3;
                }

                return;
            }));

        $collection = $transformer->reverseTransform(['foo', 'bar']);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $collection);
        $this->assertSame([$entity1, $entity2], $collection->getValues());
        $this->assertCount(2, $collection);
    }

    public function testReverseTransformWithNonexistentEntityKey()
    {
        $this->expectException('Symfony\Component\Form\Exception\TransformationFailedException', 'The entities with keys "nonexistent" could not be found');

        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->will($this->returnValue(new ArrayCollection()));

        $this->choiceList->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue(false));

        $transformer->reverseTransform(['nonexistent']);
    }
}
