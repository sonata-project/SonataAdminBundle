<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin\Security\Acl\Permission;

use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;

class MaskBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPattern()
    {
        $builder = new MaskBuilder();
        $this->assertSame(MaskBuilder::ALL_OFF, $builder->getPattern());

        $builder->add('view');
        $this->assertSame(str_repeat('.', 31).'V', $builder->getPattern());

        $builder->add('owner');
        $this->assertSame(str_repeat('.', 24).'N......V', $builder->getPattern());

        $builder->add('list');
        $this->assertSame(str_repeat('.', 19).'L....N......V', $builder->getPattern());

        $builder->add('export');
        $this->assertSame(str_repeat('.', 18).'EL....N......V', $builder->getPattern());

        $builder->add(1 << 10);
        $this->assertSame(str_repeat('.', 18).'EL.'.MaskBuilder::ON.'..N......V', $builder->getPattern());
    }
}
