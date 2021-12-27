<?php

namespace Sonata\AdminBundle\FieldDescription;

use ArrayAccess;

class Mapping implements ArrayAccess
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var array<string, mixed>
     */
    private $data = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        if (!isset($data['fieldName']) || !\is_string($data['fieldName'])) {
            throw new \InvalidArgumentException('The fieldName is required.');
        } else {
            $this->fieldName = $data['fieldName'];
        }

        $this->data = $data;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return mixed
     */
    public function getValue(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('The Mapping data is immutable.');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('The Mapping data is immutable.');
    }
}
