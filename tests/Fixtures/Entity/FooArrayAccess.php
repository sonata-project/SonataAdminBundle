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

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

/**
 * @phpstan-implements \ArrayAccess<string, string|null>
 */
final class FooArrayAccess implements \ArrayAccess, \Stringable
{
    private ?string $bar = null;

    public function __toString(): string
    {
        return (string) $this->bar;
    }

    // methods to enable ArrayAccess
    public function offsetExists($offset): bool
    {
        $value = $this->offsetGet($offset);

        return null !== $value;
    }

    public function offsetGet($offset): ?string
    {
        $offset = str_replace('_', '', $offset); // method names always use camels, field names can use snakes
        $methodName = "get$offset";
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        return null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException(\sprintf('Array access of class %s is read-only!', self::class));
    }

    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException(\sprintf('Array access of class %s is read-only!', self::class));
    }

    public function getBar(): ?string
    {
        return $this->bar;
    }

    public function setBar(string $bar): void
    {
        $this->bar = $bar;
    }
}
