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

namespace Sonata\AdminBundle\Tests\Mapper;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\BuilderInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;

/**
 * Test for BaseMapperTest.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class BaseMapperTest extends TestCase
{
    /**
     * @var BaseMapper
     */
    protected $baseMapper;

    /**
     * @var AdminInterface
     */
    protected $admin;

    public function setUp(): void
    {
        $this->admin = $this->getMockForAbstractClass(AdminInterface::class);
        $builder = $this->getMockForAbstractClass(BuilderInterface::class);

        $this->baseMapper = $this->getMockForAbstractClass(BaseMapper::class, [$builder, $this->admin]);
    }

    public function testGetAdmin(): void
    {
        $this->assertSame($this->admin, $this->baseMapper->getAdmin());
    }
}
