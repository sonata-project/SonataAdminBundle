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
    public function testValidateUsername($expected, $value): void
    {
        $this->assertSame($expected, Validators::validateUsername($value));
    }

    public function getValidateUsernameTests()
    {
        return [
            ['Foo', 'Foo'],
            ['abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789', 'abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789'],
        ];
    }

    /**
     * @dataProvider getValidateUsernameWithExceptionTests
     */
    public function testValidateUsernameWithException($value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateUsername($value);
    }

    public function getValidateUsernameWithExceptionTests()
    {
        return [
            [null],
        ];
    }

    /**
     * @dataProvider getValidateEntityNameTests
     */
    public function testValidateEntityName($expected, $value): void
    {
        $this->assertSame($expected, Validators::validateEntityName($value));
    }

    public function getValidateEntityNameTests()
    {
        return [
            [['AcmeBlogBundle', 'Post'], 'AcmeBlogBundle:Post'],
            [['Foo\Bar\BlogBundle', 'Post'], 'Foo/Bar/BlogBundle:Post'],
            [['Foo\Bar\BlogBundle', 'Post'], 'Foo\Bar\BlogBundle:Post'],
        ];
    }

    /**
     * @dataProvider getValidateEntityNamesWithExceptionTests
     */
    public function testValidateEntityNameWithException($value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateEntityName($value);
    }

    public function getValidateEntityNamesWithExceptionTests()
    {
        return [
            ['Sonata\AdminBundle\Admin\AbstractAdmin'],
            ['Sonata/AdminBundle/Admin/Admin'],
            ['Foo/Bar/Controller'],
            ['Foo/BarController'],
            ['Foo_Bar'],
            ['FooBarController'],
            ['FooBarAdmin'],
        ];
    }

    /**
     * @dataProvider getValidateClassTests
     */
    public function testValidateClass($expected, $value): void
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
    public function testValidateClassWithException($value): void
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
    public function testValidateAdminClassBasename($expected, $value): void
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
    public function testValidateAdminClassBasenameWithException($value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateAdminClassBasename($value);
    }

    public function getValidateAdminClassBasenameWithExceptionTests()
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
    public function testValidateControllerClassBasename($expected, $value): void
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
    public function testValidateControllerClassBasenameWithException($value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateControllerClassBasename($value);
    }

    public function getValidateControllerClassBasenameWithExceptionTests()
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
    public function testValidateServicesFile($expected, $value): void
    {
        $this->assertSame($expected, Validators::validateServicesFile($value));
    }

    public function getValidateServicesFileTests()
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
    public function testValidateServiceId($value): void
    {
        $this->assertSame($value, Validators::validateServiceId($value));
    }

    public function getValidateServiceIdTests()
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
    public function testValidateServiceIdWithException($value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Validators::validateServiceId($value);
    }

    public function getValidateServiceIdWithExceptionTests()
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
