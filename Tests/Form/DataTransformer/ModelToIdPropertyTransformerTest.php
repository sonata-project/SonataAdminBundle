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
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooArrayAccess;

class ModelToIdPropertyTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $modelManager = null;

    public function setUp()
    {
        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
    }

    public function testReverseTransform()
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', false);

        $entity = new Foo();
        $entity->setBar('example');

        $this->modelManager
            ->expects($this->any())
            ->method('find')
            ->with($this->equalTo('Sonata\AdminBundle\Tests\Fixtures\Entity\Foo'), $this->equalTo(123))
            ->will($this->returnValue($entity));

        $this->assertNull($transformer->reverseTransform(null));
        $this->assertNull($transformer->reverseTransform(false));
        $this->assertNull($transformer->reverseTransform(12));
        $this->assertSame($entity, $transformer->reverseTransform(array('identifiers' => array(123), 'titles' => array('example'))));
    }

    /**
     * @dataProvider getReverseTransformMultipleTests
     */
    public function testReverseTransformMultiple($expected, $params, $entity1, $entity2, $entity3)
    {
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', true);

        $this->modelManager
            ->expects($this->any())
            ->method('find')
            ->will($this->returnCallback(function ($className, $value) use ($entity1, $entity2, $entity3) {
                if ($className != 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo') {
                    return;
                }

                if ($value == 123) {
                    return $entity1;
                }

                if ($value == 456) {
                    return $entity2;
                }

                if ($value == 789) {
                    return $entity3;
                }

                return;
            }));

        $collection = new ArrayCollection();
        $this->modelManager
            ->expects($this->any())
            ->method('getModelCollectionInstance')
            ->with($this->equalTo('Sonata\AdminBundle\Tests\Fixtures\Entity\Foo'))
            ->will($this->returnValue($collection));

        $result = $transformer->reverseTransform($params);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $result);
        $this->assertSame($expected, $result->getValues());
    }

    public function getReverseTransformMultipleTests()
    {
        $entity1 = new Foo();
        $entity1->setBaz(123);
        $entity1->setBar('example');

        $entity2 = new Foo();
        $entity2->setBaz(456);
        $entity2->setBar('example2');

        $entity3 = new Foo();
        $entity3->setBaz(789);
        $entity3->setBar('example3');

        return array(
            array(array(), null, $entity1, $entity2, $entity3),
            array(array(), false, $entity1, $entity2, $entity3),
            array(array(), true, $entity1, $entity2, $entity3),
            array(array(), 12, $entity1, $entity2, $entity3),
            array(array($entity1), array('identifiers' => array(123), 'titles' => array('example')), $entity1, $entity2, $entity3),
            array(array($entity1, $entity2, $entity3), array('identifiers' => array(123, 456, 789), 'titles' => array('example', 'example2', 'example3')), $entity1, $entity2, $entity3),
        );
    }

    public function testTransform()
    {
        $entity = new Foo();
        $entity->setBar('example');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->will($this->returnValue(array(123)));

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', false);

        $this->assertSame(array('identifiers' => array(), 'labels' => array()), $transformer->transform(null));
        $this->assertSame(array('identifiers' => array(), 'labels' => array()), $transformer->transform(false));
        $this->assertSame(array('identifiers' => array(), 'labels' => array()), $transformer->transform(0));
        $this->assertSame(array('identifiers' => array(), 'labels' => array()), $transformer->transform('0'));

        $this->assertSame(array('identifiers' => array(123), 'labels' => array('example')), $transformer->transform($entity));
    }

    public function testTransformWorksWithArrayAccessEntity()
    {
        $entity = new FooArrayAccess();
        $entity->setBar('example');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->will($this->returnValue(array(123)));

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\FooArrayAccess', 'bar', false);

        $this->assertSame(array('identifiers' => array(123), 'labels' => array('example')), $transformer->transform($entity));
    }

    public function testTransformToStringCallback()
    {
        $entity = new Foo();
        $entity->setBar('example');
        $entity->setBaz('bazz');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->will($this->returnValue(array(123)));

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', false, function ($entity) {
            return $entity->getBaz();
        });

        $this->assertSame(array('identifiers' => array(123), 'labels' => array('bazz')), $transformer->transform($entity));
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Callback in "to_string_callback" option doesn`t contain callable function.
     */
    public function testTransformToStringCallbackException()
    {
        $entity = new Foo();
        $entity->setBar('example');
        $entity->setBaz('bazz');

        $this->modelManager->expects($this->once())
            ->method('getIdentifierValues')
            ->will($this->returnValue(array(123)));

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', false, '987654');

        $transformer->transform($entity);
    }

    public function testTransformMultiple()
    {
        $entity1 = new Foo();
        $entity1->setBar('foo');

        $entity2 = new Foo();
        $entity2->setBar('bar');

        $entity3 = new Foo();
        $entity3->setBar('baz');

        $collection = new ArrayCollection();
        $collection[] = $entity1;
        $collection[] = $entity2;
        $collection[] = $entity3;

        $this->modelManager->expects($this->exactly(6))
            ->method('getIdentifierValues')
            ->will($this->returnCallback(function ($value) use ($entity1, $entity2, $entity3) {
                if ($value == $entity1) {
                    return array(123);
                }

                if ($value == $entity2) {
                    return array(456);
                }

                if ($value == $entity3) {
                    return array(789);
                }

                return array(999);
            }));

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', true);

        $this->assertSame(array('identifiers' => array(), 'labels' => array()), $transformer->transform(null));
        $this->assertSame(array('identifiers' => array(), 'labels' => array()), $transformer->transform(false));
        $this->assertSame(array('identifiers' => array(), 'labels' => array()), $transformer->transform(0));
        $this->assertSame(array('identifiers' => array(), 'labels' => array()), $transformer->transform('0'));

        $expected = array('identifiers' => array(123, 456, 789), 'labels' => array('foo', 'bar', 'baz'));
        $this->assertSame($expected, $transformer->transform($collection));
        $this->assertSame($expected, $transformer->transform($collection->toArray()));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.
     */
    public function testTransformCollectionException()
    {
        $entity = new Foo();
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', true);
        $transformer->transform($entity);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.
     */
    public function testTransformArrayAccessException()
    {
        $entity = new FooArrayAccess();
        $entity->setBar('example');
        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\FooArrayAccess', 'bar', true);
        $transformer->transform($entity);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage A single selection must be passed a single value not a collection. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.
     */
    public function testTransformEntityException()
    {
        $entity1 = new Foo();
        $entity1->setBar('foo');

        $entity2 = new Foo();
        $entity2->setBar('bar');

        $entity3 = new Foo();
        $entity3->setBar('baz');

        $collection = new ArrayCollection();
        $collection[] = $entity1;
        $collection[] = $entity2;
        $collection[] = $entity3;

        $transformer = new ModelToIdPropertyTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo', 'bar', false);

        $transformer->transform($collection);
    }
}
