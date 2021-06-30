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
        self::assertSame(MaskBuilder::ALL_OFF, $builder->getPattern());

        $builder->add('view');
        self::assertSame(sprintf('%sV', str_repeat('.', 31)), $builder->getPattern());

        $builder->add('owner');
        self::assertSame(sprintf('%sN......V', str_repeat('.', 24)), $builder->getPattern());

        $builder->add('list');
        self::assertSame(sprintf('%sL....N......V', str_repeat('.', 19)), $builder->getPattern());

        $builder->add('export');
        self::assertSame(sprintf('%sEL....N......V', str_repeat('.', 18)), $builder->getPattern());

        $builder->add(1 << 10);
        self::assertSame(sprintf('%sEL.%s..N......V', str_repeat('.', 18), MaskBuilder::ON), $builder->getPattern());
    }
}
