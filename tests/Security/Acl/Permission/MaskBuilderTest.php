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

namespace Sonata\AdminBundle\Tests\Security\Acl\Permission;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;

class MaskBuilderTest extends TestCase
{
    public function testGetPattern(): void
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
