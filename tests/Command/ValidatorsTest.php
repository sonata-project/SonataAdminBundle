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
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Command\Validators;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class ValidatorsTest extends TestCase
{
    /**
     * @dataProvider provideValidateUsernameCases
     */
    public function testValidateUsername(string $expected, string $value): void
    {
        static::assertSame($expected, Validators::validateUsername($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function provideValidateUsernameCases(): iterable
    {
        yield ['Foo', 'Foo'];
        yield ['abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789', 'abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789'];
    }

    public function testValidateUsernameWithException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateUsername(null);
    }

    /**
     * @dataProvider provideValidateClassCases
     */
    public function testValidateClass(string $expected, string $value): void
    {
        static::assertSame($expected, Validators::validateClass($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function provideValidateClassCases(): iterable
    {
        yield [AbstractAdmin::class, AbstractAdmin::class];
        yield [AbstractAdmin::class, 'Sonata/AdminBundle/Admin/AbstractAdmin'];
    }

    /**
     * @dataProvider provideValidateClassWithExceptionCases
     */
    public function testValidateClassWithException(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateClass($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideValidateClassWithExceptionCases(): iterable
    {
        yield ['Foo:BarAdmin'];
        yield ['Foo:Bar:Admin'];
        yield ['Foo/Bar/Admin'];
    }

    /**
     * @dataProvider provideValidateAdminClassBasenameCases
     */
    public function testValidateAdminClassBasename(string $expected, string $value): void
    {
        static::assertSame($expected, Validators::validateAdminClassBasename($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function provideValidateAdminClassBasenameCases(): iterable
    {
        yield ['FooBarAdmin', 'FooBarAdmin'];
        yield ['Foo\Foo\BarAdmin', 'Foo\Foo\BarAdmin'];
        yield ['Foo\Foo\BarAdmin', 'Foo/Foo/BarAdmin'];
    }

    /**
     * @dataProvider provideValidateAdminClassBasenameWithExceptionCases
     */
    public function testValidateAdminClassBasenameWithException(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateAdminClassBasename($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideValidateAdminClassBasenameWithExceptionCases(): iterable
    {
        yield ['Foo:BarAdmin'];
        yield ['Foo:Bar:Admin'];
        yield ['*+-!:@&^%'];
    }

    /**
     * @dataProvider provideValidateControllerClassBasenameCases
     */
    public function testValidateControllerClassBasename(string $expected, string $value): void
    {
        static::assertSame($expected, Validators::validateControllerClassBasename($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function provideValidateControllerClassBasenameCases(): iterable
    {
        yield ['FooBarController', 'FooBarController'];
        yield ['Foo\Foo\BarController', 'Foo/Foo/BarController'];
        yield ['Foo\Foo\BarController', 'Foo\Foo\BarController'];
    }

    /**
     * @dataProvider provideValidateControllerClassBasenameWithExceptionCases
     */
    public function testValidateControllerClassBasenameWithException(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateControllerClassBasename($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideValidateControllerClassBasenameWithExceptionCases(): iterable
    {
        yield [' foobar '];
        yield [' FooBar'];
        yield ['Foo Bar'];
        yield ['Foo-Bar'];
        yield ['foo*'];
        yield ['foo+'];
        yield ['foo-'];
        yield ['foo!'];
        yield ['foo@'];
        yield ['foo&'];
        yield ['foo%'];
        yield ['foo^'];
        yield ['foo(bar)'];
        yield ['foo[bar]'];
        yield ['foo{bar}'];
        yield ['Foo/Bar'];
        yield ['Foo\Bar'];
        yield ['Foo/BarControllr'];
        yield ['Foo\BarControllr'];
        yield ['Foo:BarControllr'];
    }

    /**
     * @dataProvider provideValidateServicesFileCases
     */
    public function testValidateServicesFile(string $expected, string $value): void
    {
        static::assertSame($expected, Validators::validateServicesFile($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string, string}>
     */
    public function provideValidateServicesFileCases(): iterable
    {
        yield ['foobar', 'foobar'];
        yield ['fooBar', 'fooBar'];
        yield [' foo Bar ', ' foo Bar '];
        yield ['Foo/Bar', '/Foo/Bar/'];
        yield ['Foo/BAR', '/Foo/BAR/'];
        yield ['Foo/Bar', '/Foo/Bar'];
        yield ['Foo/Bar', 'Foo/Bar/'];
    }

    /**
     * @dataProvider provideValidateServiceIdCases
     */
    public function testValidateServiceId(string $value): void
    {
        static::assertSame($value, Validators::validateServiceId($value));
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideValidateServiceIdCases(): iterable
    {
        yield ['abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789'];
        yield ['Foo_Bar_0123'];
        yield ['Foo.Bar.0123'];
    }

    /**
     * @dataProvider provideValidateServiceIdWithExceptionCases
     */
    public function testValidateServiceIdWithException(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateServiceId($value);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideValidateServiceIdWithExceptionCases(): iterable
    {
        yield [' foobar '];
        yield [' FooBar'];
        yield ['Foo Bar'];
        yield ['Foo-Bar'];
        yield ['foo*'];
        yield ['foo+'];
        yield ['foo-'];
        yield ['foo!'];
        yield ['foo@'];
        yield ['foo&'];
        yield ['foo%'];
        yield ['foo^'];
        yield ['foo:'];
        yield ['foo(bar)'];
        yield ['foo[bar]'];
        yield ['foo{bar}'];
        yield ['Foo/Bar'];
        yield ['Foo\Bar'];
    }
}
