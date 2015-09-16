<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Mapper;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;

/**
 * Test for BaseMapperTest.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class BaseMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaseMapper
     */
    protected $baseMapper;

    /**
     * @var AdminInterface
     */
    protected $admin;

    public function setUp()
    {
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $builder = $this->getMock('Sonata\AdminBundle\Builder\BuilderInterface');

        $this->baseMapper = $this->getMockForAbstractClass('Sonata\AdminBundle\Mapper\BaseMapper', array($builder, $this->admin));
    }

    public function testGetAdmin()
    {
        $this->assertSame($this->admin, $this->baseMapper->getAdmin());
    }
}
