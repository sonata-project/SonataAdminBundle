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
        $this->assertSame($expected, Validators::validateUsername($value));
    }

    public function getValidateUsernameTests(): array
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
        $this->assertSame($expected, Validators::validateClass($value));
    }

    public function getValidateClassTests()
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

    public function getValidateClassWithExceptionTests()
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
        $this->assertSame($expected, Validators::validateAdminClassBasename($value));
    }

    public function getValidateAdminClassBasenameTests()
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

    public function getValidateAdminClassBasenameWithExceptionTests(): array
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
        $this->assertSame($expected, Validators::validateControllerClassBasename($value));
    }

    public function getValidateControllerClassBasenameTests()
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

    public function getValidateControllerClassBasenameWithExceptionTests(): array
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
        $this->assertSame($expected, Validators::validateServicesFile($value));
    }

    public function getValidateServicesFileTests(): array
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
        $this->assertSame($value, Validators::validateServiceId($value));
    }

    public function getValidateServiceIdTests(): array
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

    public function getValidateServiceIdWithExceptionTests(): array
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
