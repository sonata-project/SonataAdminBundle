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

namespace Sonata\AdminBundle\Tests\Twig;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Twig\PagerRuntime;

final class PagerRuntimeTest extends TestCase
{
    public function testIsDeterministicReturnsTrueWhenPagerIsDeterministic(): void
    {
        static::assertTrue((new PagerRuntime())->isDeterministic($this->createPager(true)));
    }

    public function testIsDeterministicReturnsFalseWhenPagerIsNotDeterministic(): void
    {
        static::assertFalse((new PagerRuntime())->isDeterministic($this->createPager(false)));
    }

    /**
     * BC layer: a pager implemented before `isDeterministic()` was introduced is
     * considered deterministic.
     */
    public function testIsDeterministicReturnsTrueWhenMethodIsMissing(): void
    {
        $pager = $this->createMock(PagerInterface::class);

        static::assertFalse(method_exists($pager, 'isDeterministic'));
        static::assertTrue((new PagerRuntime())->isDeterministic($pager));
    }

    /**
     * @return Pager<ProxyQueryInterface<object>>&MockObject
     */
    private function createPager(bool $deterministic): Pager
    {
        /** @var Pager<ProxyQueryInterface<object>>&MockObject $pager */
        $pager = $this->createMock(Pager::class);
        $pager->method('isDeterministic')->willReturn($deterministic);

        return $pager;
    }
}
