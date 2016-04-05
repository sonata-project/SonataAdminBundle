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
class HTMLAssert extends \PHPUnit_Framework_Assert
{
    /**
     * @param $string
     *
     * @return string
     */
    public static function normalizeWhitespace($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }

    /**
     * Asserts that two HTML strings are equal by comparing their DOM.
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     */
    public static function assertHTMLEquals($expected, $actual, $message = '')
    {
        $expectedDoc = self::getHTMLDocument($expected);
        $actualDoc = self::getHTMLDocument($actual);

        self::assertHTMLElementEquals(
            $expectedDoc->getElementsByTagName('body')->item(0),
            $actualDoc->getElementsByTagName('body')->item(0),
            $message
        );
    }

    /**
     * Asserts that two HTML elements are *semantically* equal:
     *   - Empty text nodes, comments, CDATA sections are skipped.
     *   - Whitespace is normalized.
     *   - Attributes names & values are checked (order doesn't matter).
     *   - The order of classes in the class attribute doesn't matter.
     *
     * @param \DOMElement $expected
     * @param \DOMElement $actual
     * @param string      $message
     */
    public static function assertHTMLElementEquals(\DOMElement $expected, \DOMElement $actual, $message = '')
    {
        if ($message) {
            $message .= PHP_EOL;
        }
        self::assertSame($expected->tagName, $actual->tagName, sprintf(
            "%sTag name doesn't match",
            $message
        ));
        self::assertSame($expected->attributes->length, $actual->attributes->length, sprintf(
            '%sNumber of attributes differ on element %s',
            $message,
            self::dumpHtmlNode($actual)
        ));

        /** @var \DOMAttr $attribute */
        foreach ($expected->attributes as $attribute) {
            if (!$actual->hasAttribute($attribute->name)) {
                self::fail(sprintf(
                    '%sAttribute "%s" not found on element %s',
                    $message,
                    $attribute->name,
                    self::dumpHtmlNode($expected)
                ));
            }
            if ($attribute->name === 'class') {
                self::assertClassAttributeEquals($expected, $actual, $message);
            } else {
                self::assertSame($attribute->value, $actual->getAttribute($attribute->name), sprintf(
                    '%sValues of attribute "%s" differ on element <%s>.',
                    $message,
                    $attribute->name,
                    $expected->tagName
                ));
            }
        }

        self::removeEmptyNodes($expected);
        self::removeEmptyNodes($actual);

        self::assertSame($expected->childNodes->length, $actual->childNodes->length, sprintf(
            '%sNumber of child nodes differ on element %s',
            $message,
            self::dumpHtmlNode($expected)
        ));

        foreach ($expected->childNodes as $i => $expectedChild) {
            $actualChild = $actual->childNodes->item($i);
            self::assertSame($expectedChild->nodeType, $actualChild->nodeType, sprintf(
                '%sExpected child node to be of type "%s", got "%s"',
                $message,
                get_class($expectedChild),
                get_class($actualChild)
            ));

            if ($expectedChild instanceof \DOMElement) {
                self::assertHTMLElementEquals($expectedChild, $actualChild);
            } elseif ($expectedChild instanceof \DOMText) {
                self::assertSame(trim($expectedChild->nodeValue), trim($actualChild->nodeValue), sprintf(
                    "%sText node value doesn't match in element %s.",
                    $message,
                    self::dumpHtmlNode($expected)
                ));
            }
        }
    }

    /**
     * Compares class list in a order-independent fashion.
     *
     * @param \DOMElement $expected
     * @param \DOMElement $actual
     * @param string      $message
     */
    public static function assertClassAttributeEquals(\DOMElement $expected, \DOMElement $actual, $message = '')
    {
        $expectedAttribute = $expected->getAttribute('class');
        $actualAttribute = $actual->getAttribute('class');

        $expectedClasses = preg_split('/\s+/', $expectedAttribute, -1, PREG_SPLIT_NO_EMPTY);
        $actualClasses = preg_split('/\s+/', $actualAttribute, -1, PREG_SPLIT_NO_EMPTY);

        // Let's re-implement assertEquals here, because the php_cs rules do not allow it :/
        sort($expectedClasses);
        sort($actualClasses);

        self::assertSame($expectedClasses, $actualClasses, sprintf(
            '%sClass attribute differ on element %s',
            $message,
            self::dumpHtmlNode($actual)
        ));
    }

    /**
     * @param string $html
     *
     * @return \DOMDocument
     */
    private static function getHTMLDocument($html)
    {
        $html = self::normalizeWhitespace($html);
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        // remove empty & merge adjacent text nodes.
        $dom->normalizeDocument();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return $dom;
    }

    /**
     * Removes empty text nodes & all other character data (comments, CDATA, etc...).
     *
     * @param \DOMNode $node
     */
    private static function removeEmptyNodes(\DOMNode $node)
    {
        for ($i = $node->childNodes->length - 1; $i >= 0; --$i) {
            $child = $node->childNodes->item($i);
            if ($child instanceof \DOMText) {
                $text = trim($child->nodeValue);
                if (!$text) {
                    $node->removeChild($child);
                }
            } elseif ($child instanceof \DOMCharacterData) {
                $node->removeChild($child);
            }
        }
        // Merge adjacent text nodes.
        $node->normalize();
    }

    /**
     * Returns the HTML string representation of a DOMNode.
     *
     * @param \DOMNode $element
     * @param bool     $deep
     *
     * @return string
     */
    private static function dumpHtmlNode(\DOMNode $element, $deep = false)
    {
        return $element->ownerDocument->saveHTML($element->cloneNode($deep));
    }
}
