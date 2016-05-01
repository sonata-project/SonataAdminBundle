<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests;

/**
 * @author ju1ius
 */
class HTMLAssertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testAssertHTMLEqualsProvider
     */
    public function testAssertHTMLEquals($actual, $expected, $shouldBeEqual)
    {
        if (!$shouldBeEqual) {
            $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
        }
        HTMLAssert::assertHTMLEquals($expected, $actual);
    }

    public function testAssertHTMLEqualsProvider()
    {
        return array(
            'Whitespace does not matter' => array(
                "<div>\n\t<b>\n\t\tmucho space  </b>\n\t</div>",
                '<div><b>mucho space</b></div>',
                true,
            ),
            'Comments are ignored' => array(
                '<div>Foo<!-- comment 1 -->Bar<!-- comment 2 -->Baz</div>',
                '<div>FooBarBaz</div>',
                true,
            ),
            'CDATA sections are ignored' => array(
                '<div><[!CDATA[ nothing to see here ]]></div>',
                '<div></div>',
                true,
            ),
            'Attribute order does not matter' => array(
                '<i required disabled></i>',
                '<i disabled required></i>',
                true,
            ),
            'Class order does not matter' => array(
                '<i class="foo bar baz"></i>',
                '<i class="baz bar foo"></i>',
                true,
            ),
            'Fails when tag names differ' => array(
                '<i></i>',
                '<b></b>',
                false,
            ),
            'Fails when attributes differ' => array(
                '<i disabled></i>',
                '<i required></i>',
                false,
            ),
            'Fails when attributes values differ' => array(
                '<i id="one"></i>',
                '<i id="two"></i>',
                false,
            ),
            'Fails when text differ' => array(
                '<i>Foo</i>',
                '<i>Bar</i>',
                false,
            ),
            'Fails when number of children differ' => array(
                '<div><br><br></div>',
                '<div><br><br><br></div>',
                false,
            ),
        );
    }
}
