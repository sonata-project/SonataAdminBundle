<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\DataTransformer;

use Sonata\AdminBundle\Form\Type\BooleanType;
use Sonata\AdminBundle\Form\DataTransformer\BooleanTypeToBooleanTransformer;

class BooleanTypeToBooleanTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getReverseTransformData
     */
    public function testReverseTransform($value, $expected)
    {
        $transformer = new BooleanTypeToBooleanTransformer();

        $this->assertEquals($expected, $transformer->transform($value));
    }

    public function testTransform()
    {
        $transformer = new BooleanTypeToBooleanTransformer();
        $this->assertTrue($transformer->reverseTransform(BooleanType::TYPE_YES));
        $this->assertTrue($transformer->reverseTransform(1));
        $this->assertFalse($transformer->reverseTransform('asd'));
        $this->assertFalse($transformer->reverseTransform(BooleanType::TYPE_NO));
    }

    public function getReverseTransformData()
    {
        return array(
            array(true, BooleanType::TYPE_YES),
            array(false, BooleanType::TYPE_NO),
            array("wrong", BooleanType::TYPE_NO),
            array("1", BooleanType::TYPE_YES),
            array("2", BooleanType::TYPE_NO),

            array("3", BooleanType::TYPE_NO), // default value is false ...
        );
    }
}