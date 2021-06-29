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

use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template TValue of FieldDescriptionInterface
 * @phpstan-implements \ArrayAccess<string,TValue>
 */
class FieldDescriptionCollection implements \ArrayAccess, \Countable
{
    /**
     * @var array<string, FieldDescriptionInterface>
     *
     * @phpstan-var array<string, TValue>
     */
    protected $elements = [];

    /**
     * @phpstan-param TValue $fieldDescription
     */
    public function add(FieldDescriptionInterface $fieldDescription)
    {
        $this->elements[$fieldDescription->getName()] = $fieldDescription;
    }

    /**
     * @return array<string, FieldDescriptionInterface>
     *
     * @phpstan-return array<string, TValue>
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return \array_key_exists($name, $this->elements);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return FieldDescriptionInterface
     *
     * @phpstan-return TValue
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
        }

        return $this->elements[$name];
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        unset($this->elements[$name]);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param string $offset
     *
     * @return FieldDescriptionInterface
     *
     * @phpstan-return TValue
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Cannot set value, use add');
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    public function count()
    {
        return \count($this->elements);
    }

    public function reorder(array $keys)
    {
        if ($this->has(ListMapper::NAME_BATCH)) {
            array_unshift($keys, ListMapper::NAME_BATCH);
        }

        $orderedElements = [];
        foreach ($keys as $name) {
            if (!$this->has($name)) {
                throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
            }

            $orderedElements[$name] = $this->elements[$name];
        }

        $this->elements = $orderedElements + $this->elements;
    }
}

// NEXT_MAJOR: Remove next line.
class_exists(\Sonata\AdminBundle\Admin\FieldDescriptionCollection::class);
