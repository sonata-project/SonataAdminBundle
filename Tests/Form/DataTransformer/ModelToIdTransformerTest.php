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

use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class ModelToIdTransformerTest extends PHPUnit_Framework_TestCase
{
    private $modelManager = null;

    public function setUp()
    {
        $this->modelManager = $this->getMockForAbstractClass('Sonata\AdminBundle\Model\ModelManagerInterface');
    }

    public function testReverseTransformWhenPassing0AsId()
    {
        $transformer = new ModelToIdTransformer($this->modelManager, 'TEST');

        $this->modelManager
                ->expects($this->exactly(2))
                ->method('find')
                ->will($this->returnValue(true));

        $this->assertFalse(in_array(false, array('0', 0), true));

        // we pass 0 as integer
        $this->assertTrue($transformer->reverseTransform(0));

        // we pass 0 as string
        $this->assertTrue($transformer->reverseTransform('0'));

        // we pass null must return null
        $this->assertNull($transformer->reverseTransform(null));

        // we pass false, must return null
        $this->assertNull($transformer->reverseTransform(false));
    }

    /**
     * @dataProvider getReverseTransformValues
     */
    public function testReverseTransform($value, $expected)
    {
        $transformer = new ModelToIdTransformer($this->modelManager, 'TEST2');

        $this->modelManager->expects($this->any())->method('find');

        $this->assertSame($expected, $transformer->reverseTransform($value));
    }

    public function getReverseTransformValues()
    {
        return array(
            array(null, null),
            array(false, null),
            array(array(), null),
            array('', null),
        );
    }

    public function testTransform()
    {
        $this->modelManager->expects($this->once())
            ->method('getNormalizedIdentifier')
            ->will($this->returnValue(123));

        $transformer = new ModelToIdTransformer($this->modelManager, 'TEST');

        $this->assertNull($transformer->transform(null));
        $this->assertNull($transformer->transform(false));
        $this->assertNull($transformer->transform(0));
        $this->assertNull($transformer->transform('0'));

        $this->assertSame(123, $transformer->transform(new \stdClass()));
    }
}
