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

namespace Sonata\AdminBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Command\Validators;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ValidatorsTest extends TestCase
{
    /**
     * @dataProvider getValidateUsernameTests
     */
    public function testValidateUsername(string $expected, string $value): void
    {
        self::assertSame($expected, Validators::validateUsername($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function getValidateUsernameTests(): iterable
    {
        return [
            ['Foo', 'Foo'],
            ['abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789', 'abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789'],
        ];
    }

    public function testValidateUsernameWithException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateUsername(null);
    }

    /**
     * @dataProvider getValidateClassTests
     */
    public function testValidateClass(string $expected, string $value): void
    {
        self::assertSame($expected, Validators::validateClass($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function getValidateClassTests(): iterable
    {
        return [
            ['Sonata\AdminBundle\Admin\AbstractAdmin', 'Sonata\AdminBundle\Admin\AbstractAdmin'],
            ['Sonata\AdminBundle\Admin\AbstractAdmin', 'Sonata/AdminBundle/Admin/AbstractAdmin'],
        ];
    }

    /**
     * @dataProvider getValidateClassWithExceptionTests
     */
    public function testValidateClassWithException(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateClass($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function getValidateClassWithExceptionTests(): iterable
    {
        return [
            ['Foo:BarAdmin'],
            ['Foo:Bar:Admin'],
            ['Foo/Bar/Admin'],
        ];
    }

    /**
     * @dataProvider getValidateAdminClassBasenameTests
     */
    public function testValidateAdminClassBasename(string $expected, string $value): void
    {
        self::assertSame($expected, Validators::validateAdminClassBasename($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function getValidateAdminClassBasenameTests(): iterable
    {
        return [
            ['FooBarAdmin', 'FooBarAdmin'],
            ['Foo\Foo\BarAdmin', 'Foo\Foo\BarAdmin'],
            ['Foo\Foo\BarAdmin', 'Foo/Foo/BarAdmin'],
        ];
    }

    /**
     * @dataProvider getValidateAdminClassBasenameWithExceptionTests
     */
    public function testValidateAdminClassBasenameWithException(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateAdminClassBasename($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function getValidateAdminClassBasenameWithExceptionTests(): iterable
    {
        return [
            ['Foo:BarAdmin'],
            ['Foo:Bar:Admin'],
            ['*+-!:@&^%'],
        ];
    }

    /**
     * @dataProvider getValidateControllerClassBasenameTests
     */
    public function testValidateControllerClassBasename(string $expected, string $value): void
    {
        self::assertSame($expected, Validators::validateControllerClassBasename($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function getValidateControllerClassBasenameTests(): iterable
    {
        return [
            ['FooBarController', 'FooBarController'],
            ['Foo\Foo\BarController', 'Foo/Foo/BarController'],
            ['Foo\Foo\BarController', 'Foo\Foo\BarController'],
        ];
    }

    /**
     * @dataProvider getValidateControllerClassBasenameWithExceptionTests
     */
    public function testValidateControllerClassBasenameWithException(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateControllerClassBasename($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function getValidateControllerClassBasenameWithExceptionTests(): iterable
    {
        return [
            [' foobar '],
            [' FooBar'],
            ['Foo Bar'],
            ['Foo-Bar'],
            ['foo*'],
            ['foo+'],
            ['foo-'],
            ['foo!'],
            ['foo@'],
            ['foo&'],
            ['foo%'],
            ['foo^'],
            ['foo(bar)'],
            ['foo[bar]'],
            ['foo{bar}'],
            ['Foo/Bar'],
            ['Foo\Bar'],
            ['Foo/BarControllr'],
            ['Foo\BarControllr'],
            ['Foo:BarControllr'],
        ];
    }

    /**
     * @dataProvider getValidateServicesFileTests
     */
    public function testValidateServicesFile(string $expected, string $value): void
    {
        self::assertSame($expected, Validators::validateServicesFile($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function getValidateServicesFileTests(): iterable
    {
        return [
            ['foobar', 'foobar'],
            ['fooBar', 'fooBar'],
            [' foo Bar ', ' foo Bar '],
            ['Foo/Bar', '/Foo/Bar/'],
            ['Foo/BAR', '/Foo/BAR/'],
            ['Foo/Bar', '/Foo/Bar'],
            ['Foo/Bar', 'Foo/Bar/'],
        ];
    }

    /**
     * @dataProvider getValidateServiceIdTests
     */
    public function testValidateServiceId(string $value): void
    {
        self::assertSame($value, Validators::validateServiceId($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function getValidateServiceIdTests(): iterable
    {
        return [
            ['abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789'],
            ['Foo_Bar_0123'],
            ['Foo.Bar.0123'],
        ];
    }

    /**
     * @dataProvider getValidateServiceIdWithExceptionTests
     */
    public function testValidateServiceIdWithException(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateServiceId($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function getValidateServiceIdWithExceptionTests(): iterable
    {
        return [
            [' foobar '],
            [' FooBar'],
            ['Foo Bar'],
            ['Foo-Bar'],
            ['foo*'],
            ['foo+'],
            ['foo-'],
            ['foo!'],
            ['foo@'],
            ['foo&'],
            ['foo%'],
            ['foo^'],
            ['foo:'],
            ['foo(bar)'],
            ['foo[bar]'],
            ['foo{bar}'],
            ['Foo/Bar'],
            ['Foo\Bar'],
        ];
    }
}
