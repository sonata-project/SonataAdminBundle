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

final class MaskBuilderTest extends TestCase
{
    public function testGetPattern(): void
    {
        $builder = new MaskBuilder();
        static::assertSame(MaskBuilder::ALL_OFF, $builder->getPattern());

        $builder->add('view');
        static::assertSame(\sprintf('%sV', str_repeat('.', 31)), $builder->getPattern());

        $builder->add('owner');
        static::assertSame(\sprintf('%sN......V', str_repeat('.', 24)), $builder->getPattern());

        $builder->add('list');
        static::assertSame(\sprintf('%sL....N......V', str_repeat('.', 19)), $builder->getPattern());

        $builder->add('export');
        static::assertSame(\sprintf('%sEL....N......V', str_repeat('.', 18)), $builder->getPattern());

        $builder->add(1 << 10);
        static::assertSame(\sprintf('%sEL.%s..N......V', str_repeat('.', 18), MaskBuilder::ON), $builder->getPattern());
    }
}
