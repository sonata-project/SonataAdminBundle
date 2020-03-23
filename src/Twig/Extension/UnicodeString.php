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

namespace Sonata\AdminBundle\Twig\Extension;

use Symfony\Component\String\Exception\ExceptionInterface;
use Symfony\Component\String\UnicodeString as DecoratedUnicodeString;

/**
 * Decorates `Symfony\Component\String\UnicodeString` in order to provide the `$cut`
 * argument for `truncate()`. This class must be removed when the component ships
 * this feature.
 *
 * @see https://github.com/symfony/symfony/pull/35649
 *
 * @throws ExceptionInterface
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class UnicodeString
{
    /**
     * @var DecoratedUnicodeString
     */
    private $unicodeString;

    public function __construct(string $string = '')
    {
        $this->unicodeString = new DecoratedUnicodeString($string);
    }

    public function __call($name, $arguments)
    {
        return $this->unicodeString->$name(...$arguments);
    }

    public function __toString(): string
    {
        return (string) $this->unicodeString;
    }

    public function truncate(int $length, string $ellipsis = '', bool $preserve = false): DecoratedUnicodeString
    {
        $stringLength = $this->unicodeString->length();

        if ($stringLength <= $length) {
            return clone $this->unicodeString;
        }

        $ellipsisLength = '' !== $ellipsis ? (new DecoratedUnicodeString($ellipsis))->length() : 0;

        if ($length < $ellipsisLength) {
            $ellipsisLength = 0;
        }

        if ($preserve) {
            $length = $ellipsisLength + ($this->unicodeString->indexOf([' ', "\r", "\n", "\t"], ($length ?: 1) - 1) ?? $stringLength);
        }

        $str = $this->unicodeString->slice(0, $length - $ellipsisLength);

        return $ellipsisLength ? $str->trimEnd()->append($ellipsis) : $str;
    }
}
