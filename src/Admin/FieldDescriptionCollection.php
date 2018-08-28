<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class FieldDescriptionCollection implements \ArrayAccess, \Countable
{
    /**
     * @var FieldDescriptionInterface[]
     */
    protected $elements = [];

    public function add(FieldDescriptionInterface $fieldDescription)
    {
        $this->elements[$fieldDescription->getName()] = $fieldDescription;
    }

    /**
     * @return array
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
        return array_key_exists($name, $this->elements);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return FieldDescriptionInterface
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->elements[$name];
        }

        throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            unset($this->elements[$name]);
        }
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

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
        if ($this->has('batch')) {
            array_unshift($keys, 'batch');
        }

        $this->elements = array_merge(array_flip($keys), $this->elements);
    }
}
