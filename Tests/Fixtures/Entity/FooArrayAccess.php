<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

class FooArrayAccess implements \ArrayAccess
{
    // methods to enable ArrayAccess
    public function offsetExists($offset) {
        $value = $this->offsetGet($offset);
        return $value !== null;
    }

    public function offsetGet($offset) {
        $offset = str_replace('_', '', $offset); // method names always use camels, field names can use snakes
        $methodName = "get$offset";
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        } else {
            return null;
        }
    }

    public function offsetSet($offset, $value) {
        throw new \BadMethodCallException ("Array access of class " . get_class($this) . " is read-only!");
    }

    public function offsetUnset($offset) {
        throw new \BadMethodCallException("Array access of class " . get_class($this) . " is read-only!");
    }

    private $bar;

    private $baz;

    public function getBar()
    {
        return $this->bar;
    }

    public function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function getBaz()
    {
        return $this->baz;
    }

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }

    public function __toString()
    {
        return (string) $this->bar;
    }
}
