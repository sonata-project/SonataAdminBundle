<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Mapper;

use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

/**
 * Test for BaseGroupedMapper
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class BaseGroupedMapperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BaseGroupedMapper
     */
    protected $baseGroupedMapper;

    public function setUp()
    {
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $builder = $this->getMock('Sonata\AdminBundle\Builder\BuilderInterface');

        $this->baseGroupedMapper = $this->getMockForAbstractClass('Sonata\AdminBundle\Mapper\BaseGroupedMapper', array($builder, $admin));
    }

    public function testWith()
    {
        $this->assertEquals($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
    }

    public function testEnd()
    {
        $this->assertEquals($this->baseGroupedMapper, $this->baseGroupedMapper->with('fooGroup'));
    }
}
