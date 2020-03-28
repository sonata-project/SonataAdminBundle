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
use Sonata\AdminBundle\Twig\Extension\UnicodeString;
use Symfony\Component\String\Exception\InvalidArgumentException;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class UnicodeStringTest extends TestCase
{
    public function testCreateFromStringWithInvalidUtf8Input(): void
    {
        $this->expectException(InvalidArgumentException::class);

        static::createFromString("\xE9");
    }

    public function testAscii(): void
    {
        $s = static::createFromString('Dieser Wert sollte größer oder gleich');
        $this->assertSame('Dieser Wert sollte grosser oder gleich', (string) $s->ascii());
        $this->assertSame('Dieser Wert sollte groesser oder gleich', (string) $s->ascii(['de-ASCII']));
    }

    /**
     * @dataProvider provideTruncate
     */
    public function testTruncate(string $expected, string $origin, int $length, string $ellipsis, bool $cut = true): void
    {
        $instance = static::createFromString($origin)->truncate($length, $ellipsis, $cut);

        $this->assertSame((string) static::createFromString($expected), (string) $instance);
    }

    public static function provideTruncate(): iterable
    {
        yield from [
            ['', '', 3, ''],
            ['', 'foo', 0, '...'],
            ['foo', 'foo', 0, '...', false],
            ['fo', 'foobar', 2, ''],
            ['foobar', 'foobar', 10, ''],
            ['foobar', 'foobar', 10, '...', false],
            ['foo', 'foo', 3, '...'],
            ['fo', 'foobar', 2, '...'],
            ['...', 'foobar', 3, '...'],
            ['fo...', 'foobar', 5, '...'],
            ['foobar...', 'foobar foo', 6, '...', false],
            ['foobar...', 'foobar foo', 7, '...', false],
            ['foobar foo...', 'foobar foo a', 10, '...', false],
        ];
    }

    /**
     * @dataProvider wordwrapProvider
     */
    public function testWordwrap(string $expected, string $actual, int $length, string $break, bool $cut = false): void
    {
        $instance = static::createFromString($actual);
        $actual = $instance->wordwrap($length, $break, $cut);

        $this->assertSame((string) $expected, (string) $actual);
    }

    public function wordwrapProvider(): iterable
    {
        yield from [
            [
                'Lo-re-m-Ip-su-m',
                'Lorem Ipsum',
                2,
                '-',
                true,
            ],
            [
                'Lorem-Ipsum',
                'Lorem Ipsum',
                2,
                '-',
            ],
            [
                'Lor-em-Ips-um',
                'Lorem Ipsum',
                3,
                '-',
                true,
            ],
            [
                'L-o-r-e-m-I-p-s-u-m',
                'Lorem Ipsum',
                1,
                '-',
                true,
            ],
        ];
    }

    private static function createFromString(string $string): UnicodeString
    {
        return new UnicodeString($string);
    }
}
