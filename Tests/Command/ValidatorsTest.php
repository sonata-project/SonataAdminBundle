<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Command;

use Sonata\AdminBundle\Command\Validators;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ValidatorsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidateUsernameTests
     */
    public function testValidateUsername($expected, $value)
    {
        $this->assertEquals($expected, Validators::validateUsername($value));
    }

    public function getValidateUsernameTests()
    {
        return array(
            array('Foo', 'Foo'),
            array('abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789', 'abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789'),
        );
    }

    /**
     * @dataProvider getValidateUsernameWithExceptionTests
     */
    public function testValidateUsernameWithException($value)
    {
        $this->setExpectedException('\InvalidArgumentException');

        Validators::validateUsername($value);
    }

    public function getValidateUsernameWithExceptionTests()
    {
        return array(
            array(null),
        );
    }

    /**
     * @dataProvider getValidateEntityNameTests
     */
    public function testValidateEntityName($expected, $value)
    {
        $this->assertEquals($expected, Validators::validateEntityName($value));
    }

    public function getValidateEntityNameTests()
    {
        return array(
            array(array('AcmeBlogBundle', 'Post'), 'AcmeBlogBundle:Post'),
            array(array('Foo\Bar\BlogBundle', 'Post'), 'Foo/Bar/BlogBundle:Post'),
            array(array('Foo\Bar\BlogBundle', 'Post'), 'Foo\Bar\BlogBundle:Post'),
        );
    }

    /**
     * @dataProvider getValidateEntityNamesWithExceptionTests
     */
    public function testValidateEntityNameWithException($value)
    {
        $this->setExpectedException('\InvalidArgumentException');

        Validators::validateEntityName($value);
    }

    public function getValidateEntityNamesWithExceptionTests()
    {
        return array(
            array('Sonata\AdminBundle\Admin\Admin'),
            array('Sonata/AdminBundle/Admin/Admin'),
            array('Foo/Bar/Controller'),
            array('Foo/BarController'),
            array('Foo_Bar'),
            array('FooBarController'),
            array('FooBarAdmin'),
        );
    }

    /**
     * @dataProvider getValidateClassTests
     */
    public function testValidateClass($expected, $value)
    {
        $this->assertEquals($expected, Validators::validateClass($value));
    }

    public function getValidateClassTests()
    {
        return array(
            array('Sonata\AdminBundle\Admin\Admin', 'Sonata\AdminBundle\Admin\Admin'),
            array('Sonata\AdminBundle\Admin\Admin', 'Sonata/AdminBundle/Admin/Admin'),
        );
    }

    /**
     * @dataProvider getValidateClassWithExceptionTests
     */
    public function testValidateClassWithException($value)
    {
        $this->setExpectedException('\InvalidArgumentException');

        Validators::validateClass($value);
    }

    public function getValidateClassWithExceptionTests()
    {
        return array(
            array('Foo:BarAdmin'),
            array('Foo:Bar:Admin'),
            array('Foo/Bar/Admin'),
        );
    }

    /**
     * @dataProvider getValidateAdminClassBasenameTests
     */
    public function testValidateAdminClassBasename($expected, $value)
    {
        $this->assertEquals($expected, Validators::validateAdminClassBasename($value));
    }

    public function getValidateAdminClassBasenameTests()
    {
        return array(
            array('FooBarAdmin', 'FooBarAdmin'),
            array('Foo\Foo\BarAdmin', 'Foo\Foo\BarAdmin'),
            array('Foo\Foo\BarAdmin', 'Foo/Foo/BarAdmin'),
        );
    }

    /**
     * @dataProvider getValidateAdminClassBasenameWithExceptionTests
     */
    public function testValidateAdminClassBasenameWithException($value)
    {
        $this->setExpectedException('\InvalidArgumentException');

        Validators::validateAdminClassBasename($value);
    }

    public function getValidateAdminClassBasenameWithExceptionTests()
    {
        return array(
            array('Foo:BarAdmin'),
            array('Foo:Bar:Admin'),
            array('*+-!:@&^%'),
        );
    }

    /**
     * @dataProvider getValidateControllerClassBasenameTests
     */
    public function testValidateControllerClassBasename($expected, $value)
    {
        $this->assertEquals($expected, Validators::validateControllerClassBasename($value));
    }

    public function getValidateControllerClassBasenameTests()
    {
        return array(
            array('FooBarController', 'FooBarController'),
            array('Foo\Foo\BarController', 'Foo/Foo/BarController'),
            array('Foo\Foo\BarController', 'Foo\Foo\BarController'),
        );
    }

    /**
     * @dataProvider getValidateControllerClassBasenameWithExceptionTests
     */
    public function testValidateControllerClassBasenameWithException($value)
    {
        $this->setExpectedException('\InvalidArgumentException');

        Validators::validateControllerClassBasename($value);
    }

    public function getValidateControllerClassBasenameWithExceptionTests()
    {
        return array(
            array(' foobar '),
            array(' FooBar'),
            array('Foo Bar'),
            array('Foo-Bar'),
            array('foo*'),
            array('foo+'),
            array('foo-'),
            array('foo!'),
            array('foo@'),
            array('foo&'),
            array('foo%'),
            array('foo^'),
            array('foo(bar)'),
            array('foo[bar]'),
            array('foo{bar}'),
            array('Foo/Bar'),
            array('Foo\Bar'),
            array('Foo/BarControllr'),
            array('Foo\BarControllr'),
            array('Foo:BarControllr'),
        );
    }

    /**
     * @dataProvider getValidateServicesFileTests
     */
    public function testValidateServicesFile($expected, $value)
    {
        $this->assertEquals($expected, Validators::validateServicesFile($value));
    }

    public function getValidateServicesFileTests()
    {
        return array(
            array('foobar', 'foobar'),
            array('fooBar', 'fooBar'),
            array(' foo Bar ', ' foo Bar '),
            array('Foo/Bar', '/Foo/Bar/'),
            array('Foo/BAR', '/Foo/BAR/'),
            array('Foo/Bar', '/Foo/Bar'),
            array('Foo/Bar', 'Foo/Bar/'),
        );
    }

    /**
     * @dataProvider getValidateServiceIdTests
     */
    public function testValidateServiceId($value)
    {
        $this->assertEquals($value, Validators::validateServiceId($value));
    }

    public function getValidateServiceIdTests()
    {
        return array(
            array('abcdefghijklmnopqrstuvwxyz.ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789'),
            array('Foo_Bar_0123'),
            array('Foo.Bar.0123'),
        );
    }

    /**
     * @dataProvider getValidateServiceIdWithExceptionTests
     */
    public function testValidateServiceIdWithException($value)
    {
        $this->setExpectedException('\InvalidArgumentException');

        Validators::validateServiceId($value);
    }

    public function getValidateServiceIdWithExceptionTests()
    {
        return array(
            array(' foobar '),
            array(' FooBar'),
            array('Foo Bar'),
            array('Foo-Bar'),
            array('foo*'),
            array('foo+'),
            array('foo-'),
            array('foo!'),
            array('foo@'),
            array('foo&'),
            array('foo%'),
            array('foo^'),
            array('foo:'),
            array('foo(bar)'),
            array('foo[bar]'),
            array('foo{bar}'),
            array('Foo/Bar'),
            array('Foo\Bar'),
        );
    }
}
