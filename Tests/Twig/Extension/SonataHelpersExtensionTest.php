<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use Sonata\AdminBundle\Twig\Extension\SonataHelpersExtension;
use Symfony\Bridge\Twig\Tests\Extension\Fixtures\StubFilesystemLoader;

/**
 * Defined here for brevity.
 */
class StringLike
{
    public function __toString()
    {
        return 'string-like';
    }
}

class SonataHelpersExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SonataHelpersExtension
     */
    private $twigExtension;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    public function setUp()
    {
        $loader = new StubFilesystemLoader(array(
            __DIR__.'/../../../Resources/views/CRUD',
        ));
        $this->environment = new \Twig_Environment($loader, array(
            'strict_variables' => true,
            'cache'            => false,
            'autoescape'       => false,
            'optimizations'    => 0,
        ));
        $this->twigExtension = new SonataHelpersExtension();
        $this->environment->addExtension($this->twigExtension);
        $this->twigExtension->initRuntime($this->environment);
    }

    /**
     * @dataProvider testRenderClasslistProvider
     */
    public function testRenderClassList($input, $expected)
    {
        $output = call_user_func_array(array($this->twigExtension, 'renderClasslist'), $input);
        $this->assertSame($expected, $output);
    }

    public function testRenderClasslistProvider()
    {
        $stringLike = new StringLike();
        $iterable = new \ArrayObject(array('foo' => true, 'bar' => true, 'baz' => false));

        return array(
            'accepts scalar arguments'               => array(array('foo', 'bar'), 'foo bar'),
            'accepts array arguments'                => array(array(array('foo' => true, 'bar' => true)), 'foo bar'),
            'ignores falsy values'                   => array(array(null, false, 0, array(), '', array(null, false, 0, '')), ''),
            'ignores non-string keys/values'         => array(array(42, 666, array(true, false)), ''),
            'keeps truthy vales'                     => array(array('yep', array('jawoll')), 'yep jawoll'),
            'accepts iterable & string-like objects' => array(array($stringLike, $iterable), 'string-like foo bar'),
            'flattens arrays 1'                      => array(array(array('foo' => array('bar' => array('baz')))), 'baz'),
            'flattens arrays 2'                      => array(array(array('foo', array('bar', array('baz')))), 'foo bar baz'),
            'flattens arrays 3'                      => array(array(array('foo', array('bar' => array('baz', 'qux')))), 'foo baz qux'),
            'later values override previous'         => array(array('foo', 'bar', array('bar' => false)), 'foo'),
        );
    }

    /**
     * @dataProvider testRenderListFieldClassesProvider
     */
    public function testRenderListFieldClasses($input, $expected)
    {
        $output = call_user_func(array($this->twigExtension, 'renderListFieldClasses'), $input);
        $this->assertSame($expected, $output);
    }

    public function testRenderListFieldClassesProvider()
    {
        return array(
            'no arguments'       => array(array(), 'sonata-ba-list-field'),
            'batch field'        => array(array('batch' => true), 'sonata-ba-list-field sonata-ba-list-field-batch'),
            'actions field'      => array(array('actions' => true), 'sonata-ba-list-field sonata-ba-list-field-_action'),
            'inline fields cell' => array(array('inline' => true), 'sonata-ba-list-field sonata-ba-list-field-inline-fields'),
            'type boolean'       => array(array('type' => 'boolean'), 'sonata-ba-list-field sonata-ba-list-field-boolean'),
            'type foo'           => array(array('type' => 'foo'), 'sonata-ba-list-field sonata-ba-list-field-foo'),
            'empty type'         => array(array('type' => ''), 'sonata-ba-list-field'),
        );
    }
}
