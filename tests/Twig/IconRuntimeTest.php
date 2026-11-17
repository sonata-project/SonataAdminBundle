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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Twig\IconRuntime;

final class IconRuntimeTest extends TestCase
{
    #[DataProvider('provideParseIconCases')]
    public function testParseIcon(string $icon, string $expected): void
    {
        $iconRuntime = new IconRuntime();

        static::assertSame($expected, $iconRuntime->parseIcon($icon));
    }

    /**
     * @return iterable<array{string, string}>
     */
    public static function provideParseIconCases(): iterable
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

    public function testParseIconWithUnsupportedFormat(): void
    {
        $iconRuntime = new IconRuntime();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The icon format "bi bi-house" is not supported.');

        $iconRuntime->parseIcon('bi bi-house');
    }

    #[DataProvider('provideParseIconWithCustomPrefixesCases')]
    public function testParseIconWithCustomPrefixes(string $icon, string $expected): void
    {
        $iconRuntime = new IconRuntime(['bi', 'fa-solid']);

        static::assertSame($expected, $iconRuntime->parseIcon($icon));
    }

    /**
     * @return iterable<array{string, string}>
     */
    public static function provideParseIconWithCustomPrefixesCases(): iterable
    {
        yield ['', ''];
        yield ['bi bi-house', '<i class="bi bi-house" aria-hidden="true"></i>'];
        yield ['fa-solid fa-house', '<i class="fa-solid fa-house" aria-hidden="true"></i>'];
        yield ['<i class="fas fa-cog" aria-hidden="true"></i>', '<i class="fas fa-cog" aria-hidden="true"></i>'];
    }

    #[DataProvider('provideParseIconWithCustomPrefixesAndUnsupportedFormatCases')]
    public function testParseIconWithCustomPrefixesAndUnsupportedFormat(string $icon): void
    {
        $iconRuntime = new IconRuntime(['bi']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('The icon format "%s" is not supported.', $icon));

        $iconRuntime->parseIcon($icon);
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideParseIconWithCustomPrefixesAndUnsupportedFormatCases(): iterable
    {
        yield ['fas fa-cog'];
        yield ['ti ti-home'];
        yield ['bi-house'];
        yield ['bi'];
    }

    public function testParseIconWithoutPrefixes(): void
    {
        $iconRuntime = new IconRuntime([]);

        static::assertSame('<i class="bi bi-house"></i>', $iconRuntime->parseIcon('<i class="bi bi-house"></i>'));

        $this->expectException(\InvalidArgumentException::class);

        $iconRuntime->parseIcon('fas fa-cog');
    }
}
