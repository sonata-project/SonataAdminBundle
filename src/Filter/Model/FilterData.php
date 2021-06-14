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

namespace Sonata\AdminBundle\Filter\Model;

/**
 * @psalm-immutable
 */
final class FilterData
{
    /**
     * @var ?int
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $hasValue;

    private function __construct()
    {
        $this->hasValue = false;
    }

    /**
     * @param array<string, mixed>|null $data
     *
     * @psalm-pure
     *
     * @phpstan-param array{type?: int|numeric-string|null, value?: mixed} $data
     */
    public static function fromArray(array $data): self
    {
        $filterData = new self();

        if (isset($data['type'])) {
            if (!\is_int($data['type']) && (!\is_string($data['type']) || !is_numeric($data['type']))) {
                throw new \InvalidArgumentException(sprintf(
                    'The "type" parameter MUST be of type "integer" or "null", %s given.',
                    \is_object($data['type']) ? 'instance of "'.\get_class($data['type']).'"' : '"'.\gettype($data['type']).'"'
                ));
            }

            $filterData->type = (int) $data['type'];
        }

        if (\array_key_exists('value', $data)) {
            $filterData->value = $data['value'];
            $filterData->hasValue = true;
        }

        return $filterData;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (!$this->hasValue) {
            throw new \LogicException('The FilterData object does not have a value.');
        }

        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function changeValue($value): self
    {
        return self::fromArray([
            'type' => $this->getType(),
            'value' => $value,
        ]);
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function isType(int $type): bool
    {
        return $this->type === $type;
    }

    public function hasValue(): bool
    {
        return $this->hasValue;
    }
}
