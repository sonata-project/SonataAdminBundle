<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\DataTransformer;

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ArrayToModelTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $modelManager = null;

    public function setUp()
    {
        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
    }

    public function testReverseTransformEntity()
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity');

        $entity = new FooEntity();
        $this->assertEquals($entity, $transformer->reverseTransform($entity));
    }

    /**
     * @dataProvider getReverseTransformTests
     */
    public function testReverseTransform($value)
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity');

        $this->modelManager->expects($this->any())
            ->method('modelReverseTransform')
            ->will($this->returnValue(new FooEntity()));

        $this->assertInstanceOf('Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity', $transformer->reverseTransform($value));
    }

    public function getReverseTransformTests()
    {
        return array(
            array('Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity'),
            array(array()),
            array(array('foo'=>'bar')),
            array('foo'),
            array(123),
            array(null),
            array(false),
        );
    }

    /**
     * @dataProvider getTransformTests
     */
    public function testTransform($expected, $value)
    {
        $transformer = new ArrayToModelTransformer($this->modelManager, 'Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity');

        $this->assertEquals($expected, $transformer->transform($value));
    }

    public function getTransformTests()
    {
        return array(
            array(123, 123),
            array('foo', 'foo'),
            array(false, false),
            array(null, null),
            array(0, 0),
        );
    }
}
