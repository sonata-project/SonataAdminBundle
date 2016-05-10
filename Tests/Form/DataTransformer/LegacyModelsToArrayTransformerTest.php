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

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class LegacyModelsToArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $choiceList;
    private $modelChoiceList;

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

        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

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

                return array();
            }));

        $this->choiceList->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnCallback(function () use ($identifiers) {
                return $identifiers;
            }));

        $this->choiceList->expects($this->any())
            ->method('getEntities')
            ->will($this->returnCallback(function () {
                return array('bcd' => new FooEntity(array('bcd')), 'efg' => new FooEntity(array('efg')), 'abc' => new FooEntity(array('abc')));
            }));

        $this->assertSame($expected, $transformer->transform($collection));
    }

    public function getTransformTests()
    {
        return array(
            array(array(), null, array()),
            array(array(), array(), array()),
            array(array('id'), array(new FooEntity()), array('id')),
            array(array('id', 'id'), array(new FooEntity(), new FooEntity()), array('id')),
            array(array('abc', 'bcd', 'efg'), array(new FooEntity(array('abc')), new FooEntity(array('bcd')), new FooEntity(array('efg'))), array('id1', 'id2')),
        );
    }

    public function testReverseTransformWithException1()
    {
        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException', 'Expected argument of type "\ArrayAccess", "NULL" given');

        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->will($this->returnValue(null));

        $transformer->reverseTransform(array());
    }

    public function testReverseTransformWithException2()
    {
        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException', 'Expected argument of type "array", "integer" given');

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
        return array(
            array(null),
            array(''),
        );
    }

    public function testReverseTransform()
    {
        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->will($this->returnValue(new ArrayCollection()));

        $entity1 = new FooEntity(array('foo'));
        $entity2 = new FooEntity(array('bar'));
        $entity3 = new FooEntity(array('baz'));

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

        $collection = $transformer->reverseTransform(array('foo', 'bar'));
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $collection);
        $this->assertSame(array($entity1, $entity2), $collection->getValues());
        $this->assertCount(2, $collection);
    }

    public function testReverseTransformWithNonexistentEntityKey()
    {
        $this->setExpectedException('Symfony\Component\Form\Exception\TransformationFailedException', 'The entities with keys "nonexistent" could not be found');

        $transformer = new LegacyModelsToArrayTransformer($this->choiceList);

        $this->modelManager->expects($this->any())
            ->method('getModelCollectionInstance')
            ->will($this->returnValue(new ArrayCollection()));

        $this->choiceList->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue(false));

        $transformer->reverseTransform(array('nonexistent'));
    }
}
