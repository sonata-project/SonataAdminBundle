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

final class IconExtensionTest extends TestCase
{
    /**
     * @dataProvider iconProvider
     */
    public function testParseIcon(string $icon, string $expected): void
    {
        $twigExtension = new IconExtension();

        $this->assertSame($expected, $twigExtension->parseIcon($icon));
    }

    /**
     * @return iterable<array{string, string}>
     */
    public function iconProvider(): iterable
    {
        return [
            ['', ''],
            ['<i class="fa fa-cog" aria-hidden="true"></i>', '<i class="fa fa-cog" aria-hidden="true"></i>'],
            ['fa fa-cog', '<i class="fa fa-cog" aria-hidden="true"></i>'],
            ['far fa-cog', '<i class="far fa-cog" aria-hidden="true"></i>'],
            ['fas fa-cog', '<i class="fas fa-cog" aria-hidden="true"></i>'],
            ['fal fa-cog', '<i class="fal fa-cog" aria-hidden="true"></i>'],
            ['fad fa-cog', '<i class="fad fa-cog" aria-hidden="true"></i>'],
        ];
    }
}
