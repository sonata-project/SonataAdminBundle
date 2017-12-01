<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AdminObjectAclManipulatorTest extends TestCase
{
    const MASK_BUILDER_CLASS = MaskBuilder::class;

    public function testGetMaskBuilder()
    {
        $adminObjectAclManipulator = $this->createAdminObjectAclManipulator();
        $this->assertSame(self::MASK_BUILDER_CLASS, $adminObjectAclManipulator->getMaskBuilderClass());
    }

    protected function createAdminObjectAclManipulator()
    {
        return new AdminObjectAclManipulator($this->getMockForAbstractClass(FormFactoryInterface::class), self::MASK_BUILDER_CLASS);
    }
}
