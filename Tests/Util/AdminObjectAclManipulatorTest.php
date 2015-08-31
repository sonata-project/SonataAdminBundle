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

use Sonata\AdminBundle\Util\AdminObjectAclManipulator;

/**
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AdminObjectAclManipulatorTest extends \PHPUnit_Framework_TestCase
{
    const MASK_BUILDER_CLASS = '\Symfony\Component\Security\Acl\Permission\MaskBuilder';

    protected function createAdminObjectAclManipulator()
    {
        return new AdminObjectAclManipulator($this->getMock('Symfony\Component\Form\FormFactoryInterface'), self::MASK_BUILDER_CLASS);
    }

    public function testGetMaskBuilder()
    {
        $adminObjectAclManipulator = $this->createAdminObjectAclManipulator();
        $this->assertSame(self::MASK_BUILDER_CLASS, $adminObjectAclManipulator->getMaskBuilderClass());
    }
}
