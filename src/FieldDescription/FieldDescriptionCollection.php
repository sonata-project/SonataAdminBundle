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

namespace Sonata\AdminBundle\FieldDescription;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template TValue of FieldDescriptionInterface
 * @phpstan-implements \ArrayAccess<string,TValue>
 */
final class FieldDescriptionCollection implements \ArrayAccess, \Countable
{
    /**
     * @var array<string, FieldDescriptionInterface>
     *
     * @phpstan-var array<string, TValue>
     */
    private $elements = [];

    /**
     * @phpstan-param TValue $fieldDescription
     */
    public function add(FieldDescriptionInterface $fieldDescription): void
    {
        $this->elements[$fieldDescription->getName()] = $fieldDescription;
    }

    /**
     * @return array<string, FieldDescriptionInterface>
     *
     * @phpstan-return array<string, TValue>
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->elements);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @phpstan-return TValue
     */
    public function get(string $name): FieldDescriptionInterface
    {
        if ($this->has($name)) {
            return $this->elements[$name];
        }

        throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
    }

    public function remove(string $name): void
    {
        if ($this->has($name)) {
            unset($this->elements[$name]);
        }
    }

    /**
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param string $offset
     *
     * @phpstan-return TValue
     */
    public function offsetGet($offset): FieldDescriptionInterface
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new \RuntimeException('Cannot set value, use add');
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    public function count(): int
    {
        return \count($this->elements);
    }

    /**
     * @param string[] $keys
     */
    public function reorder(array $keys): void
    {
        if ($this->has('batch')) {
            array_unshift($keys, 'batch');
        }

        $this->elements = array_merge(array_flip($keys), $this->elements);
    }
}
