<?php

namespace Sonata\AdminBundle\FieldDescription;

final class Mapping
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
}
