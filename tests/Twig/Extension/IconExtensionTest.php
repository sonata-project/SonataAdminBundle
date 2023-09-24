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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Twig\Extension\IconExtension;
use Sonata\AdminBundle\Twig\IconRuntime;

/**
 * NEXT_MAJOR: Remove this test.
 *
 * @group legacy
 */
final class IconExtensionTest extends TestCase
{
    /**
     * @dataProvider provideParseIconCases
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function testParseIcon(string $icon, string $expected): void
    {
        $twigExtension = new IconExtension(new IconRuntime());

        static::assertSame($expected, $twigExtension->parseIcon($icon));
    }

    public function provideParseIconCases(): iterable
    {
        yield ['', ''];
        yield ['<i class="fa fa-cog" aria-hidden="true"></i>', '<i class="fa fa-cog" aria-hidden="true"></i>'];
        yield ['fa fa-cog', '<i class="fa fa-cog" aria-hidden="true"></i>'];
        yield ['far fa-cog', '<i class="far fa-cog" aria-hidden="true"></i>'];
        yield ['fas fa-cog', '<i class="fas fa-cog" aria-hidden="true"></i>'];
        yield ['fab fa-font-awesome', '<i class="fab fa-font-awesome" aria-hidden="true"></i>'];
        yield ['fal fa-cog', '<i class="fal fa-cog" aria-hidden="true"></i>'];
        yield ['fad fa-cog', '<i class="fad fa-cog" aria-hidden="true"></i>'];
    }
}
